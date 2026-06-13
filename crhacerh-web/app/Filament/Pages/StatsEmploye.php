<?php

namespace App\Filament\Pages;

use App\Models\Employe;
use Filament\Pages\Page;

class StatsEmploye extends Page
{
    protected static string $view = 'filament.pages.stats-employe';
    protected static bool $shouldRegisterNavigation = false;
    
    // CORRECTION : Supprimez le préfixe de panneau du slug
    protected static ?string $slug = 'stats-employe/{employeId}';

    public $employeId = null;
    public $employe;

    public function mount($employeId = null): void
    {
        // Filament injecte automatiquement le paramètre de la route si nommé pareil
        $this->employeId = $employeId;
        
        if (!$this->employeId) {
            $this->redirect('/ressources-humaines/employes');
            return;
        }

        $this->employe = Employe::with(['absences', 'pointages', 'conges'])->findOrFail($this->employeId);
    }
}