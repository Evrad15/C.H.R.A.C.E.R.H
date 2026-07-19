<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Employe;
use App\Models\Pointage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.dashboard';

    // Propriétés pour les 4 cartes du haut
    public int $totalEmployes = 0;
    public int $absentsSemaine = 0;
    public int $retardsSemaine = 0;
    public int $aLHeureSemaine = 0;
    public int $tendanceAbsents = 0;
    public int $tendanceRetards = 0;
    public int $tendanceALHeure = 0;
    public float $tauxPresenceGlobal = 0.0;

    // Propriétés pour les tops 5
    public $topAbsents = [];
    public $topRetards = [];
    public $topAssidus = [];

    // Données pour le graphique linéaire
    public array $chartData = [];

    public function mount(): void
    {
        $debutSemaine = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $finSemaine = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(4)->endOfDay();

        // 1. Calculs de base
        $this->totalEmployes = Employe::count();

        // Déterminer la date cible (dernier jour ayant des pointages d'entrée, ou aujourd'hui par défaut)
        $latestPointage = Pointage::where('type', 'ENTREE')->latest('created_at')->first();
        $targetDate = $latestPointage ? Carbon::parse($latestPointage->created_at)->startOfDay() : Carbon::today();

        // Requêtes du jour ciblé (les 3 compteurs d'entrée)
        $this->absentsSemaine = Pointage::whereDate('created_at', $targetDate)->where('type', 'ENTREE')->where('status', 'Absent')->count();
        $this->retardsSemaine = Pointage::whereDate('created_at', $targetDate)->where('type', 'ENTREE')->where('status', 'Retard')->count();
        $this->aLHeureSemaine = Pointage::whereDate('created_at', $targetDate)->where('type', 'ENTREE')->where('status', 'Present')->count();

        // 2. Calcul des tendances (Différence avec le jour ouvré précédent contenant des pointages d'entrée)
        $previousPointage = Pointage::where('type', 'ENTREE')
            ->whereDate('created_at', '<', $targetDate)
            ->latest('created_at')
            ->first();
        $prevDate = $previousPointage ? Carbon::parse($previousPointage->created_at)->startOfDay() : null;

        if ($prevDate) {
            $absentsHier = Pointage::whereDate('created_at', $prevDate)->where('type', 'ENTREE')->where('status', 'Absent')->count();
            $retardsHier = Pointage::whereDate('created_at', $prevDate)->where('type', 'ENTREE')->where('status', 'Retard')->count();
            $aLHeureHier = Pointage::whereDate('created_at', $prevDate)->where('type', 'ENTREE')->where('status', 'Present')->count();
        } else {
            $absentsHier = 0;
            $retardsHier = 0;
            $aLHeureHier = 0;
        }

        $this->tendanceAbsents = $this->absentsSemaine - $absentsHier;
        $this->tendanceRetards = $this->retardsSemaine - $retardsHier;
        $this->tendanceALHeure = $this->aLHeureSemaine - $aLHeureHier;

        // 3. Calcul du taux de présence global sur la journée ciblée
        if ($this->totalEmployes > 0) {
            $this->tauxPresenceGlobal = round((($this->aLHeureSemaine + $this->retardsSemaine) / $this->totalEmployes) * 100, 1);
        }

        // Chargement des Tops 5 et données graphiques
        $this->chargerTopsEtGraphique($debutSemaine, $finSemaine);
    }

    protected function chargerTopsEtGraphique($debutSemaine, $finSemaine)
    {
        $this->topAbsents = Pointage::where('type', 'ENTREE')->where('status', 'Absent')->select('employee_id', DB::raw('count(*) as total'))->groupBy('employee_id')->orderByDesc('total')->with('employe')->limit(5)->get();
        $this->topRetards = Pointage::where('type', 'ENTREE')->where('status', 'Retard')->select('employee_id', DB::raw('count(*) as total'))->groupBy('employee_id')->orderByDesc('total')->with('employe')->limit(5)->get();
        $this->topAssidus = Pointage::where('type', 'ENTREE')->where('status', 'Present')->select('employee_id', DB::raw('count(*) as total'))->groupBy('employee_id')->orderByDesc('total')->with('employe')->limit(5)->get();

        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $absencesParJour = [];
        $retardsParJour = [];
        $assidusParJour = [];
        for ($i = 0; $i < 5; $i++) {
            $jourCible = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays($i);
            $absencesParJour[] = Pointage::whereDate('created_at', $jourCible)->where('type', 'ENTREE')->where('status', 'Absent')->count();
            $retardsParJour[] = Pointage::whereDate('created_at', $jourCible)->where('type', 'ENTREE')->where('status', 'Retard')->count();
            $assidusParJour[] = Pointage::whereDate('created_at', $jourCible)->where('type', 'ENTREE')->where('status', 'Present')->count();
        }
        $this->chartData = ['labels' => $jours, 'absences' => $absencesParJour, 'retards' => $retardsParJour, 'assidus' => $assidusParJour];
    }
}
