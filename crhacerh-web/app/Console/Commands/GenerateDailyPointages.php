<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateDailyPointages extends Command
{
    /**
     * Le nom et la signature de la commande console.
     *
     * ex: php artisan pointages:generate-daily
     * ex: php artisan pointages:generate-daily --date=2026-08-03 --force
     */
    protected $signature = 'pointages:generate-daily 
                            {--date= : La date cible (format YYYY-MM-DD, défaut: aujourd\'hui)} 
                            {--force : Force la régénération même si des pointages existent déjà}';

    /**
     * La description de la commande console.
     */
    protected $description = 'Génère de manière dynamique et 100% aléatoire les pointages journaliers (toutes les 24h)';

    public function handle()
    {
        $dateOption = $this->option('date');
        $dateTarget = $dateOption ? Carbon::parse($dateOption) : Carbon::today();
        $dateStr = $dateTarget->toDateString();
        $isWeekend = $dateTarget->isWeekend();

        $this->info("Génération aléatoire des pointages pour le {$dateTarget->locale('fr')->isoFormat('dddd D MMMM YYYY')} ({$dateStr})...");

        // Récupérer tous les employés actifs
        $employes = DB::table('employés')->whereNull('deleted_at')->get();

        if ($employes->isEmpty()) {
            $this->warn("Aucun employé trouvé dans la base de données.");
            return Command::FAILURE;
        }

        // Vérifier si des pointages existent déjà pour cette date
        $existCount = DB::table('pointages')
            ->whereDate('created_at', $dateStr)
            ->count();

        if ($existCount > 0 && !$this->option('force')) {
            $this->warn("{$existCount} pointages existent déjà pour le {$dateStr}. Utilisez --force pour régénérer.");
            return Command::SUCCESS;
        }

        if ($existCount > 0 && $this->option('force')) {
            DB::table('pointages')->whereDate('created_at', $dateStr)->delete();
            $this->line("Anciens pointages supprimés pour le {$dateStr}.");
        }

        // --- PROFIL DE LA JOURNÉE TOTALEMENT ALÉATOIRE ---
        // Le taux de présence fluctue dynamiquement d'un jour à l'autre (ex: météo, garde, imprévus)
        if ($isWeekend) {
            // Le week-end : présence réduite (garde/astreinte ~ 20% à 45%)
            $pctPresentThreshold = rand(20, 45);
            $pctRetardThreshold  = $pctPresentThreshold + rand(5, 15);
        } else {
            // Semaine ouvrée : le taux de présence varie de 40% à 85% selon les jours
            // ex: certains jours il y aura beaucoup d'absents (40% de présence), d'autres très peu (85%)
            $pctPresentThreshold = rand(40, 85);
            // Le taux de retards varie aussi de 10% à 30%
            $retardShare = rand(10, 30);
            $pctRetardThreshold = min(95, $pctPresentThreshold + $retardShare);
        }

        $this->line("📊 Profil aléatoire du jour : Présents ≤ {$pctPresentThreshold}%, Retards ≤ {$pctRetardThreshold}%, Absents > {$pctRetardThreshold}%");

        $insertedCount = 0;
        $cntPresent = 0;
        $cntRetard = 0;
        $cntAbsent = 0;

        foreach ($employes as $emp) {
            // Tirage au sort individuel entre 1 et 100
            $rand = rand(1, 100);

            if ($rand <= $pctPresentThreshold) {
                // PRESENT (À l'heure)
                $cntPresent++;
                $entreeHour = rand(7, 7);
                $entreeMin  = rand(10, 59);
                $timeEntree = (clone $dateTarget)->setTime($entreeHour, $entreeMin, rand(0, 59));

                $sortieHour = rand(16, 17);
                $sortieMin  = rand(0, 50);
                $timeSortie = (clone $dateTarget)->setTime($sortieHour, $sortieMin, rand(0, 59));

                // Insérer ENTREE
                DB::table('pointages')->insert([
                    'employee_id'      => $emp->id,
                    'type'             => 'ENTREE',
                    'status'           => 'Present',
                    'source'           => 'SIMULATEUR',
                    'timestamp'        => $timeEntree,
                    'confidence_score' => 0.98,
                    'anomaly_detected' => false,
                    'created_at'       => $timeEntree,
                    'updated_at'       => $timeEntree,
                ]);

                // Insérer SORTIE
                DB::table('pointages')->insert([
                    'employee_id'      => $emp->id,
                    'type'             => 'SORTIE',
                    'status'           => 'Present',
                    'source'           => 'SIMULATEUR',
                    'timestamp'        => $timeSortie,
                    'confidence_score' => 0.98,
                    'anomaly_detected' => false,
                    'created_at'       => $timeSortie,
                    'updated_at'       => $timeSortie,
                ]);

                $insertedCount += 2;

            } elseif ($rand <= $pctRetardThreshold) {
                // RETARD
                $cntRetard++;
                $entreeHour = rand(8, 10);
                $entreeMin  = rand(1, 55);
                $timeEntree = (clone $dateTarget)->setTime($entreeHour, $entreeMin, rand(0, 59));

                $sortieHour = rand(16, 18);
                $sortieMin  = rand(0, 30);
                $timeSortie = (clone $dateTarget)->setTime($sortieHour, $sortieMin, rand(0, 59));

                // Insérer ENTREE (Retard)
                DB::table('pointages')->insert([
                    'employee_id'      => $emp->id,
                    'type'             => 'ENTREE',
                    'status'           => 'Retard',
                    'source'           => 'SIMULATEUR',
                    'timestamp'        => $timeEntree,
                    'confidence_score' => 0.95,
                    'anomaly_detected' => true,
                    'created_at'       => $timeEntree,
                    'updated_at'       => $timeEntree,
                ]);

                // Insérer SORTIE
                DB::table('pointages')->insert([
                    'employee_id'      => $emp->id,
                    'type'             => 'SORTIE',
                    'status'           => 'Present',
                    'source'           => 'SIMULATEUR',
                    'timestamp'        => $timeSortie,
                    'confidence_score' => 0.98,
                    'anomaly_detected' => false,
                    'created_at'       => $timeSortie,
                    'updated_at'       => $timeSortie,
                ]);

                $insertedCount += 2;

            } else {
                // ABSENT
                $cntAbsent++;
                $timeAbsent = (clone $dateTarget)->setTime(8, 0, 0);

                DB::table('pointages')->insert([
                    'employee_id'      => $emp->id,
                    'type'             => 'ENTREE',
                    'status'           => 'Absent',
                    'source'           => 'SIMULATEUR',
                    'timestamp'        => $timeAbsent,
                    'confidence_score' => 1.00,
                    'anomaly_detected' => false,
                    'created_at'       => $timeAbsent,
                    'updated_at'       => $timeAbsent,
                ]);

                $insertedCount += 1;
            }
        }

        $this->info("✅ Résultat du jour ({$dateStr}) : {$cntPresent} Présents, {$cntRetard} Retards, {$cntAbsent} Absents.");
        return Command::SUCCESS;
    }
}
