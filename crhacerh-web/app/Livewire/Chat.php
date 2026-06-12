<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Illuminate\Support\Facades\Auth;

class Chat extends Component
{
    use WithFileUploads;

    public $attachment = null;

    protected $rules = [
        'newMessage' => 'nullable|string|max:2000',
        'attachment' => 'nullable|file|max:10240',
    ];

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
        if ($thread) {
            $thread->markAsRead(Auth::id());
        }
    }

    public function sendMessage()
    {
        $this->validate();

        if (!$this->activeThreadId) {
            return;
        }

        $thread = Thread::find($this->activeThreadId);
        if (! $thread) {
            return;
        }

        // Create message via the thread to ensure relationships are correct
        $msg = $thread->messages()->create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'body' => trim($this->newMessage) ?: ($this->attachment ? 'Fichier joint' : ''),
        ]);

        // Handle attachment: can't mass-assign in vendor model, set and save
        if ($this->attachment) {
            $path = $this->attachment->store('chat-attachments', 'public');
            $msg->attachment = $path;
            $msg->save();
        }

        // Update thread metadata and participant states
        $thread->touch();
        $thread->activateAllParticipants();
        $thread->markAsRead(Auth::id());

        $this->newMessage = '';
        $this->attachment = null;
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
