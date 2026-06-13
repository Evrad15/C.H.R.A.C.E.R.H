<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Analyse quotidienne à 06h00
        $schedule->command('horizon:analyze --period=daily')->dailyAt('06:00');

        // Rapport hebdomadaire XLSX — tous les vendredis à 18h00
        $schedule->command('horizon:report --period=weekly')->weeklyOn(5, '18:00');

        // Rapport mensuel XLSX — dernier jour du mois à 23h30
        $schedule->command('horizon:report --period=monthly')->lastDayOfMonth('23:30');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
