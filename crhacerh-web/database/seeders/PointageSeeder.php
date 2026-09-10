<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employe;

class PointageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Vider entièrement la table des pointages
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE "pointages" RESTART IDENTITY CASCADE');
        } else {
            DB::table('pointages')->truncate();
        }

        $employes = Employe::all();
        if ($employes->isEmpty()) {
            return;
        }

        // 2. Définir les jours de la semaine courante (Lundi à Vendredi)
        $today = Carbon::today();
        $startOfWeek = (clone $today)->startOfWeek(Carbon::MONDAY);

        $daysOfWeek = [
            0 => 'Lundi',
            1 => 'Mardi',
            2 => 'Mercredi',
            3 => 'Jeudi',
            4 => 'Vendredi',
        ];

        $pointages = [];

        foreach ($employes as $employe) {
            $isMopi = (
                stripos($employe->last_name ?? '', 'MOPI') !== false ||
                stripos($employe->first_name ?? '', 'MOPI') !== false ||
                stripos($employe->email ?? '', 'mopao') !== false ||
                ($employe->matricule ?? '') === 'chr-2026-0053' ||
                ($employe->matricule ?? '') === 'chr-2020-0002'
            );

            foreach ($daysOfWeek as $dayIndex => $dayName) {
                $date = (clone $startOfWeek)->addDays($dayIndex);

                // Condition spéciale pour MOPI : aucun pointage le Jeudi (index 3) et Vendredi (index 4)
                if ($isMopi && in_array($dayIndex, [3, 4])) {
                    continue; // Pas de pointage jeudi et vendredi
                }

                // Pour MOPI le Lundi, Mardi, Mercredi : Assiduité exemplaire (Présent)
                if ($isMopi) {
                    $entreeHour = 7;
                    $entreeMin = rand(25, 45);
                    $entreeSec = rand(10, 50);
                    $timeEntree = (clone $date)->setTime($entreeHour, $entreeMin, $entreeSec);

                    $sortieHour = 17;
                    $sortieMin = rand(15, 45);
                    $sortieSec = rand(10, 50);
                    $timeSortie = (clone $date)->setTime($sortieHour, $sortieMin, $sortieSec);

                    $source = ($dayIndex % 2 === 0) ? 'Facial' : 'Empreinte';

                    $pointages[] = [
                        'employee_id'      => $employe->id,
                        'type'             => 'ENTREE',
                        'source'           => $source,
                        'status'           => 'Present',
                        'timestamp'        => $timeEntree,
                        'confidence_score' => 0.98,
                        'anomaly_detected' => false,
                        'created_at'       => $timeEntree,
                        'updated_at'       => $timeEntree,
                    ];

                    $pointages[] = [
                        'employee_id'      => $employe->id,
                        'type'             => 'SORTIE',
                        'source'           => $source,
                        'status'           => 'Present',
                        'timestamp'        => $timeSortie,
                        'confidence_score' => 0.98,
                        'anomaly_detected' => false,
                        'created_at'       => $timeSortie,
                        'updated_at'       => $timeSortie,
                    ];

                    continue;
                }

                // Pour les autres employés : ~90% de présence quotidienne
                $randParticipation = rand(1, 100);
                if ($randParticipation > 92) {
                    // Employé absent ce jour-là (aucun pointage)
                    continue;
                }

                // Répartition : 85% Présent à l'heure, 15% Retard
                $isRetard = rand(1, 100) > 85;
                $status = $isRetard ? 'Retard' : 'Present';

                if ($isRetard) {
                    $entreeHour = rand(8, 9);
                    $entreeMin = rand(31, 55);
                } else {
                    $entreeHour = 7;
                    $entreeMin = rand(15, 59);
                }
                $entreeSec = rand(0, 59);
                $timeEntree = (clone $date)->setTime($entreeHour, $entreeMin, $entreeSec);

                $sortieHour = rand(16, 18);
                $sortieMin = rand(0, 55);
                $sortieSec = rand(0, 59);
                $timeSortie = (clone $date)->setTime($sortieHour, $sortieMin, $sortieSec);

                $source = rand(0, 1) ? 'Facial' : 'Empreinte';
                $confidence = round(rand(950, 995) / 1000.0, 2);

                $pointages[] = [
                    'employee_id'      => $employe->id,
                    'type'             => 'ENTREE',
                    'source'           => $source,
                    'status'           => $status,
                    'timestamp'        => $timeEntree,
                    'confidence_score' => $confidence,
                    'anomaly_detected' => $isRetard,
                    'created_at'       => $timeEntree,
                    'updated_at'       => $timeEntree,
                ];

                $pointages[] = [
                    'employee_id'      => $employe->id,
                    'type'             => 'SORTIE',
                    'source'           => $source,
                    'status'           => 'Present',
                    'timestamp'        => $timeSortie,
                    'confidence_score' => $confidence,
                    'anomaly_detected' => false,
                    'created_at'       => $timeSortie,
                    'updated_at'       => $timeSortie,
                ];
            }
        }

        // Insertion par batch de 100 pour optimiser les performances
        foreach (array_chunk($pointages, 100) as $chunk) {
            DB::table('pointages')->insert($chunk);
        }
    }
}

