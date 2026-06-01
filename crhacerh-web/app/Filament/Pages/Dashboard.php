<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;

class Dashboard extends BaseDashboard
{
    // 💡 Signature exacte demandée par ton erreur : string|BackedEnum|null
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Tableau de bord';
    
    protected static ?int $navigationSort = 0; 

    protected static ?string $title = 'Tableau de bord';

    /**
     * Retourne un tableau vide pour supprimer tout le contenu par défaut
     */
    public function getWidgets(): array
    {
        return [
            // Tes futurs widgets viendront ici
        ];
    }
}