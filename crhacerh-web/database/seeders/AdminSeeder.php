<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employe;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Création des départements si inexistants
        $depts = [
            'Informatique',
            'Ressources Humaines',
            'Comptabilité',
            'Marketing & Ventes',
            'Direction',
            'Sécurité & Logistique'
        ];

        $departmentModels = [];
        foreach ($depts as $name) {
            $departmentModels[$name] = \App\Models\Department::firstOrCreate(['name' => $name]);
        }

        // 2. Hashage unique pour de meilleures performances
        $defaultPassword = Hash::make('password123');
        $adminPassword = Hash::make('admin123');

        // 3. Création des administrateurs et employés de base
        Employe::firstOrCreate(
            ['email' => 'admin@crhacerh.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'RH',
                'matricule' => 'chr-2020-0001',
                'password' => $adminPassword,
                'role' => 'RH_Admin',
                'department_id' => $departmentModels['Ressources Humaines']->id,
                'consent_facial_at' => now(),
            ]
        );

        Employe::firstOrCreate(
            ['email' => 'jean.dupont@entreprise.com'],
            [
                'first_name' => 'Mopi',
                'last_name' => 'Sonkouat',
                'matricule' => 'chr-2020-0002',
                'password' => $defaultPassword,
                'role' => 'Employés',
                'department_id' => $departmentModels['Direction']->id,
                'consent_facial_at' => now(),
            ]
        );

        // 4. Création de 50 employés fictifs avec départements
        $firstNames = ['Jean', 'Marie', 'Pierre', 'Michel', 'Philippe', 'Thomas', 'Nicolas', 'Antoine', 'Lucas', 'Alexandre', 'Julien', 'Guillaume', 'Romain', 'Maxime', 'Florian', 'David', 'Vincent', 'Olivier', 'Sébastien', 'Christophe', 'Sophie', 'Isabelle', 'Nathalie', 'Valerie', 'Sandrine', 'Sylvie', 'Catherine', 'Monique', 'Françoise', 'Christine', 'Julie', 'Camille', 'Sarah', 'Pauline', 'Mathilde', 'Charlotte', 'Marine', 'Elodie', 'Laura', 'Audrey', 'Emilie', 'Lea', 'Clara', 'Manon', 'Chloe', 'Ines', 'Emma', 'Jade', 'Alice', 'Lola'];
        $lastNames = ['Martin', 'Bernard', 'Thomas', 'Petit', 'Robert', 'Richard', 'Durand', 'Dubois', 'Moreau', 'Laurent', 'Simon', 'Michel', 'Lefebvre', 'Leroy', 'Roux', 'David', 'Bertrand', 'Morel', 'Fournier', 'Girard', 'Bonnet', 'Dupont', 'Lambert', 'Fontaine', 'Rousseau', 'Vincent', 'Muller', 'Lefevre', 'Faure', 'Andre', 'Mercier', 'Blanc', 'Guerin', 'Boyer', 'Chevalier', 'Francois', 'Mathieu', 'Denis', 'Clement', 'Brun', 'Gautier', 'Clerc', 'Lucas', 'Dufour', 'Simon', 'Lemoine', 'Roussel', 'Garnier', 'Morin', 'Lacroix'];

        $deptKeys = array_keys($departmentModels);

        for ($i = 0; $i < 50; $i++) {
            $firstName = $firstNames[$i % count($firstNames)];
            $lastName = $lastNames[$i % count($lastNames)];
            $email = strtolower($firstName . '.' . $lastName . ($i + 3) . '@entreprise.com');
            $matricule = sprintf('chr-2026-%04d', $i + 3);
            
            // Assigner un département de manière cyclique ou aléatoire
            $deptName = $deptKeys[$i % count($deptKeys)];
            $deptId = $departmentModels[$deptName]->id;

            Employe::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'matricule' => $matricule,
                    'password' => $defaultPassword,
                    'role' => 'Employés',
                    'department_id' => $deptId,
                    'consent_facial_at' => now(),
                ]
            );
        }

        // 5. Simulation des absences, retards et présences pour tous les employés
        // Supprimer les anciens pointages pour repartir sur des données propres
        \App\Models\Pointage::query()->delete();

        $employeesToSimulate = Employe::all();

        // Générer une liste de dates sur les 30 derniers jours (hors week-ends)
        $dates = [];
        for ($d = 0; $d <= 30; $d++) {
            $date = \Carbon\Carbon::now()->subDays($d);
            if (!$date->isWeekend()) {
                $dates[] = $date;
            }
        }

        foreach ($employeesToSimulate as $emp) {
            // Définir des probabilités d'absence, retard et présence uniques et aléatoires pour chaque employé
            $probAbsent = rand(2, 25); // taux d'absence aléatoire entre 2% et 25%
            $probRetard = rand(5, 30); // taux de retard aléatoire entre 5% et 30%
            $probPresent = 100 - $probAbsent - $probRetard;

            foreach ($dates as $date) {
                $rand = rand(1, 100);

                if ($rand <= $probPresent) {
                    $status = 'Present';
                    $type = 'ENTREE';
                } elseif ($rand <= ($probPresent + $probRetard)) {
                    $status = 'Retard';
                    $type = 'ENTREE';
                } else {
                    $status = 'Absent';
                    $type = 'ENTREE';
                }

                // Déterminer l'heure exacte du pointage
                $pointageTime = $date->copy();
                if ($status === 'Present') {
                    $pointageTime->setTime(rand(7, 8), rand(0, 30), rand(0, 59));
                } elseif ($status === 'Retard') {
                    $pointageTime->setTime(rand(8, 10), rand(31, 59), rand(0, 59));
                } else {
                    // Absent : l'heure de constatation ou d'absence est en début de journée
                    $pointageTime->setTime(8, 30, 0);
                }

                // Création du pointage d'entrée
                \App\Models\Pointage::create([
                    'employee_id' => $emp->id,
                    'type' => $type,
                    'status' => $status,
                    'source' => ['QR', 'FACE', 'BIO', 'PIN'][array_rand(['QR', 'FACE', 'BIO', 'PIN'])],
                    'timestamp' => $pointageTime,
                    'latitude' => 4.051056 + (rand(-100, 100) / 100000.0), // Coordonnées géographiques réalistes (ex: Cameroun ou autre)
                    'longitude' => 9.767868 + (rand(-100, 100) / 100000.0),
                    'confidence_score' => $status === 'Absent' ? null : (rand(85, 100) / 100.0),
                    'anomaly_detected' => false,
                    'created_at' => $pointageTime,
                    'updated_at' => $pointageTime,
                ]);

                // Création d'un pointage de sortie (uniquement si présent ou en retard)
                if ($status === 'Present' || $status === 'Retard') {
                    $sortieTime = $date->copy()->setTime(rand(17, 19), rand(0, 59), rand(0, 59));
                    \App\Models\Pointage::create([
                        'employee_id' => $emp->id,
                        'type' => 'SORTIE',
                        'status' => $status,
                        'source' => ['QR', 'FACE', 'BIO', 'PIN'][array_rand(['QR', 'FACE', 'BIO', 'PIN'])],
                        'timestamp' => $sortieTime,
                        'latitude' => 4.051056 + (rand(-100, 100) / 100000.0),
                        'longitude' => 9.767868 + (rand(-100, 100) / 100000.0),
                        'confidence_score' => rand(85, 100) / 100.0,
                        'anomaly_detected' => false,
                        'created_at' => $sortieTime,
                        'updated_at' => $sortieTime,
                    ]);
                }
            }
        }
    }
}
