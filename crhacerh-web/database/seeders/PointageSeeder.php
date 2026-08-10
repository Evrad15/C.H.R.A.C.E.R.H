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
        $employes = Employe::all();
        if ($employes->isEmpty()) {
            return;
        }

        $pointages = [];
        // Créer des pointages pour les 7 derniers jours
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            // On fait pointer aléatoirement 40 employés sur 54 par jour
            $shuffled = $employes->shuffle()->take(40);
            
            foreach ($shuffled as $employe) {
                // Aléatoire : Présent (80%) ou Retard (20%)
                $status = rand(1, 100) > 80 ? 'Retard' : 'Present';
                $heure = $status === 'Present' ? rand(7, 8) . ':' . rand(10, 59) : rand(9, 11) . ':' . rand(10, 59);
                
                $pointages[] = [
                    'employee_id' => $employe->id,
                    'type' => 'ENTREE',
                    'source' => rand(0, 1) ? 'Facial' : 'Empreinte',
                    'status' => $status,
                    'created_at' => $date->copy()->setTime((int) explode(':', $heure)[0], (int) explode(':', $heure)[1]),
                    'updated_at' => $date->copy()->setTime((int) explode(':', $heure)[0], (int) explode(':', $heure)[1]),
                ];
            }
        }

        DB::table('pointages')->insert($pointages);
    }
}
