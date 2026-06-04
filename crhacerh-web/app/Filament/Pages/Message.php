<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Message extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string $view = 'filament.pages.message';

    protected static ?string $navigationLabel = 'Messagerie';

    protected static ?string $title = 'Messages';

    protected static ?int $navigationSort = 4;

    // Permet d'afficher la page en pleine largeur pour un meilleur rendu de chat
    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}