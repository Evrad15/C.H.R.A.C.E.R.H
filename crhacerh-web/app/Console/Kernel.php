<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Génération automatique quotidienne des pointages (chaque 24h à 08h00)
        $schedule->command('pointages:generate-daily')->dailyAt('08:00');

        // Rappel automatique SMS J-1 pour les patientes (tous les jours à 18h00)
        $schedule->command('rdv:send-reminders')->dailyAt('18:00');

        // Rapport hebdomadaire XLSX — tous les vendredis à 18h00 (classé dans le dossier du mois)
        $schedule->command('reports:generate --period=weekly')->weeklyOn(5, '18:00');

        // Rapport mensuel XLSX — dernier jour du mois à 23h30 (classé dans le dossier du mois)
        $schedule->command('reports:generate --period=monthly')->lastDayOfMonth('23:30');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
