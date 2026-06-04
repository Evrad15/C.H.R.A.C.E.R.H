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
        // Définir les bornes de la semaine en cours (Lundi à Vendredi)
        $debutSemaine = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $finSemaine = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(4)->endOfDay(); // Vendredi soir

        // 1. Calcul des 4 cartes du haut
        $this->totalEmployes = Employe::count(); // Total absolu des employés actifs
        
        $this->absentsSemaine = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])
            ->where('statut', 'Absent')
            ->count();

        $this->retardsSemaine = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])
            ->where('statut', 'Retard')
            ->count();

        $this->aLHeureSemaine = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])
            ->where('statut', 'Present') // Ou 'A l\'heure' selon votre énumération
            ->count();

        // 2. Récupération des Tops 5 (Basé sur la semaine en cours)
        $this->topAbsents = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])
            ->where('statut', 'Absent')
            ->select('employe_id', DB::raw('count(*) as total'))
            ->groupBy('employe_id')
            ->orderByDesc('total')
            ->with('employe') // Assurez-vous d'avoir la relation 'employe' dans votre modèle Presence
            ->limit(5)
            ->get();

        $this->topRetards = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])
            ->where('statut', 'Retard')
            ->select('employe_id', DB::raw('count(*) as total'))
            ->groupBy('employe_id')
            ->orderByDesc('total')
            ->with('employe')
            ->limit(5)
            ->get();

        $this->topAssidus = Presence::whereBetween('created_at', [$debutSemaine, $finSemaine])
            ->where('statut', 'Present')
            ->select('employe_id', DB::raw('count(*) as total'))
            ->groupBy('employe_id')
            ->orderByDesc('total')
            ->with('employe')
            ->limit(5)
            ->get();

        // 3. Préparation des données du graphique (Lundi à Vendredi)
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $absencesParJour = [];
        $retardsParJour = [];
        $assidusParJour = [];

        for ($i = 0; $i < 5; $i++) {
            $jourCible = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays($i);
            
            $absencesParJour[] = Presence::whereDate('created_at', $jourCible)->where('statut', 'Absent')->count();
            $retardsParJour[] = Presence::whereDate('created_at', $jourCible)->where('statut', 'Retard')->count();
            $assidusParJour[] = Presence::whereDate('created_at', $jourCible)->where('statut', 'Present')->count();
        }

        $this->chartData = [
            'labels' => $jours,
            'absences' => $absencesParJour,
            'retards' => $retardsParJour,
            'assidus' => $assidusParJour,
        ];
    }
}