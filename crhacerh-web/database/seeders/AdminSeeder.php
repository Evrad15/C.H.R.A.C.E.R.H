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
        // Clear existing records to avoid duplicates if necessary
        Employe::create([
            'first_name' => 'Admin',
            'last_name' => 'RH',
            'matricule' => 'chr-2020-0001',
            'email' => 'admin@crhacerh.com',
            'password' => Hash::make('admin123'), // Ou laisse Laravel le hasher automatiquement si configuré dans les casts
            'role' => 'RH_Admin', // Ton rôle d'administrateur
            'consent_facial_at' => now(),
        ]);

        Employe::create([
            'email' => 'jean.dupont@entreprise.com',
            'matricule' => 'chr-2020-0002',
            'password' => Hash::make('password123'),
            'first_name' => 'Mopi',
            'last_name' => 'Sonkouat',
            'role' => 'Employés',
        ]);
    }
}
