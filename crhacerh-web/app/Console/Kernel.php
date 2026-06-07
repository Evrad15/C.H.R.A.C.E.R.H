<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('horizon:analyze --period=daily')->dailyAt('06:00');
        $schedule->command('horizon:analyze --period=weekly')->weeklyOn(1, '07:00');
        $schedule->command('horizon:analyze --period=monthly')->monthlyOn(1, '08:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
