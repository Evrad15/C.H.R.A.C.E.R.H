<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Pointage;
use App\Models\Conge;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le Tableau de bord en temps réel pour la journée en cours.
     */
    public function index()
    {
        $today = Carbon::today();

        // 1. Total des employés dans la base
        $totalEmployes = Employe::count();

        // 2. Date d'analyse = Toujours la date du jour en cours
        $targetDate = $today;

        // 3. Calculs des 3 compteurs d'entrée du jour (aujourd'hui uniquement)
        $presentsCount = Pointage::whereDate('created_at', $targetDate)
            ->where('type', 'ENTREE')
            ->where('status', 'Present')
            ->count();

        $retardsCount = Pointage::whereDate('created_at', $targetDate)
            ->where('type', 'ENTREE')
            ->where('status', 'Retard')
            ->count();

        // Les absents du jour = Total employés - (Présents + Retards du jour)
        $absentsCount = max(0, $totalEmployes - ($presentsCount + $retardsCount));

        // Le chiffre central du Donut est le total des employés
        $donutTotal = $totalEmployes;

        // 4. Liste stricte des pointages du jour pour la table défilable (aucun fallback sur les anciens jours)
        $recentPointages = Pointage::with(['employee', 'employee.department'])
            ->whereDate('created_at', $targetDate)
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();

        // 5. Calcul de la semaine en cours (du Lundi au Dimanche de la semaine actuelle)
        $startOfWeek = (clone $today)->startOfWeek(Carbon::MONDAY);
        $endOfWeek = (clone $startOfWeek)->endOfWeek(Carbon::SUNDAY);

        $startOfPrevWeek = (clone $startOfWeek)->subWeek();
        $endOfPrevWeek = (clone $startOfWeek)->subDays(1)->endOfDay();

        $currWeekPresents = Pointage::whereBetween('created_at', [$startOfWeek, (clone $today)->endOfDay()])
            ->where('type', 'ENTREE')
            ->whereIn('status', ['Present', 'Retard'])
            ->count();

        $prevWeekPresents = Pointage::whereBetween('created_at', [$startOfPrevWeek, $endOfPrevWeek])
            ->where('type', 'ENTREE')
            ->whereIn('status', ['Present', 'Retard'])
            ->count();

        if ($prevWeekPresents > 0) {
            $trendPercent = round((($currWeekPresents - $prevWeekPresents) / $prevWeekPresents) * 100, 1);
        } else {
            $trendPercent = $currWeekPresents > 0 ? 100.0 : 0.0;
        }

        $isTrendPositive = $trendPercent >= 0;

        // 6. Données du graphique hebdomadaire pour la semaine réelle en cours (1 seule requête SQL groupée)
        $weeklyData = [];
        $dayNames = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        $weekStats = Pointage::whereBetween('created_at', [$startOfWeek, (clone $endOfWeek)->endOfDay()])
            ->where('type', 'ENTREE')
            ->selectRaw('DATE(created_at) as date_val, status, count(*) as count')
            ->groupBy('date_val', 'status')
            ->get()
            ->groupBy('date_val');

        for ($i = 0; $i < 7; $i++) {
            $currentDay = (clone $startOfWeek)->addDays($i);
            $dateKey = $currentDay->toDateString();
            $isPastOrToday = $currentDay->lte($today);

            if ($isPastOrToday) {
                $dayGroup = $weekStats->get($dateKey, collect());
                $dayPresents = (int)($dayGroup->firstWhere('status', 'Present')->count ?? 0);
                $dayRetards = (int)($dayGroup->firstWhere('status', 'Retard')->count ?? 0);

                $dayAbsents = ($dayPresents + $dayRetards > 0 || $currentDay->lt($today))
                    ? max(0, $totalEmployes - ($dayPresents + $dayRetards))
                    : 0;
            } else {
                // Jours futurs de la semaine en cours
                $dayPresents = 0;
                $dayRetards = 0;
                $dayAbsents = 0;
            }

            $weeklyData[] = [
                'day' => $dayNames[$i],
                'day_short' => substr($dayNames[$i], 0, 3),
                'date_str' => $currentDay->format('d/m'),
                'presents' => $dayPresents,
                'retards' => $dayRetards,
                'absents' => $dayAbsents
            ];
        }

        $analysisDate = $today;

        return view('dashboard.index', compact(
            'totalEmployes',
            'presentsCount',
            'retardsCount',
            'absentsCount',
            'donutTotal',
            'recentPointages',
            'weeklyData',
            'analysisDate',
            'trendPercent',
            'isTrendPositive'
        ));
    }
}
