<?php

namespace Database\Seeders;

use App\Models\Employe; // MODIFIEZ ICI : Retirez l'accent et utilisez le bon namespace
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeSeeder extends Seeder
{
    public function run(): void
    {
        // Utilisez maintenant la classe Employe (sans accent)
        Employe::create([
            'email' => 'jean.dupont@entreprise.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'role' => 'Employés',
        ]);

        // ... répétez pour les autres
    }
}