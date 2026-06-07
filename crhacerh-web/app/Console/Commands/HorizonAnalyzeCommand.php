<?php

namespace App\Console\Commands;

use App\Services\HorizonAiService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class HorizonAnalyzeCommand extends Command
{
    protected $signature = 'horizon:analyze {--period=daily : Période d\'analyse (daily|weekly|monthly|yearly)}';

    protected $description = 'Analyse les présences, génère un rapport et envoie une notification en cas d\'anomalies.';

    public function handle(): int
    {
        $period = $this->option('period');
        $service = new HorizonAiService();

        $report = $service->generateReport($this->normalizePeriod($period));
        $anomalies = $service->detectAnomalies($this->normalizePeriod($period));

        $title = 'Rapport automatique Horizon - ' . ucfirst($this->normalizePeriod($period));
        $content = $service->formatReportText($report, $title);
        $service->createReportRecord($title, $content, Carbon::now());

        if ($anomalies['count'] > 0) {
            $notificationBody = $service->formatAnomaliesText($anomalies, 'Alertes détectées par Horizon');
            $service->sendNotification('Alerte RH - anomalies détectées', $notificationBody);
            $this->info('Anomalies détectées et notification envoyée.');
        } else {
            $this->info('Aucune anomalie critique détectée.');
        }

        $this->info('Analyse Horizon terminée pour la période : ' . $this->normalizePeriod($period));

        return self::SUCCESS;
    }

    protected function normalizePeriod(string $period): string
    {
        return match ($period) {
            'weekly' => 'week',
            'monthly' => 'month',
            'yearly' => 'year',
            default => 'day',
        };
    }
}
