<?php

namespace App\Filament\Pages;

use App\Models\Discussion;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Htmlable;

class Chat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string $view = 'filament.pages.chat';
    protected static ?string $title = 'Messagerie';
    protected static ?int $navigationSort = 4;
   
    public $search = '';

    public function getTitle(): string|Htmlable
    {
        return ''; // Retourne une chaîne vide au lieu du nom de la classe
    }

   public function getDiscussionsProperty()
    {
        $userId = Auth::id();

        return Discussion::where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
        })
        ->where(function ($query) {
            $query->whereHas('sender', function ($q) {
                // REMPLACEZ 'nom' PAR LE NOM EXACT DE VOTRE COLONNE
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%");
            })
            ->orWhereHas('receiver', function ($q) {
                // REMPLACEZ 'nom' PAR LE NOM EXACT DE VOTRE COLONNE
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%");
            });
        })
        ->get();
    }
}
