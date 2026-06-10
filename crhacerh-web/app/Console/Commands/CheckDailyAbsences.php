<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckDailyAbsences extends Command
{
    protected $signature = 'attendance:check-absences';
    protected $description = 'Enregistre automatiquement les employés absents en fin de journée';

    public function handle()
    {
        $aujourdHui = Carbon::today()->toDateString();
        
        // On ignore les week-ends (Samedi = 6, Dimanche = 7)
        if (Carbon::today()->isWeekend()) {
            return Command::SUCCESS;
        }

        // 1. Récupérer tous les employés actifs
        $employes = DB::table('employés')->whereNull('deleted_at')->get();

        foreach ($employes as $e) {
            // 2. Vérifier si l'employé a au moins un pointage aujourd'hui
            $aPointe = DB::table('pointages')
                ->where('employee_id', $e->id)
                ->whereDate('timestamp', $aujourdHui)
                ->exists();

            // 3. S'il n'a rien enregistré, on crée un pointage AUTOMATIQUE "ABSENT"
            if (!$aPointe) {
                DB::table('pointages')->insert([
                    'employee_id' => $e->id,
                    'type' => 'Absent',       // Devient le type pour l'absence
                    'status' => 'Absent',     // Statut stocké
                    'source' => 'SYSTEME',
                    'timestamp' => Carbon::now(), // Aura la bonne heure UTC+1 désormais
                    'confidence_score' => 1.00,
                    'anomaly_detected' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }

        $this->info('Vérification des absences terminée !');
        return Command::SUCCESS;
    }
}