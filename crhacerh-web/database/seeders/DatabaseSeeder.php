<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // On dit à Laravel d'exécuter ton AdminSeeder automatiquement
        $this->call([
            AdminSeeder::class,
        ]);
    }
}