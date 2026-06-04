<?php

namespace App\Livewire;

use Livewire\Component;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Illuminate\Support\Facades\Auth;

class Chat extends Component
{
    public $threads;
    public $activeThreadId;
    public $newMessage = '';

    // Chargement initial
    public function mount()
    {
        $this->loadThreads();
    }

    public function loadThreads()
    {
        // Récupère toutes les conversations de l'employé connecté
        $this->threads = Auth::user()->threads()->latest('updated_at')->get();
    }

    public function selectThread($id)
    {
        $this->activeThreadId = $id;
        // Marquer comme lu
        $thread = Thread::find($id);
        $thread->markAsRead(Auth::id());
    }

    public function sendMessage()
    {
        if (empty($this->newMessage) || !$this->activeThreadId) return;

        Message::create([
            'thread_id' => $this->activeThreadId,
            'user_id'   => Auth::id(),
            'body'      => $this->newMessage,
        ]);

        $this->newMessage = '';
        $this->loadThreads(); // Rafraîchir la liste
    }

    public function render()
    {
        $activeThread = $this->activeThreadId ? Thread::find($this->activeThreadId) : null;
        $messages = $activeThread ? $activeThread->messages()->oldest()->get() : [];

        return view('livewire.chat', [
            'messages' => $messages,
            'activeThread' => $activeThread
        ]);
    }

    public function createTestThread()
    {
        // Crée une discussion (subject)
        $thread = \Cmgmyr\Messenger\Models\Thread::create([
            'subject' => 'Discussion de test',
        ]);

        // Ajoute l'utilisateur connecté comme participant
        $thread->addParticipant([auth()->id()]);

        // Envoie un premier message
        \Cmgmyr\Messenger\Models\Message::create([
            'thread_id' => $thread->id,
            'user_id'   => auth()->id(),
            'body'      => 'Ceci est un message de test !',
        ]);

        // Recharge la liste
        $this->loadThreads();
    }
}
