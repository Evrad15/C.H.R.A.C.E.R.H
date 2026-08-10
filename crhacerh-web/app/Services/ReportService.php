<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\Pointage;
use App\Models\Rapport;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReportService
{
    private const HOURS_PER_ABSENCE = 8;

    /**
     * Normalise la période demandée (week/weekly/hebdo, month/monthly, year/yearly, day/daily).
     */
    public function normalizePeriod(string $period): string
    {
        return match (strtolower(trim($period))) {
            'weekly', 'week', 'hebdo', 'semaine' => 'week',
            'monthly', 'month', 'mensuel', 'mois' => 'month',
            'yearly', 'year', 'annuel', 'an' => 'year',
            default => 'day',
        };
    }

    /**
     * Calcule et génère les statistiques agrégées pour une période donnée.
     */
    public function generateReport(string $period, ?Carbon $endDate = null, ?int $employeeId = null): array
    {
        $period = $this->normalizePeriod($period);
        $range = $this->buildRange($period, $endDate);

        $query = Pointage::with(['employe.department'])
            ->whereBetween('created_at', [$range['from'], $range['to']]);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $records = $query->get();

        $totalEmployes = Employe::count();
        $present = $records->where('status', 'Present')->count();
        $absent = $records->where('status', 'Absent')->count();
        $late = $records->where('status', 'Retard')->count();
        $absenceHours = $absent * self::HOURS_PER_ABSENCE;

        $topRetards = $records
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

        $topAbsences = $records
            ->where('status', 'Absent')
            ->groupBy('employee_id')
            ->map(function ($items) {
                $employee = $items->first()->employe;
                $count = $items->count();
                return [
                    'nom' => $employee?->getFullNameAttribute() ?? 'Employé inconnu',
                    'absences' => $count,
                    'absence_hours' => $count * self::HOURS_PER_ABSENCE,
                ];
            })
            ->sortByDesc('absence_hours')
            ->values()
            ->take(5)
            ->all();

        $topAssidus = $records
            ->groupBy('employee_id')
            ->map(function ($items) {
                $employee = $items->first()->employe;
                $presentCount = $items->where('status', 'Present')->count();
                $lateCount = $items->where('status', 'Retard')->count();
                $absentCount = $items->where('status', 'Absent')->count();
                $total = $presentCount + $lateCount + $absentCount;

                if ($total === 0) {
                    return null;
                }

                return [
                    'nom' => $employee?->getFullNameAttribute() ?? 'Employé inconnu',
                    'departement' => $employee?->department?->name ?? 'N/A',
                    'taux_assiduite' => $this->formatAttendanceRate($presentCount, $lateCount, $total),
                    'presents' => $presentCount,
                    'retards' => $lateCount,
                    'absences' => $absentCount,
                    'absence_hours' => $absentCount * self::HOURS_PER_ABSENCE,
                ];
            })
            ->filter()
            ->sort(function ($a, $b) {
                if ($a['taux_assiduite'] === $b['taux_assiduite']) {
                    return $b['presents'] <=> $a['presents'];
                }
                return $b['taux_assiduite'] <=> $a['taux_assiduite'];
            })
            ->values()
            ->take(5)
            ->all();

        $employeeStats = null;
        if ($employeeId) {
            $employee = Employe::find($employeeId);
            $employeeStats = [
                'employee_id' => $employeeId,
                'name' => $employee?->getFullNameAttribute() ?? 'Employé inconnu',
                'total_records' => $records->count(),
                'present' => $present,
                'retard' => $late,
                'absent' => $absent,
                'absence_hours' => $absenceHours,
                'attendance_rate' => $this->formatAttendanceRate($present, $late, $present + $late + $absent),
            ];
        }

        return [
            'period' => $period,
            'from' => $range['from']->toDateString(),
            'to' => $range['to']->toDateString(),
            'total_employes' => $totalEmployes,
            'scope' => $employeeStats ? 'Employé ciblé' : 'Global',
            'enregistrements' => $records->count(),
            'present' => $present,
            'absent' => $absent,
            'retard' => $late,
            'absence_hours' => $absenceHours,
            'top_retards' => $topRetards,
            'top_absences' => $topAbsences,
            'top_assidus' => $topAssidus,
            'employee_stats' => $employeeStats,
        ];
    }

    /**
     * Formate un résumé textuel clair du rapport généré.
     */
    public function formatReportText(array $report, string $title = 'Rapport'): string
    {
        $summary = [
            "{$title} ({$report['from']} → {$report['to']})",
            "Portée : {$report['scope']}",
            "Effectif total : {$report['total_employes']}",
            "Enregistrements de présence : {$report['enregistrements']}",
            "Présents : {$report['present']}",
            "Absents : {$report['absent']}",
            "Retards : {$report['retard']}",
            "Heures d'absence estimées : {$report['absence_hours']}h",
            '',
        ];

        if (!empty($report['employee_stats'])) {
            $summary[] = 'Rapport ciblé sur : ' . ($report['employee_stats']['name'] ?? 'Employé inconnu');
            $summary[] = 'Taux d’assiduité : ' . ($report['employee_stats']['attendance_rate'] ?? '0%');
            $summary[] = 'Heures d’absence estimées : ' . ($report['employee_stats']['absence_hours'] ?? 0) . 'h';
            $summary[] = '';
        }

        if (!empty($report['top_assidus'])) {
            $summary[] = 'Top 5 des employés les plus assidus :';
            foreach ($report['top_assidus'] as $item) {
                $summary[] = "- {$item['nom']} ({$item['departement']}) : {$item['taux_assiduite']}% d'assiduité";
            }
            $summary[] = '';
        }

        if (!empty($report['top_retards'])) {
            $summary[] = 'Top 5 des retards :';
            foreach ($report['top_retards'] as $item) {
                $summary[] = "- {$item['nom']} : {$item['retards']} retard(s)";
            }
            $summary[] = '';
        }

        if (!empty($report['top_absences'])) {
            $summary[] = 'Top 5 des absences (heures estimées) :';
            foreach ($report['top_absences'] as $item) {
                $summary[] = "- {$item['nom']} : {$item['absence_hours']}h d'absence";
            }
        }

        return implode("\n", $summary);
    }

    /**
     * Enregistre le rapport dans la table `rapports`.
     */
    public function createReportRecord(string $title, string $content, ?Carbon $date = null): ?Rapport
    {
        $date = $date ?? Carbon::now();
        $owner = Employe::whereIn('role', ['RH_Admin', 'Manager'])->first() ?? Employe::first();

        if (!$owner) {
            Log::warning('ReportService : impossible de créer le rapport car aucun employé n\'a été trouvé.');
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
     * Exporte un rapport au format Excel (XLSX) dans un sous-dossier mensuel (ex: `reports/Juin 2026/`).
     *
     * @throws \RuntimeException
     */
    public function exportReportAsExcel(array $report, ?string $filename = null): string
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            throw new \RuntimeException('PhpSpreadsheet missing. Run: composer require phpoffice/phpspreadsheet');
        }

        $monthsFr = [
            1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
            7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'
        ];

        $reportDate = \Carbon\Carbon::parse($report['to']);
        $month = $monthsFr[$reportDate->month] ?? 'Mois';
        $year  = $reportDate->year;
        $day   = $reportDate->day;

        if ($filename === null) {
            if ($report['period'] === 'week') {
                $monday  = Carbon::parse($report['from']);
                $friday  = Carbon::parse($report['to']);
                $from    = $monday->format('d');
                $to      = $friday->format('d');
                $filename = "Du-{$from}-au-{$to}_{$month}-{$year}.xlsx";
            } elseif ($report['period'] === 'day') {
                $filename = "Journalier_{$day}-{$month}-{$year}.xlsx";
            } elseif ($report['period'] === 'year') {
                $filename = "Annuel_{$year}.xlsx";
            } else {
                $filename = "Mensuel_{$month}-{$year}.xlsx";
            }
        }

        $subFolder = "reports/{$month} {$year}";

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Résumé Global');

        $sheet->setCellValue('A1', "Rapport ({$report['from']} → {$report['to']})");
        $sheet->setCellValue('A2', 'Portée');
        $sheet->setCellValue('B2', $report['scope'] ?? 'Global');
        $sheet->setCellValue('A3', 'Total employés');
        $sheet->setCellValue('B3', $report['total_employes']);
        $sheet->setCellValue('A4', 'Enregistrements');
        $sheet->setCellValue('B4', $report['enregistrements']);
        $sheet->setCellValue('A5', 'Présents');
        $sheet->setCellValue('B5', $report['present']);
        $sheet->setCellValue('A6', 'Absents');
        $sheet->setCellValue('B6', $report['absent']);
        $sheet->setCellValue('A7', 'Retards');
        $sheet->setCellValue('B7', $report['retard']);
        $sheet->setCellValue('A8', 'Heures d’absence estimées');
        $sheet->setCellValue('B8', $report['absence_hours'] . 'h');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A8')->getFont()->setBold(true);

        $row = 10;
        if (!empty($report['top_assidus'])) {
            $sheet->setCellValue("A{$row}", 'Top 5 des employés les plus assidus');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue("A{$row}", 'Nom');
            $sheet->setCellValue("B{$row}", 'Département');
            $sheet->setCellValue("C{$row}", 'Taux d’assiduité');
            $sheet->setCellValue("D{$row}", 'Présences');
            $sheet->setCellValue("E{$row}", 'Retards');
            $sheet->setCellValue("F{$row}", 'Absences');
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            $row++;
            foreach ($report['top_assidus'] as $item) {
                $sheet->setCellValue("A{$row}", $item['nom']);
                $sheet->setCellValue("B{$row}", $item['departement']);
                $sheet->setCellValue("C{$row}", $item['taux_assiduite'] . '%');
                $sheet->setCellValue("D{$row}", $item['presents']);
                $sheet->setCellValue("E{$row}", $item['retards']);
                $sheet->setCellValue("F{$row}", $item['absences']);
                $row++;
            }
            $row++;
        }

        if (!empty($report['top_absences'])) {
            $sheet->setCellValue("A{$row}", 'Top 5 des absences en heures');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue("A{$row}", 'Nom');
            $sheet->setCellValue("B{$row}", 'Absences');
            $sheet->setCellValue("C{$row}", 'Heures estimées');
            $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
            $row++;
            foreach ($report['top_absences'] as $item) {
                $sheet->setCellValue("A{$row}", $item['nom']);
                $sheet->setCellValue("B{$row}", $item['absences']);
                $sheet->setCellValue("C{$row}", $item['absence_hours'] . 'h');
                $row++;
            }
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Détail Employés');

        $sheet2->setCellValue('A1', "Détails de Présence des Employés ({$report['from']} → {$report['to']})");
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->mergeCells('A1:G1');

        $headers = ['Nom Complet', 'Département', 'Présences', 'Retards', 'Absences', 'Heures d’absence', 'Taux de Présence'];
        $colChar = 'A';
        foreach ($headers as $header) {
            $sheet2->setCellValue($colChar . '3', $header);
            $colChar++;
        }

        $sheet2->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet2->getStyle('A3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet2->getStyle('A3:F3')->getFill()->getStartColor()->setARGB('FF4F81BD');
        $sheet2->getStyle('A3:F3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        $from = Carbon::parse($report['from'])->startOfDay();
        $to = Carbon::parse($report['to'])->endOfDay();

        $selectedEmployeeId = $report['employee_stats']['employee_id'] ?? null;

        $employes = Employe::when($selectedEmployeeId, function ($query) use ($selectedEmployeeId) {
            $query->where('id', $selectedEmployeeId);
        })->with(['department', 'pointages' => function ($query) use ($from, $to) {
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
            $sheet2->setCellValue("F{$rowNum}", $absencesCount * self::HOURS_PER_ABSENCE . 'h');
            $sheet2->setCellValue("G{$rowNum}", $attendanceRate);

            if ($rowNum % 2 == 0) {
                $sheet2->getStyle("A{$rowNum}:F{$rowNum}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet2->getStyle("A{$rowNum}:F{$rowNum}")->getFill()->getStartColor()->setARGB('FFF9FAFB');
            }

            $sheet2->getStyle("C{$rowNum}:F{$rowNum}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = sys_get_temp_dir() . '/' . ($filename ?? 'report.xlsx');
        $writer->save($tempFile);

        $stored = Storage::disk('public')->putFileAs($subFolder, new \Illuminate\Http\File($tempFile), $filename);
        @unlink($tempFile);

        return '/storage/' . $stored;
    }

    /**
     * Exporte un rapport au format PDF dans un sous-dossier mensuel.
     *
     * @throws \RuntimeException
     */
    public function exportReportAsPdf(array $report, ?string $filename = null): string
    {
        $monthsFr = [
            1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
            7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'
        ];

        $reportDate = \Carbon\Carbon::parse($report['to']);
        $month = $monthsFr[$reportDate->month] ?? 'Mois';
        $year  = $reportDate->year;
        $day   = $reportDate->day;

        if ($filename === null) {
            if ($report['period'] === 'week') {
                $monday  = Carbon::parse($report['from']);
                $friday  = Carbon::parse($report['to']);
                $from    = $monday->format('d');
                $to      = $friday->format('d');
                $filename = "Du-{$from}-au-{$to}_{$month}-{$year}.pdf";
            } elseif ($report['period'] === 'day') {
                $filename = "Journalier_{$day}-{$month}-{$year}.pdf";
            } elseif ($report['period'] === 'year') {
                $filename = "Annuel_{$year}.pdf";
            } else {
                $filename = "Mensuel_{$month}-{$year}.pdf";
            }
        }

        $subFolder = "reports/{$month} {$year}";
        $path = $subFolder . '/' . $filename;

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<style>';
        $html .= 'body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: #1F384C; padding: 20px; font-size: 13px; }';
        $html .= 'h1 { color: #3A5BF0; font-size: 20px; margin-bottom: 5px; }';
        $html .= '.subtitle { color: #64748B; font-size: 12px; margin-bottom: 25px; }';
        $html .= '.stats-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }';
        $html .= '.stats-table th, .stats-table td { border: 1px solid #E2E8F0; padding: 10px; text-align: left; }';
        $html .= '.stats-table th { background: #F8FAFC; color: #1F384C; font-weight: bold; }';
        $html .= '.badge-present { color: #0E9F2D; font-weight: bold; }';
        $html .= '.badge-absent { color: #E81123; font-weight: bold; }';
        $html .= '.badge-retard { color: #D97300; font-weight: bold; }';
        $html .= '</style></head><body>';
        $html .= '<h1>C.H.R.A.C.E.R.H — Rapport de Présence</h1>';
        $html .= '<div class="subtitle">Période du ' . e($report['from']) . ' au ' . e($report['to']) . '</div>';

        $html .= '<table class="stats-table">';
        $html .= '<tr><th>Métrique</th><th>Valeur</th></tr>';
        $html .= '<tr><td>Total employés</td><td>' . $report['total_employes'] . '</td></tr>';
        $html .= '<tr><td>Total enregistrements</td><td>' . $report['enregistrements'] . '</td></tr>';
        $html .= '<tr><td>Présents</td><td class="badge-present">' . $report['present'] . '</td></tr>';
        $html .= '<tr><td>Retards</td><td class="badge-retard">' . $report['retard'] . '</td></tr>';
        $html .= '<tr><td>Absents</td><td class="badge-absent">' . $report['absent'] . '</td></tr>';
        $html .= '</table>';

        if (!empty($report['top_retards'])) {
            $html .= '<h3>Top 5 des retards</h3>';
            $html .= '<table class="stats-table"><tr><th>Nom Complet</th><th>Nombre de Retards</th></tr>';
            foreach ($report['top_retards'] as $item) {
                $html .= '<tr><td>' . e($item['nom']) . '</td><td>' . e($item['retards']) . '</td></tr>';
            }
            $html .= '</table>';
        }

        if (!empty($report['top_absences'])) {
            $html .= '<h3>Top 5 des absences</h3>';
            $html .= '<table class="stats-table"><tr><th>Nom Complet</th><th>Nombre d\'Absences</th><th>Heures estimées</th></tr>';
            foreach ($report['top_absences'] as $item) {
                $html .= '<tr><td>' . e($item['nom']) . '</td><td>' . e($item['absences']) . '</td><td>' . e($item['absence_hours']) . 'h</td></tr>';
            }
            $html .= '</table>';
        }
        $html .= '</body></html>';

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('A4', 'portrait');
            Storage::disk('public')->put($path, $pdf->output());
            return '/storage/' . $path;
        }

        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();
            Storage::disk('public')->put($path, $output);
            return '/storage/' . $path;
        }

        // Fallback sans Dompdf : sauvegarde en HTML lisible direct
        Storage::disk('public')->put($path, $html);
        return '/storage/' . $path;
    }

    /**
     * Génère et exporte un rapport complet, puis l'enregistre en BDD.
     */
    public function generateAndExport(string $period, string $format = 'excel', ?Carbon $endDate = null, ?int $employeeId = null): string
    {
        $period = $this->normalizePeriod($period);
        $report = $this->generateReport($period, $endDate, $employeeId);
        
        $title = match ($period) {
            'week' => 'Rapport hebdomadaire',
            'month' => 'Rapport mensuel',
            'year' => 'Rapport annuel',
            default => 'Rapport journalier',
        };

        if (!empty($report['employee_stats'])) {
            $title .= ' - ' . ($report['employee_stats']['name'] ?? 'Employé');
        }

        $content = $this->formatReportText($report, $title);
        $this->createReportRecord($title, $content, $endDate);

        return match (strtolower($format)) {
            'pdf' => $this->exportReportAsPdf($report),
            default => $this->exportReportAsExcel($report),
        };
    }

    protected function formatAttendanceRate(int $present, int $late, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round((($present + $late) / $total) * 100, 1);
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
