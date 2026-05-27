<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Pointage;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création d'un département
        $itDepartment = Department::create([
            'name' => 'Direction Informatique'
        ]);

        // 2. Création d'un compte Administrateur RH
        $admin = Employee::create([
            'first_name' => 'Mopi',
            'last_name' => 'Admin',
            'email' => 'admin@crhacerh.cm',
            'password' => Hash::make('Secret123'), // Sécurisation du mot de passe
            'role' => 'RH_Admin',
            'department_id' => $itDepartment->id,
            'phone' => '+237681964798'
        ]);

        // 3. Création d'un employé standard
        $employee = Employee::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@crhacerh.cm',
            'password' => Hash::make('Employee123'),
            'role' => 'Employee',
            'department_id' => $itDepartment->id,
            'phone' => '+237611111111'
        ]);

        // 4. Création de faux pointages pour l'employé (Entrée et Sortie)
        Pointage::create([
            'employee_id' => $employee->id,
            'type' => 'ENTREE',
            'source' => 'QR',
            'timestamp' => now()->setHour(8)->setMinute(0),
            'latitude' => 3.8480,  // Coordonnées de test (ex: Yaoundé)
            'longitude' => 11.5021,
            'confidence_score' => 1.00,
            'anomaly_detected' => false
        ]);

        Pointage::create([
            'employee_id' => $employee->id,
            'type' => 'SORTIE',
            'source' => 'FACE',
            'timestamp' => now()->setHour(17)->setMinute(0),
            'latitude' => 3.8485,
            'longitude' => 11.5025,
            'confidence_score' => 0.96, // Score de reconnaissance faciale
            'anomaly_detected' => false
        ]);
    }
}