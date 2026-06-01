<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // ✅ TYPE CORRIGÉ EN ?string
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Tableau de bord';
    
    protected static ?int $navigationSort = 0; 

    protected static ?string $title = 'Tableau de bord';

    public function getWidgets(): array
    {
        return [];
    }
}