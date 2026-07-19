<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
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
