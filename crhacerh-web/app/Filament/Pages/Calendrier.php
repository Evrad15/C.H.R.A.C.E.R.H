<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;

class Calendrier extends Page
{
    // 💡 TOUS LES TYPES SONT ALIGNÉS AU MILLIMÈTRE AVEC FILAMENT
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendrier';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.calendrier';
}