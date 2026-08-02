<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckDailyAbsences extends Command
{
    protected $signature = 'attendance:check-absences';
    protected $description = 'Enregistre automatiquement les employés absents ou en congé en fin de journée';

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

            // 3. S'il n'a rien enregistré
            if (!$aPointe) {
                // Vérifier s'il est en congé approuvé aujourd'hui
                $enConge = DB::table('conges')
                    ->where('employe_id', $e->id)
                    ->where('statut', 'Approuvé')
                    ->whereDate('date_debut', '<=', $aujourdHui)
                    ->whereDate('date_fin', '>=', $aujourdHui)
                    ->exists();

                $statutFinal = $enConge ? 'Congé' : 'Absent';

                DB::table('pointages')->insert([
                    'employee_id'      => $e->id,
                    'type'             => 'ENTREE',
                    'status'           => $statutFinal,
                    'source'           => 'SYSTEME',
                    'timestamp'        => Carbon::now(),
                    'confidence_score' => 1.00,
                    'anomaly_detected' => false,
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now()
                ]);
            }
        }

        $this->info('Vérification des absences et congés terminée !');
        return Command::SUCCESS;
    }
}