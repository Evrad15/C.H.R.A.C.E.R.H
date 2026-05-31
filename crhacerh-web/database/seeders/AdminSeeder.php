<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employés; // 💡 On utilise ton modèle mis à jour
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employés::create([
            'first_name' => 'Admin',
            'last_name' => 'RH',
            'email' => 'admin@crhacerh.com',
            'password' => Hash::make('admin123'), // Ou laisse Laravel le hasher automatiquement si configuré dans les casts
            'role' => 'RH_Admin', // Ton rôle d'administrateur
            'consent_facial_at' => now(),
        ]);
    }
}