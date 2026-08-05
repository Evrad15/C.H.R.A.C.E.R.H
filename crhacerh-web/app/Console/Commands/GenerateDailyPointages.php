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
     * ex: php artisan pointages:generate-daily --from=2026-08-01 --to=2026-08-05 --force
     * ex: php artisan pointages:generate-daily --days=7 --force
     */
    protected $signature = 'pointages:generate-daily 
                            {--date= : La date cible (format YYYY-MM-DD)} 
                            {--from= : Date de début (format YYYY-MM-DD)} 
                            {--to= : Date de fin (format YYYY-MM-DD)} 
                            {--days= : Nombre de jours en arrière à générer jusqu\'à aujourd\'hui} 
                            {--force : Force la régénération même si des pointages existent déjà}';

    /**
     * La description de la commande console.
     */
    protected $description = 'Génère de manière dynamique les pointages journaliers avec prise en compte des congés approuvés (Congé au lieu de Absent)';

    public function handle()
    {
        $employes = DB::table('employés')->whereNull('deleted_at')->get();

        if ($employes->isEmpty()) {
            $this->warn("Aucun employé trouvé dans la base de données.");
            return Command::FAILURE;
        }

        $datesToGenerate = [];

        if ($this->option('from') && $this->option('to')) {
            $start = Carbon::parse($this->option('from'));
            $end   = Carbon::parse($this->option('to'));
            while ($start->lte($end)) {
                $datesToGenerate[] = $start->copy();
                $start->addDay();
            }
        } elseif ($this->option('days')) {
            $count = (int) $this->option('days');
            $start = Carbon::today()->subDays($count - 1);
            $end   = Carbon::today();
            while ($start->lte($end)) {
                $datesToGenerate[] = $start->copy();
                $start->addDay();
            }
        } elseif ($this->option('date')) {
            $datesToGenerate[] = Carbon::parse($this->option('date'));
        } else {
            $datesToGenerate[] = Carbon::today();
        }

        $force = (bool) $this->option('force');

        foreach ($datesToGenerate as $dateTarget) {
            $this->generateForSingleDate($dateTarget, $employes, $force);
        }

        return Command::SUCCESS;
    }

    protected function generateForSingleDate(Carbon $dateTarget, $employes, bool $force)
    {
        $dateStr = $dateTarget->toDateString();
        $isWeekend = $dateTarget->isWeekend();

        $this->info("Génération des pointages pour le {$dateTarget->locale('fr')->isoFormat('dddd D MMMM YYYY')} ({$dateStr})...");

        // Vérifier si des pointages existent déjà pour cette date
        $existCount = DB::table('pointages')
            ->whereDate('created_at', $dateStr)
            ->count();

        if ($existCount > 0 && !$force) {
            $this->warn("{$existCount} pointages existent déjà pour le {$dateStr}. Utilisez --force pour régénérer.");
            return;
        }

        if ($existCount > 0 && $force) {
            DB::table('pointages')->whereDate('created_at', $dateStr)->delete();
            $this->line("Anciens pointages supprimés pour le {$dateStr}.");
        }

        $insertedCount = 0;
        $cntPresent = 0;
        $cntRetard = 0;
        $cntAbsent = 0;
        $cntConge = 0;

        // Pré-charger tous les congés approuvés valides pour cette date
        $congesApprouvesIds = DB::table('conges')
            ->where('statut', 'Approuvé')
            ->whereDate('date_debut', '<=', $dateStr)
            ->whereDate('date_fin', '>=', $dateStr)
            ->pluck('employe_id')
            ->toArray();

        if ($isWeekend) {
            $pctVolontaires = rand(15, 35);
            $this->line("🗓️ Week-end (Non obligatoire) : ~{$pctVolontaires}% d'employés venus travailler. Aucun marquage d'absence.");

            foreach ($employes as $emp) {
                if (in_array($emp->id, $congesApprouvesIds)) {
                    $cntConge++;
                    $timeConge = (clone $dateTarget)->setTime(8, 0, 0);

                    DB::table('pointages')->insert([
                        'employee_id'      => $emp->id,
                        'type'             => 'ENTREE',
                        'status'           => 'Congé',
                        'source'           => 'SYSTEME',
                        'timestamp'        => $timeConge,
                        'confidence_score' => 1.00,
                        'anomaly_detected' => false,
                        'created_at'       => $timeConge,
                        'updated_at'       => $timeConge,
                    ]);
                    $insertedCount++;
                    continue;
                }

                if (rand(1, 100) <= $pctVolontaires) {
                    $cntPresent++;
                    $entreeHour = rand(7, 9);
                    $entreeMin  = rand(0, 59);
                    $timeEntree = (clone $dateTarget)->setTime($entreeHour, $entreeMin, rand(0, 59));

                    $sortieHour = rand(13, 17);
                    $sortieMin  = rand(0, 59);
                    $timeSortie = (clone $dateTarget)->setTime($sortieHour, $sortieMin, rand(0, 59));

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

            $this->info("✅ Résultat Week-end ({$dateStr}) : {$cntPresent} employés venus, {$cntConge} en Congé.");

        } else {
            $pctPresentThreshold = rand(65, 85);
            $retardShare = rand(10, 20);
            $pctRetardThreshold = min(95, $pctPresentThreshold + $retardShare);

            $this->line("📊 Profil Semaine ({$dateStr}) : Présents ≤ {$pctPresentThreshold}%, Retards ≤ {$pctRetardThreshold}%");

            foreach ($employes as $emp) {
                if (in_array($emp->id, $congesApprouvesIds)) {
                    $cntConge++;
                    $timeConge = (clone $dateTarget)->setTime(8, 0, 0);

                    DB::table('pointages')->insert([
                        'employee_id'      => $emp->id,
                        'type'             => 'ENTREE',
                        'status'           => 'Congé',
                        'source'           => 'SYSTEME',
                        'timestamp'        => $timeConge,
                        'confidence_score' => 1.00,
                        'anomaly_detected' => false,
                        'created_at'       => $timeConge,
                        'updated_at'       => $timeConge,
                    ]);

                    $insertedCount++;
                    continue;
                }

                $rand = rand(1, 100);

                if ($rand <= $pctPresentThreshold) {
                    $cntPresent++;
                    $entreeHour = rand(7, 7);
                    $entreeMin  = rand(10, 59);
                    $timeEntree = (clone $dateTarget)->setTime($entreeHour, $entreeMin, rand(0, 59));

                    $sortieHour = rand(16, 17);
                    $sortieMin  = rand(0, 50);
                    $timeSortie = (clone $dateTarget)->setTime($sortieHour, $sortieMin, rand(0, 59));

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
                    $cntRetard++;
                    $entreeHour = rand(8, 10);
                    $entreeMin  = rand(1, 55);
                    $timeEntree = (clone $dateTarget)->setTime($entreeHour, $entreeMin, rand(0, 59));

                    $sortieHour = rand(16, 18);
                    $sortieMin  = rand(0, 30);
                    $timeSortie = (clone $dateTarget)->setTime($sortieHour, $sortieMin, rand(0, 59));

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

            $this->info("✅ Résultat Semaine ({$dateStr}) : {$cntPresent} Présents, {$cntRetard} Retards, {$cntConge} Congés, {$cntAbsent} Absents.");
        }
    }
}
