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
     * Affiche le Tableau de bord Figma réaligné horizontalement, connecté aux données du 20 juillet au 16 août.
     */
    public function index()
    {
        $today = Carbon::today();

        // 1. Total des employés dans la base
        $totalEmployes = Employe::count() ?: 1;

        // 2. Détermination de la date cible pour le pointage du jour (Aujourd'hui ou dernière date dans l'intervalle)
        $latestPointageTimestamp = Pointage::max('created_at');
        $latestDbDate = $latestPointageTimestamp ? Carbon::parse($latestPointageTimestamp)->startOfDay() : $today;
        
        $hasPointagesToday = Pointage::whereDate('created_at', $today)->exists();
        $targetDate = $hasPointagesToday ? $today : $latestDbDate;

        // 3. Calculs des 3 compteurs d'entrée du jour
        $presentsCount = Pointage::whereDate('created_at', $targetDate)
            ->where('type', 'ENTREE')
            ->where('status', 'Present')
            ->count();

        $retardsCount = Pointage::whereDate('created_at', $targetDate)
            ->where('type', 'ENTREE')
            ->where('status', 'Retard')
            ->count();

        $absentsCount = Pointage::whereDate('created_at', $targetDate)
            ->where('type', 'ENTREE')
            ->where('status', 'Absent')
            ->count();

        if ($absentsCount === 0 && ($presentsCount + $retardsCount) < $totalEmployes) {
            $absentsCount = max(0, $totalEmployes - ($presentsCount + $retardsCount));
        }

        // Le chiffre central du Donut est le total des employés
        $donutTotal = $totalEmployes;

        // 4. Liste des pointages du jour pour la table défilable
        $recentPointages = Pointage::with(['employee', 'employee.department'])
            ->whereDate('created_at', $targetDate)
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();

        if ($recentPointages->isEmpty()) {
            $recentPointages = Pointage::with(['employee', 'employee.department'])
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get();
        }

        // 5. Calcul de la semaine actuelle (du Lundi au Dimanche - 7 jours)
        $startOfWeek = (clone $targetDate)->startOfWeek(Carbon::MONDAY);
        $endOfWeek = (clone $startOfWeek)->endOfWeek(Carbon::SUNDAY);

        $startOfPrevWeek = (clone $startOfWeek)->subWeek();
        $endOfPrevWeek = (clone $startOfWeek)->subDays(1)->endOfDay();

        $currWeekPresents = Pointage::whereBetween('created_at', [$startOfWeek, $endOfWeek])
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

        // 6. Données du graphique hebdomadaire sur 7 jours (Lundi à Dimanche incluant les week-ends)
        $weeklyData = [];
        $dayNames = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        for ($i = 0; $i < 7; $i++) {
            $currentDay = (clone $startOfWeek)->addDays($i);

            $dayPresents = Pointage::whereDate('created_at', $currentDay)
                ->where('type', 'ENTREE')
                ->where('status', 'Present')
                ->count();

            $dayRetards = Pointage::whereDate('created_at', $currentDay)
                ->where('type', 'ENTREE')
                ->where('status', 'Retard')
                ->count();

            $dayAbsents = Pointage::whereDate('created_at', $currentDay)
                ->where('type', 'ENTREE')
                ->where('status', 'Absent')
                ->count();

            if ($dayAbsents === 0 && ($dayPresents + $dayRetards) > 0) {
                $dayAbsents = max(0, $totalEmployes - ($dayPresents + $dayRetards));
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

        $analysisDate = $targetDate;

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
