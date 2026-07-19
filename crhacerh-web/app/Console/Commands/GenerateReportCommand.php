<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;

class GenerateReportCommand extends Command
{
    protected $signature = 'reports:generate {--period=weekly : weekly|monthly|yearly}';

    protected $description = 'Génère automatiquement un rapport périodique au format Excel ou PDF sans IA.';

    public function handle(): int
    {
        $period = $this->option('period');
        $validPeriods = ['weekly', 'monthly', 'yearly', 'week', 'month', 'year'];

        if (!in_array($period, $validPeriods, true)) {
            $this->error("Période invalide : {$period}. Les valeurs acceptées sont : weekly, monthly, yearly.");
            return self::FAILURE;
        }

        $service = new ReportService();

        try {
            $normalized = match ($period) {
                'weekly' => 'week',
                'monthly' => 'month',
                'yearly' => 'year',
                default => $period,
            };

            $url = $service->generateAndExport($normalized, 'excel');
            $this->info("Rapport de présence ({$period}) généré avec succès. URL : {$url}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erreur lors de la génération du rapport : " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
