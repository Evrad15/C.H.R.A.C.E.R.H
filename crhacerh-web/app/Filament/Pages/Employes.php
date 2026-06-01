<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;

class Employes extends Page
{
    // L'icône de groupe d'utilisateurs
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    // Le nom dans le menu
    protected static ?string $navigationLabel = 'Employés';

    // Positionné à 2 (juste sous le Dashboard, et avant le Calendrier qui est à 5)
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.employes';
}