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

        $period = null;
        $title = '';

        if (Str::contains($normalized, ['prédiction', 'prediction', 'risque', 'susceptible', 'absentéisme', 'absenteisme'])) {
            $predictions = $this->predictAbsenteeismRisk();
            $highRisk = array_filter($predictions, fn($p) => $p['score'] > 20);
            
            $texte = "🔮 **Prédiction des risques d'absentéisme (30 derniers jours)**\n\n";
            if (empty($highRisk)) {
                $texte .= "✅ Excellent ! Aucun employé ne présente de risque d'absentéisme modéré ou élevé pour la semaine prochaine.";
            } else {
                $texte .= "Voici la liste des employés présentant des risques identifiés :\n\n";
                foreach ($highRisk as $p) {
                    $texte .= "- **{$p['nom']}** ({$p['departement']}) : **{$p['score']}%** (Risque **{$p['niveau']}**)\n";
                    $texte .= "  *Motif* : {$p['raisons']}\n";
                }
                $texte .= "\n💡 *Recommandation RH* : Planifier un entretien individuel avec les employés affichant un score supérieur à 50% afin de comprendre les raisons de ces retards/absences répétés.";
            }
            return $texte;
        }

        if (Str::contains($normalized, ['graphique', 'diagramme', 'comparer', 'comparaison', 'statistiques des départements'])) {
            $departments = \App\Models\Department::with('employees.pointages')->get();
            $labels = [];
            $presents = [];
            $retards = [];
            $absences = [];

            foreach ($departments as $dept) {
                $labels[] = $dept->name;
                $empIds = $dept->employees->pluck('id');
                
                $pointages = Pointage::whereIn('employee_id', $empIds)->get();
                $presents[] = $pointages->where('status', 'Present')->count();
                $retards[] = $pointages->where('status', 'Retard')->count();
                $absences[] = $pointages->where('status', 'Absent')->count();
            }

            $chartData = [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Présences',
                        'data' => $presents,
                        'backgroundColor' => 'rgba(34, 197, 94, 0.6)'
                    ],
                    [
                        'label' => 'Retards',
                        'data' => $retards,
                        'backgroundColor' => 'rgba(234, 88, 12, 0.6)'
                    ],
                    [
                        'label' => 'Absences',
                        'data' => $absences,
                        'backgroundColor' => 'rgba(239, 68, 68, 0.6)'
                    ]
                ]
            ];

            $texte = "📊 **Graphique comparatif des présences par département**\n\n";
            $texte .= "Voici le comparatif d'assiduité généré pour chaque département :\n";
            foreach ($labels as $i => $label) {
                $texte .= "- **{$label}** : Présents ({$presents[$i]}), Retards ({$retards[$i]}), Absents ({$absences[$i]})\n";
            }
            $texte .= "\n[CHART:" . json_encode($chartData) . "]";
            return $texte;
        }

        if (Str::contains($normalized, ['rapport journalier', 'rapport du jour', 'statistiques du jour', 'jour'])) {
            $period = 'day';
            $title = 'Rapport journalier';
        } elseif (Str::contains($normalized, ['rapport hebdomadaire', 'rapport de la semaine', 'semaine'])) {
            $period = 'week';
            $title = 'Rapport hebdomadaire';
        } elseif (Str::contains($normalized, ['rapport mensuel', 'rapport du mois', 'mois'])) {
            $period = 'month';
            $title = 'Rapport mensuel';
        } elseif (Str::contains($normalized, ['rapport annuel', 'rapport de l\'année', 'année', 'an'])) {
            $period = 'year';
            $title = 'Rapport annuel';
        } elseif (Str::contains($normalized, ['absent', 'retard', 'présence', 'présences', 'pointage', 'pointages'])) {
            $period = 'day';
            $title = 'Résumé de présence';
        }

        if ($period !== null) {
            $report = $this->generateReport($period);
            $texte = $this->formatReportText($report, $title);
            try {
                $excelUrl = $this->generateAndExport($period, 'excel');
                $texte .= "\n\n📥 **[Télécharger le rapport Excel (XLSX)](" . $excelUrl . ")**";
            } catch (\Throwable $e) {
                Log::error("Erreur lors de la génération Excel pour {$period} : " . $e->getMessage());
            }
            $this->createReportRecord($title, $texte);
            return $texte;
        }

        if (Str::contains($normalized, ['anomalie', 'fraude', 'suspicion', 'irrégulier', 'inconsist'])) {
            return $this->formatAnomaliesText($this->detectAnomalies('month'), 'Détection d\'anomalies');
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

        if ($filename === null) {
            $monthsFr = [
                1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
                7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'
            ];
            $month = $monthsFr[now()->month];
            $year  = now()->year;
            $day   = now()->day;

            if ($report['period'] === 'week') {
                // Interval: Du lundi au vendredi de la semaine
                $monday  = now()->startOfWeek(\Carbon\Carbon::MONDAY);
                $friday  = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(4);
                $from    = $monday->format('d');
                $to      = $friday->format('d');
                $mMonth  = $monthsFr[$friday->month];
                $filename = "Du-{$from}-au-{$to}_{$mMonth}-{$year}.xlsx";
            } elseif ($report['period'] === 'day') {
                $filename = "Journalier_{$day}-{$month}-{$year}.xlsx";
            } elseif ($report['period'] === 'year') {
                $filename = "Annuel_{$year}.xlsx";
            } else {
                $filename = "Mensuel_{$month}-{$year}.xlsx";
            }
        }
        $path = 'reports/' . $filename;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Résumé Global');

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

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A6')->getFont()->setBold(true);

        $row = 8;
        if (!empty($report['top_retards'])) {
            $sheet->setCellValue("A{$row}", 'Top retards');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue("A{$row}", 'Nom');
            $sheet->setCellValue("B{$row}", 'Retards');
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
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
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue("A{$row}", 'Nom');
            $sheet->setCellValue("B{$row}", 'Absences');
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            $row++;
            foreach ($report['top_absences'] as $item) {
                $sheet->setCellValue("A{$row}", $item['nom']);
                $sheet->setCellValue("B{$row}", $item['absences']);
                $row++;
            }
        }

        // Auto-dimensionnement des colonnes pour le premier onglet
        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Deuxième onglet : Détail Employés
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Détail Employés');

        // Titre de la feuille
        $sheet2->setCellValue('A1', "Détails de Présence des Employés ({$report['from']} → {$report['to']})");
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->mergeCells('A1:F1');

        // En-têtes du tableau
        $headers = ['Nom Complet', 'Département', 'Présences', 'Retards', 'Absences', 'Taux de Présence'];
        $colChar = 'A';
        foreach ($headers as $header) {
            $sheet2->setCellValue($colChar . '3', $header);
            $colChar++;
        }

        // Style des en-têtes
        $sheet2->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet2->getStyle('A3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet2->getStyle('A3:F3')->getFill()->getStartColor()->setARGB('FF4F81BD'); // Sleek blue
        $sheet2->getStyle('A3:F3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        // Récupérer tous les employés avec leurs pointages pour cette période
        $from = \Carbon\Carbon::parse($report['from'])->startOfDay();
        $to = \Carbon\Carbon::parse($report['to'])->endOfDay();
        
        $employes = \App\Models\Employe::with(['department', 'pointages' => function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }])->get();

        $rowNum = 4;
        foreach ($employes as $emp) {
            $presentsCount = $emp->pointages->where('status', 'Present')->count();
            $retardsCount = $emp->pointages->where('status', 'Retard')->count();
            $absencesCount = $emp->pointages->where('status', 'Absent')->count();
            $totalCount = $presentsCount + $retardsCount + $absencesCount;

            $attendanceRate = $totalCount > 0 
                ? round((($presentsCount + $retardsCount) / $totalCount) * 100, 1) . '%'
                : '0%';

            $sheet2->setCellValue("A{$rowNum}", $emp->getFullNameAttribute());
            $sheet2->setCellValue("B{$rowNum}", $emp->department ? $emp->department->name : 'N/A');
            $sheet2->setCellValue("C{$rowNum}", $presentsCount);
            $sheet2->setCellValue("D{$rowNum}", $retardsCount);
            $sheet2->setCellValue("E{$rowNum}", $absencesCount);
            $sheet2->setCellValue("F{$rowNum}", $attendanceRate);

            // Alternance de couleur (Zebra striping)
            if ($rowNum % 2 == 0) {
                $sheet2->getStyle("A{$rowNum}:F{$rowNum}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet2->getStyle("A{$rowNum}:F{$rowNum}")->getFill()->getStartColor()->setARGB('FFF9FAFB');
            }

            // Alignements
            $sheet2->getStyle("C{$rowNum}:F{$rowNum}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        // Auto-dimensionnement des colonnes pour le deuxième onglet
        foreach (range('A', 'F') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        $writer->save($tempFile);

        $stored = Storage::disk('public')->putFileAs('reports', new \Illuminate\Http\File($tempFile), $filename);
        // Cleanup temp
        @unlink($tempFile);

        return '/storage/' . $stored;
    }

    /**
     * Export a report as a PDF file and return the public URL.
     * Requires barryvdh/laravel-dompdf or dompdf/dompdf.
     *
     * @throws \RuntimeException
     */
    public function exportReportAsPdf(array $report, string $filename = null): string
    {
        if ($filename === null) {
            $monthsFr = [
                1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
                7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'
            ];
            $month = $monthsFr[now()->month];
            $year  = now()->year;
            $day   = now()->day;

            if ($report['period'] === 'week') {
                $monday  = now()->startOfWeek(\Carbon\Carbon::MONDAY);
                $friday  = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(4);
                $from    = $monday->format('d');
                $to      = $friday->format('d');
                $mMonth  = $monthsFr[$friday->month];
                $filename = "Du-{$from}-au-{$to}_{$mMonth}-{$year}.pdf";
            } elseif ($report['period'] === 'day') {
                $filename = "Journalier_{$day}-{$month}-{$year}.pdf";
            } elseif ($report['period'] === 'year') {
                $filename = "Annuel_{$year}.pdf";
            } else {
                $filename = "Mensuel_{$month}-{$year}.pdf";
            }
        }
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
            return '/storage/reports/' . $filename;
        }

        // Fallback to direct Dompdf
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();
            Storage::disk('public')->put('reports/' . $filename, $output);
            return '/storage/reports/' . $filename;
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
            $response = $client->generativeModel('gemini-2.5-flash')->generateContent($prompt);
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

    /**
     * Analyse un document importé (justificatif d'absence, etc.) via Gemini et met à jour la base de données.
     */
    public function analyzeUserRequestWithFile(string $question, string $filePath, string $mimeType): string
    {
        if (empty($this->apiKey)) {
            return "La clé Gemini n'est pas configurée. Veuillez l'ajouter à votre fichier .env.";
        }

        try {
            $client = \Gemini::client($this->apiKey);
            
            $fileData = base64_encode(file_get_contents($filePath));
            $blob = new \Gemini\Data\Blob(
                mimeType: $mimeType,
                data: $fileData
            );

            $prompt = "Tu es Horizon, l'assistant RH de C.H.R.A.C.E.R.H. "
                . "On te fournit un fichier joint. Analyse ce document (justificatif d'absence, certificat médical ou autre). "
                . "1. Détermine s'il s'agit d'un justificatif d'absence valide. "
                . "2. Extrait les informations clés suivantes sous forme textuelle propre : "
                . "   - Nom complet de l'employé concerné "
                . "   - Dates/Période de validité de la justification (début et fin) "
                . "   - Motif de l'absence "
                . "3. Rédige un diagnostic clair en indiquant si l'absence est justifiée ou non. "
                . "4. Ajoute une note à la fin au format JSON exact suivant si tu confirmes la justification : "
                . "   ```json\n"
                . "   {\"justification_valide\": true, \"nom_employe\": \"[Nom]\", \"date_debut\": \"YYYY-MM-DD\", \"date_fin\": \"YYYY-MM-DD\"}\n"
                . "   ```\n"
                . "Question/Instruction de l'utilisateur : " . $question;

            $response = $client->generativeModel('gemini-2.5-flash')->generateContent($prompt, $blob);
            $reponseTexte = trim($response->text() ?? 'Aucune réponse obtenue de Gemini.');

            if (preg_match('/\{"justification_valide"\s*:\s*true.*?\}/s', $reponseTexte, $matches)) {
                $jsonData = json_decode($matches[0], true);
                if ($jsonData) {
                    $nom = $jsonData['nom_employe'];
                    $debut = Carbon::parse($jsonData['date_debut']);
                    $fin = Carbon::parse($jsonData['date_fin']);

                    $names = explode(' ', $nom);
                    $query = Employe::query();
                    if (count($names) >= 2) {
                        $query->where(function($q) use ($names) {
                            $q->where('first_name', 'like', "%{$names[0]}%")
                              ->orWhere('last_name', 'like', "%{$names[1]}%")
                              ->orWhere('first_name', 'like', "%{$names[1]}%")
                              ->orWhere('last_name', 'like', "%{$names[0]}%");
                        });
                    } else {
                        $query->where('first_name', 'like', "%{$nom}%")
                              ->orWhere('last_name', 'like', "%{$nom}%");
                    }
                    $employe = $query->first();

                    if ($employe) {
                        $count = Pointage::where('employee_id', $employe->id)
                            ->whereBetween('created_at', [$debut->startOfDay(), $fin->endOfDay()])
                            ->where('status', 'Absent')
                            ->update(['status' => 'Present', 'anomaly_detected' => false]);

                        $reponseTexte .= "\n\n⚙️ **[Mise à jour automatique]** : L'employé **{$employe->first_name} {$employe->last_name}** a été identifié. {$count} pointage(s) d'absence sur la période du {$jsonData['date_debut']} au {$jsonData['date_fin']} ont été convertis en 'Présent' (justifié).";
                    } else {
                        $reponseTexte .= "\n\n⚠️ **[Mise à jour automatique]** : Le justificatif semble valide, mais aucun employé nommé '{$nom}' n'a été trouvé dans la base de données.";
                    }
                }
            }

            return $reponseTexte;
        } catch (\Throwable $e) {
            Log::error('HorizonAiService File Analysis error: ' . $e->getMessage());
            return "Désolé, je rencontre des difficultés pour analyser ce fichier. Détails de l'erreur : " . $e->getMessage();
        }
    }

    /**
     * Calcule le score de risque d'absentéisme pour chaque employé.
     */
    public function predictAbsenteeismRisk(): array
    {
        $employes = Employe::with('department')->get();
        $dateLim = Carbon::now()->subDays(30);

        $predictions = [];
        foreach ($employes as $emp) {
            $pointages = Pointage::where('employee_id', $emp->id)
                ->where('created_at', '>=', $dateLim)
                ->get();

            $absences = $pointages->where('status', 'Absent')->count();
            $retards = $pointages->where('status', 'Retard')->count();
            $total = $pointages->count();

            $score = 0;
            if ($total > 0) {
                $score = ($retards * 10) + ($absences * 25);
            }
            $score = min($score, 100);

            $niveau = 'Faible';
            $couleur = 'green';
            if ($score >= 60) {
                $niveau = 'Critique';
                $couleur = 'red';
            } elseif ($score >= 30) {
                $niveau = 'Modéré';
                $couleur = 'orange';
            }

            $raisons = [];
            if ($absences > 0) {
                $raisons[] = "{$absences} absence(s) ce mois-ci.";
            }
            if ($retards > 0) {
                $raisons[] = "{$retards} retard(s) ce mois-ci.";
            }
            if (empty($raisons)) {
                $raisons[] = "Pointages réguliers et assiduité parfaite.";
            }

            $predictions[] = [
                'employe_id' => $emp->id,
                'nom' => $emp->getFullNameAttribute(),
                'departement' => $emp->department ? $emp->department->name : 'N/A',
                'retards' => $retards,
                'absences' => $absences,
                'score' => $score,
                'niveau' => $niveau,
                'couleur' => $couleur,
                'raisons' => implode(' ', $raisons)
            ];
        }

        usort($predictions, fn($a, $b) => $b['score'] <=> $a['score']);

        return $predictions;
    }
}
