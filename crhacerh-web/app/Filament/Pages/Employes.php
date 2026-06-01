<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Employes extends Page
{
    // ✅ TYPE CORRIGÉ EN ?string (Pas de string|BackedEnum|null)
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Employés';

    protected static ?int $navigationSort = 2;

    // ✅ DOIT ÊTRE STATIC EN V3
    protected static string $view = 'filament.pages.employes';
}