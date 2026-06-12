<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\Pointage;
use App\Models\Rapport;
use App\Notifications\HorizonAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class HorizonAiService
{
    public function __construct(
        protected string $apiKey = ''
    ) {
        $this->apiKey = env('GEMINI_API_KEY', $this->apiKey);
    }

    public function analyzeUserRequest(string $question): string
    {
        $question = trim($question);
        $normalized = mb_strtolower($question, 'UTF-8');

        if (Str::contains($normalized, ['rapport journalier', 'rapport du jour', 'statistiques du jour', 'jour'])) {
            return $this->formatReportText($this->generateReport('day'), 'Rapport journalier');
        }

        if (Str::contains($normalized, ['rapport hebdomadaire', 'rapport de la semaine', 'semaine'])) {
            return $this->formatReportText($this->generateReport('week'), 'Rapport hebdomadaire');
        }

        if (Str::contains($normalized, ['rapport mensuel', 'rapport du mois', 'mois'])) {
            return $this->formatReportText($this->generateReport('month'), 'Rapport mensuel');
        }

        if (Str::contains($normalized, ['rapport annuel', 'rapport de l\'année', 'année', 'an'])) {
            return $this->formatReportText($this->generateReport('year'), 'Rapport annuel');
        }

        if (Str::contains($normalized, ['anomalie', 'fraude', 'suspicion', 'irrégulier', 'inconsist'])) {
            return $this->formatAnomaliesText($this->detectAnomalies('month'), 'Détection d\'anomalies');
        }

        if (Str::contains($normalized, ['absent', 'retard', 'présence', 'présences', 'pointage', 'pointages'])) {
            return $this->formatReportText($this->generateReport('day'), 'Résumé de présence');
        }

        return $this->callGemini($this->buildSystemPrompt($this->getDashboardContext()) . "\n\nQuestion : {$question}");
    }

    public function generateReport(string $period, ?Carbon $endDate = null): array
    {
        $range = $this->buildRange($period, $endDate);
        $records = Pointage::with('employe')
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->get();

        $totalEmployes = Employe::count();
        $present = $records->where('status', 'Present')->count();
        $absent = $records->where('status', 'Absent')->count();
        $late = $records->where('status', 'Retard')->count();

        $lateByEmployee = $records
            ->where('status', 'Retard')
            ->groupBy('employee_id')
            ->map(function ($items) {
                $employee = $items->first()->employe;
                return [
                    'nom' => $employee?->getFullNameAttribute() ?? 'Employé inconnu',
                    'retards' => $items->count(),
                ];
            })
            ->sortByDesc('retards')
            ->values()
            ->take(5)
            ->all();

        $absentByEmployee = $records
            ->where('status', 'Absent')
            ->groupBy('employee_id')
            ->map(function ($items) {
                $employee = $items->first()->employe;
                return [
                    'nom' => $employee?->getFullNameAttribute() ?? 'Employé inconnu',
                    'absences' => $items->count(),
                ];
            })
            ->sortByDesc('absences')
            ->values()
            ->take(5)
            ->all();

        return [
            'period' => $period,
            'from' => $range['from']->toDateString(),
            'to' => $range['to']->toDateString(),
            'total_employes' => $totalEmployes,
            'enregistrements' => $records->count(),
            'present' => $present,
            'absent' => $absent,
            'retard' => $late,
            'top_retards' => $lateByEmployee,
            'top_absences' => $absentByEmployee,
        ];
    }

    public function detectAnomalies(string $period, ?Carbon $endDate = null): array
    {
        $range = $this->buildRange($period, $endDate);
        $records = Pointage::with('employe')
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->get();

        $duplicates = $records
            ->groupBy(function ($presence) {
                return $presence->employee_id . '|' . $presence->created_at->toDateString();
            })
            ->filter(fn($group) => $group->count() > 1)
            ->map(function ($group) {
                $employee = $group->first()->employe;
                return [
                    'nom' => $employee?->getFullNameAttribute() ?? 'Employé inconnu',
                    'date' => $group->first()->created_at->toDateString(),
                    'evenements' => $group->pluck('status')->all(),
                    'compte' => $group->count(),
                ];
            })
            ->values()
            ->all();

        $contradictions = $records
            ->groupBy(function ($presence) {
                return $presence->employee_id . '|' . $presence->created_at->toDateString();
            })
            ->filter(function ($group) {
                $statuses = $group->pluck('status')->unique();
                return $statuses->contains('Absent') && ($statuses->contains('Present') || $statuses->contains('Retard'));
            })
            ->map(function ($group) {
                $employee = $group->first()->employe;
                return [
                    'nom' => $employee?->getFullNameAttribute() ?? 'Employé inconnu',
                    'date' => $group->first()->created_at->toDateString(),
                    'status' => $group->pluck('status')->unique()->values()->all(),
                ];
            })
            ->values()
            ->all();

        $lateStreaks = $records
            ->where('status', 'Retard')
            ->groupBy('employee_id')
            ->map(function ($items) {
                $employee = $items->first()->employe;
                return [
                    'nom' => $employee?->getFullNameAttribute() ?? 'Employé inconnu',
                    'retards' => $items->count(),
                ];
            })
            ->filter(fn($item) => $item['retards'] >= 3)
            ->values()
            ->all();

        $suspicious = [];

        if (!empty($duplicates)) {
            $suspicious[] = 'Présences multiples enregistrées pour le même jour et le même employé.';
        }

        if (!empty($contradictions)) {
            $suspicious[] = 'Présence contradictoire : un employé a été marqué comme absent et présent le même jour.';
        }

        if (!empty($lateStreaks)) {
            $suspicious[] = 'Employés avec trois retards ou plus sur la période sélectionnée.';
        }

        return [
            'period' => $period,
            'from' => $range['from']->toDateString(),
            'to' => $range['to']->toDateString(),
            'count' => count($duplicates) + count($contradictions) + count($lateStreaks),
            'duplicates' => $duplicates,
            'contradictions' => $contradictions,
            'late_streaks' => $lateStreaks,
            'suspicions' => $suspicious,
        ];
    }

    public function formatReportText(array $report, string $title = 'Rapport'): string
    {
        $summary = [
            "{$title} ({$report['from']} → {$report['to']})",
            "Effectif total : {$report['total_employes']}",
            "Enregistrements de présence : {$report['enregistrements']}",
            "Présents : {$report['present']}",
            "Absents : {$report['absent']}",
            "Retards : {$report['retard']}",
            '',
        ];

        if (!empty($report['top_retards'])) {
            $summary[] = 'Top 5 des retards :';
            foreach ($report['top_retards'] as $item) {
                $summary[] = "- {$item['nom']} : {$item['retards']} retard(s)";
            }
        }

        if (!empty($report['top_absences'])) {
            $summary[] = '';
            $summary[] = 'Top 5 des absences :';
            foreach ($report['top_absences'] as $item) {
                $summary[] = "- {$item['nom']} : {$item['absences']} absence(s)";
            }
        }

        return implode("\n", $summary);
    }

    public function formatAnomaliesText(array $anomalies, string $title = 'Anomalies détectées'): string
    {
        $summary = [
            "{$title} ({$anomalies['from']} → {$anomalies['to']})",
            "Nombre total d'alertes : {$anomalies['count']}",
            '',
        ];

        if (!empty($anomalies['duplicates'])) {
            $summary[] = 'Présences multiples détectées :';
            foreach ($anomalies['duplicates'] as $item) {
                $summary[] = "- {$item['nom']} le {$item['date']} ({$item['compte']} enregistrements : " . implode(', ', $item['evenements']) . ')';
            }
            $summary[] = '';
        }

        if (!empty($anomalies['contradictions'])) {
            $summary[] = 'Statuts contradictoires détectés :';
            foreach ($anomalies['contradictions'] as $item) {
                $summary[] = "- {$item['nom']} le {$item['date']} : " . implode(', ', $item['status']);
            }
            $summary[] = '';
        }

        if (!empty($anomalies['late_streaks'])) {
            $summary[] = 'Employés avec plusieurs retards :';
            foreach ($anomalies['late_streaks'] as $item) {
                $summary[] = "- {$item['nom']} : {$item['retards']} retards";
            }
            $summary[] = '';
        }

        if (empty($anomalies['suspicions'])) {
            $summary[] = 'Aucune anomalie critique détectée sur cette période.';
        }

        return implode("\n", $summary);
    }

    public function createReportRecord(string $title, string $content, ?Carbon $date = null): ?Rapport
    {
        $date = $date ?? Carbon::now();
        $owner = Employe::whereIn('role', ['RH_Admin', 'Manager'])->first() ?? Employe::first();

        if (!$owner) {
            Log::warning('HorizonAiService : impossible de créer le rapport car aucun employé n\'a été trouvé.');
            return null;
        }

        return Rapport::create([
            'titre' => $title,
            'contenu' => $content,
            'date_rapport' => $date->toDateString(),
            'employe_id' => $owner->id,
        ]);
    }

    /**
     * Export a report as an Excel file and return the public URL.
     * Requires phpoffice/phpspreadsheet.
     *
     * @throws \RuntimeException
     */
    public function exportReportAsExcel(array $report, string $filename = null): string
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            throw new \RuntimeException('PhpSpreadsheet missing. Run: composer require phpoffice/phpspreadsheet');
        }

        $filename = $filename ?? ('rapport_' . $report['period'] . '_' . now()->format('Ymd_His') . '.xlsx');
        $path = 'reports/' . $filename;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', "Rapport ({$report['from']} → {$report['to']})");
        $sheet->setCellValue('A2', 'Total employés');
        $sheet->setCellValue('B2', $report['total_employes']);
        $sheet->setCellValue('A3', 'Enregistrements');
        $sheet->setCellValue('B3', $report['enregistrements']);
        $sheet->setCellValue('A4', 'Présents');
        $sheet->setCellValue('B4', $report['present']);
        $sheet->setCellValue('A5', 'Absents');
        $sheet->setCellValue('B5', $report['absent']);
        $sheet->setCellValue('A6', 'Retards');
        $sheet->setCellValue('B6', $report['retard']);

        $row = 8;
        if (!empty($report['top_retards'])) {
            $sheet->setCellValue("A{$row}", 'Top retards');
            $row++;
            $sheet->setCellValue("A{$row}", 'Nom');
            $sheet->setCellValue("B{$row}", 'Retards');
            $row++;
            foreach ($report['top_retards'] as $item) {
                $sheet->setCellValue("A{$row}", $item['nom']);
                $sheet->setCellValue("B{$row}", $item['retards']);
                $row++;
            }
        }

        if (!empty($report['top_absences'])) {
            $row += 1;
            $sheet->setCellValue("A{$row}", 'Top absences');
            $row++;
            $sheet->setCellValue("A{$row}", 'Nom');
            $sheet->setCellValue("B{$row}", 'Absences');
            $row++;
            foreach ($report['top_absences'] as $item) {
                $sheet->setCellValue("A{$row}", $item['nom']);
                $sheet->setCellValue("B{$row}", $item['absences']);
                $row++;
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        $writer->save($tempFile);

        $stored = Storage::disk('public')->putFileAs('reports', new \Illuminate\Http\File($tempFile), $filename);
        // Cleanup temp
        @unlink($tempFile);

        return Storage::disk('public')->url($stored);
    }

    /**
     * Export a report as a PDF file and return the public URL.
     * Requires barryvdh/laravel-dompdf or dompdf/dompdf.
     *
     * @throws \RuntimeException
     */
    public function exportReportAsPdf(array $report, string $filename = null): string
    {
        $filename = $filename ?? ('rapport_' . $report['period'] . '_' . now()->format('Ymd_His') . '.pdf');
        $path = storage_path('app/public/reports/' . $filename);

        $html = '<h2>Rapport (' . e($report['from']) . ' → ' . e($report['to']) . ')</h2>';
        $html .= '<ul>';
        $html .= '<li>Total employés: ' . $report['total_employes'] . '</li>';
        $html .= '<li>Enregistrements: ' . $report['enregistrements'] . '</li>';
        $html .= '<li>Présents: ' . $report['present'] . '</li>';
        $html .= '<li>Absents: ' . $report['absent'] . '</li>';
        $html .= '<li>Retards: ' . $report['retard'] . '</li>';
        $html .= '</ul>';

        if (!empty($report['top_retards'])) {
            $html .= '<h3>Top retards</h3><ol>';
            foreach ($report['top_retards'] as $item) {
                $html .= '<li>' . e($item['nom']) . ' — ' . e($item['retards']) . '</li>';
            }
            $html .= '</ol>';
        }

        if (!empty($report['top_absences'])) {
            $html .= '<h3>Top absences</h3><ol>';
            foreach ($report['top_absences'] as $item) {
                $html .= '<li>' . e($item['nom']) . ' — ' . e($item['absences']) . '</li>';
            }
            $html .= '</ol>';
        }

        // Use Laravel-Dompdf if available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('A4', 'portrait');
            Storage::disk('public')->put('reports/' . $filename, $pdf->output());
            return Storage::disk('public')->url('reports/' . $filename);
        }

        // Fallback to direct Dompdf
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();
            Storage::disk('public')->put('reports/' . $filename, $output);
            return Storage::disk('public')->url('reports/' . $filename);
        }

        throw new \RuntimeException('Aucun convertisseur PDF trouvé. Installez barryvdh/laravel-dompdf ou dompdf/dompdf via composer.');
    }

    /**
     * Generate report data and export to requested format ('excel'|'pdf'). Returns public URL.
     */
    public function generateAndExport(string $period, string $format = 'excel', ?Carbon $endDate = null): string
    {
        $report = $this->generateReport($period, $endDate);

        return match (strtolower($format)) {
            'pdf' => $this->exportReportAsPdf($report),
            default => $this->exportReportAsExcel($report),
        };
    }

    public function sendNotification(string $subject, string $message): bool
    {
        $email = $this->getAdminEmail();

        if (empty($email)) {
            Log::warning('HorizonAiService : aucune adresse de notification configurée.');
            return false;
        }

        Notification::route('mail', $email)->notify(new HorizonAlertNotification($subject, $message));

        return true;
    }

    protected function getAdminEmail(): string
    {
        return env('ADMIN_NOTIFICATION_EMAIL', config('mail.from.address', 'admin@crhacerh.com'));
    }

    protected function getDashboardContext(): array
    {
        $today = Carbon::today();
        $stats = $this->generateReport('day', $today);

        return [
            'total_employes' => $stats['total_employes'],
            'absents_du_jour' => $stats['absent'],
            'retards_du_jour' => $stats['retard'],
            'plage' => "du {$stats['from']} au {$stats['to']}",
        ];
    }

    protected function buildSystemPrompt(array $context): string
    {
        return "Tu es Horizon, un assistant virtuel expert en Ressources Humaines intégré à l'application C.H.R.A.C.E.R.H. "
            . "Tu connais les meilleures pratiques RH, la génération de rapports, la détection d'anomalies et la surveillance d'irrégularités. "
            . "Tu dois proposer des réponses précises en français, en utilisant les données actuelles lorsque cela est possible. "
            . "Ne donne pas de réponses génériques, formule des diagnostics et des recommandations concrètes. "
            . "Contexte disponible : effectif total = {$context['total_employes']}, absents aujourd'hui = {$context['absents_du_jour']}, retards aujourd'hui = {$context['retards_du_jour']}. "
            . "Si le message porte sur un rapport, détecte la période et fournis un résumé clair. "
            . "Si le message porte sur une anomalie ou une possible fraude, identifie les incohérences et explique-les simplement.";
    }

    protected function callGemini(string $prompt): string
    {
        if (empty($this->apiKey)) {
            return "La clé Gemini n'est pas configurée. Veuillez ajouter GEMINI_API_KEY à votre fichier .env.";
        }

        try {
            $client = \Gemini::client($this->apiKey);
            $response = $client->geminiPro()->generateContent($prompt);
            return trim($response->text() ?? 'Je n’ai pas obtenu de réponse de l’API Gemini.');
        } catch (\Throwable $exception) {
            Log::error('HorizonAiService Gemini error: ' . $exception->getMessage());
            return "Je rencontre un problème pour contacter le service Gemini. Vérifiez la clé API et la connectivité.";
        }
    }

    protected function buildRange(string $period, ?Carbon $endDate = null): array
    {
        $reference = $endDate ? $endDate->copy() : Carbon::now();

        return match ($period) {
            'week' => [
                'from' => $reference->copy()->startOfWeek(),
                'to' => $reference->copy()->endOfWeek(),
            ],
            'month' => [
                'from' => $reference->copy()->startOfMonth(),
                'to' => $reference->copy()->endOfMonth(),
            ],
            'year' => [
                'from' => $reference->copy()->startOfYear(),
                'to' => $reference->copy()->endOfYear(),
            ],
            default => [
                'from' => $reference->copy()->startOfDay(),
                'to' => $reference->copy()->endOfDay(),
            ],
        };
    }
}
