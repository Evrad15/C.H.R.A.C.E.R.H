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
    protected $description = 'Génère de manière dynamique les pointages journaliers (Semaine: obligatoire / Week-end: optionnel sans statut absent)';

    public function handle()
    {
        $dateOption = $this->option('date');
        $dateTarget = $dateOption ? Carbon::parse($dateOption) : Carbon::today();
        $dateStr = $dateTarget->toDateString();
        $isWeekend = $dateTarget->isWeekend();

        $this->info("Génération des pointages pour le {$dateTarget->locale('fr')->isoFormat('dddd D MMMM YYYY')} ({$dateStr})...");

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

        $insertedCount = 0;
        $cntPresent = 0;
        $cntRetard = 0;
        $cntAbsent = 0;

        if ($isWeekend) {
            // ==========================================
            // WEEK-END : PERIODE NON OBLIGATOIRE
            // ==========================================
            // Seuls les employés qui décident de venir enregistrent ENTREE/SORTIE.
            // Ceux qui ne viennent pas N'ONT AUCUN POINTAGE (Pas de statut "Absent", pas de pénalité).
            
            $pctVolontaires = rand(15, 35); // 15% à 35% d'employés qui viennent travailler le week-end
            $this->line("🗓️ Week-end (Non obligatoire) : ~{$pctVolontaires}% d'employés venus travailler. Aucun marquage d'absence.");

            foreach ($employes as $emp) {
                if (rand(1, 100) <= $pctVolontaires) {
                    $cntPresent++;
                    $entreeHour = rand(7, 9);
                    $entreeMin  = rand(0, 59);
                    $timeEntree = (clone $dateTarget)->setTime($entreeHour, $entreeMin, rand(0, 59));

                    $sortieHour = rand(13, 17);
                    $sortieMin  = rand(0, 59);
                    $timeSortie = (clone $dateTarget)->setTime($sortieHour, $sortieMin, rand(0, 59));

                    // ENTREE
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

                    // SORTIE
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
                }
            }

            $this->info("✅ Résultat Week-end ({$dateStr}) : {$cntPresent} employés venus (ENTREE/SORTIE), 0 marquage d'absence.");

        } else {
            // ==========================================
            // SEMAINE OUVRÉE : PERIODE OBLIGATOIRE (Lundi au Vendredi)
            // ==========================================
            $pctPresentThreshold = rand(45, 85);
            $retardShare = rand(10, 25);
            $pctRetardThreshold = min(95, $pctPresentThreshold + $retardShare);

            $this->line("📊 Profil Semaine ({$dateStr}) : Présents ≤ {$pctPresentThreshold}%, Retards ≤ {$pctRetardThreshold}%, Absents > {$pctRetardThreshold}%");

            foreach ($employes as $emp) {
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

                    // ENTREE
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

                    // SORTIE
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

                    // ENTREE (Retard)
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

                    // SORTIE
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
                    // ABSENT (Semaine uniquement)
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

            $this->info("✅ Résultat Semaine ({$dateStr}) : {$cntPresent} Présents, {$cntRetard} Retards, {$cntAbsent} Absents.");
        }

        return Command::SUCCESS;
    }
}
