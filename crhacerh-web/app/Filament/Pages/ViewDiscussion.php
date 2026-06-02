<?php

namespace App\Filament\Pages;

use App\Models\Discussion;
use App\Models\Message;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ViewDiscussion extends Page
{
    protected static string $view = 'filament.pages.view-discussion';
    protected static bool $shouldRegisterNavigation = false;

    public ?Discussion $discussion = null;
    public $newMessage = '';

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    // 👇 CHANGE mount() COMME ÇA
    public function mount(): void
    {
        // Récupère l'ID depuis la query string (?record=1)
        $recordId = request()->query('record');
        
        if (!$recordId) {
            $this->redirect('/ressources-humaines/chat');
            return;
        }

        // On cherche la discussion
        $this->discussion = Discussion::find($recordId);

        // Si on ne trouve pas, on redirige
        if (!$this->discussion) {
            $this->redirect('/ressources-humaines/chat');
        }
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->newMessage))) return;

        Message::create([
            'discussion_id' => $this->discussion->id,
            'sender_id' => auth()->id(),
            'message' => $this->newMessage,
        ]);

        $this->newMessage = '';

        // Rafraîchit les messages
        $this->dispatch('refresh-messages');
    }
}