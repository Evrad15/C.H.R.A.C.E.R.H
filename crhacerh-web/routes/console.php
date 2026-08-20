<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── PROGRAMMATION AUTOMATIQUE DES TÂCHES DU CENTRE (CRON JOBS) ──

// 1. Rappels automatiques des rendez-vous par SMS (J-6, J-3, J-1) chaque matin à 08h00
Schedule::command('rdv:send-reminders')->dailyAt('08:00');

// 2. Clôture nocturne automatique des rendez-vous et verrouillage des accès biométriques à 20h00
Schedule::command('rdv:close-daily')->dailyAt('20:00');

