<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Employe;
use App\Models\Presence; // Adaptez le nom si votre modèle s'appelle différemment
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Tableau de bord';
    protected static string $view = 'filament.pages.dashboard';

    // Propriétés pour les 4 cartes du haut
    public int $totalEmployes = 0;
    public int $absentsSemaine = 0;
    public int $retardsSemaine = 0;
    public int $aLHeureSemaine = 0;

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

        // Déterminer le jour précédent (si on est lundi, le jour précédent est le vendredi)
        $hier = Carbon::yesterday();
        if (Carbon::now()->isMonday()) {
            $hier = Carbon::now()->subDays(3);
        }

        // 1. Calculs de base
        $this->totalEmployes = Employe::count();

        // Requêtes de la semaine en cours
        $this->absentsSemaine = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])->where('status', 'Absent')->count();
        $this->retardsSemaine = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])->where('status', 'Retard')->count();
        $this->aLHeureSemaine = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])->where('status', 'Present')->count();

        // 2. Calcul des tendances (Différence avec hier)
        $absentsHier = Presence::whereDate('created_at', $hier)->where('status', 'Absent')->count();
        $retardsHier = Presence::whereDate('created_at', $hier)->where('status', 'Retard')->count();
        $aLHeureHier = Presence::whereDate('created_at', $hier)->where('status', 'Present')->count();

        // Absents aujourd'hui (ou dernier jour) pour comparer
        $absentsAujourdhui = Presence::whereDate('created_at', Carbon::today())->where('status', 'Absent')->count();
        $retardsAujourdhui = Presence::whereDate('created_at', Carbon::today())->where('status', 'Retard')->count();
        $aLHeureAujourdhui = Presence::whereDate('created_at', Carbon::today())->where('status', 'Present')->count();

        $this->tendanceAbsents = $absentsAujourdhui - $absentsHier;
        $this->tendanceRetards = $retardsAujourdhui - $retardsHier;
        $this->tendanceALHeure = $aLHeureAujourdhui - $aLHeureHier;

        // 3. Calcul du taux de présence global sur la semaine
        $totalPresencesPossibles = $this->totalEmployes * 5; // Nombre d'employés * 5 jours
        if ($totalPresencesPossibles > 0) {
            $this->tauxPresenceGlobal = round((($this->aLHeureSemaine + $this->retardsSemaine) / $totalPresencesPossibles) * 100, 1);
        }

        // 4. Section "Alerte Anomalies" en direct (Ex: Retards importants ou absences non justifiées du jour)
        $this->anomalies = Presence::whereDate('created_at', Carbon::today())
            ->whereIn('statut', ['Absent', 'Retard'])
            ->with('employe')
            ->latest()
            ->limit(4)
            ->get();

        // Chargement des Tops 5 et données graphiques (identique à votre ancienne logique)
        $this->chargerTopsEtGraphique($debutSemaine, $finSemaine);
    }

    protected function chargerTopsEtGraphique($debutSemaine, $finSemaine)
    {
        $this->topAbsents = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])->where('status', 'Absent')->select('employee_id as employe_id', DB::raw('count(*) as total'))->groupBy('employee_id')->orderByDesc('total')->with('employe')->limit(5)->get();
        $this->topRetards = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])->where('status', 'Retard')->select('employee_id as employe_id', DB::raw('count(*) as total'))->groupBy('employee_id')->orderByDesc('total')->with('employe')->limit(5)->get();
        $this->topAssidus = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])->where('status', 'Present')->select('employee_id as employe_id', DB::raw('count(*) as total'))->groupBy('employee_id')->orderByDesc('total')->with('employe')->limit(5)->get();

        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $absencesParJour = [];
        $retardsParJour = [];
        $assidusParJour = [];
        for ($i = 0; $i < 5; $i++) {
            $jourCible = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays($i);
            $absencesParJour[] = Presence::whereDate('created_at', $jourCible)->where('status', 'Absent')->count();
            $retardsParJour[] = Presence::whereDate('created_at', $jourCible)->where('status', 'Retard')->count();
            $assidusParJour[] = Presence::whereDate('created_at', $jourCible)->where('status', 'Present')->count();
        }
        $this->chartData = ['labels' => $jours, 'absences' => $absencesParJour, 'retards' => $retardsParJour, 'assidus' => $assidusParJour];
    }
}
