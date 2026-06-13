<?php

namespace App\Console\Commands;

use App\Services\HorizonAiService;
use Illuminate\Console\Command;

class HorizonReportCommand extends Command
{
    protected $signature = 'horizon:report {--period=weekly : weekly|monthly|yearly}';

    protected $description = 'Génère et exporte automatiquement un rapport XLSX (hebdomadaire ou mensuel).';

    public function handle(): int
    {
        $period = match ($this->option('period')) {
            'monthly' => 'month',
            'yearly'  => 'year',
            default   => 'week',
        };

        $service  = new HorizonAiService();
        $report   = $service->generateReport($period);

        try {
            $url = $service->generateAndExport($period, 'excel');
            $this->info("Rapport XLSX généré : {$url}");
        } catch (\Throwable $e) {
            $this->error("Erreur export XLSX : " . $e->getMessage());
            return self::FAILURE;
        }

        // Crée également l'entrée en base
        $title   = 'Rapport auto ' . ucfirst($period) . ' — ' . now()->translatedFormat('d F Y');
        $content = $service->formatReportText($report, $title);
        $service->createReportRecord($title, $content);

        $this->info("✅ Rapport {$period} généré et enregistré.");
        return self::SUCCESS;
    }
}
