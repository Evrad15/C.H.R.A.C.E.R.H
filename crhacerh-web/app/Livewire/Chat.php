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
    public $search = '';

    // Chargement initial
    public function mount()
    {
        $this->autoCreateThreads();
        $this->loadThreads();
    }

    public function loadThreads()
    {
        // Récupère toutes les conversations de l'employé connecté
        $query = Auth::user()->threads()->latest('updated_at');

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where('subject', 'like', $searchTerm);
        }

        $this->threads = $query->get();
    }

    public function updatedSearch()
    {
        $this->loadThreads();
    }

    public function autoCreateThreads()
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return;
        }

        $authId = $currentUser->id;

        if ($currentUser->role === 'RH_Admin') {
            // Pour le RH Admin, on s'assure d'avoir une discussion avec chaque employé (non RH Admin)
            $employees = \App\Models\Employe::where('role', '!=', 'RH_Admin')->get();

            foreach ($employees as $emp) {
                // Vérifie si une discussion existe déjà entre le RH et cet employé
                $exists = \Cmgmyr\Messenger\Models\Thread::whereHas('participants', function ($q) use ($authId) {
                    $q->where('user_id', $authId);
                })->whereHas('participants', function ($q) use ($emp) {
                    $q->where('user_id', $emp->id);
                })->first();

                if (!$exists) {
                    $thread = \Cmgmyr\Messenger\Models\Thread::create([
                        'subject' => $emp->first_name . ' ' . $emp->last_name,
                    ]);

                    // Ajoute les participants
                    $thread->addParticipant([$authId, $emp->id]);

                    // Message initial pour matérialiser le fil de discussion
                    $thread->messages()->create([
                        'user_id' => $authId,
                        'body' => "Fil de discussion initié avec " . $emp->first_name . " " . $emp->last_name . ".",
                    ]);
                }
            }
        } else {
            // Pour un employé simple, on s'assure qu'il a une discussion avec chaque RH Admin
            $rhAdmins = \App\Models\Employe::where('role', 'RH_Admin')->get();

            foreach ($rhAdmins as $rh) {
                $exists = \Cmgmyr\Messenger\Models\Thread::whereHas('participants', function ($q) use ($authId) {
                    $q->where('user_id', $authId);
                })->whereHas('participants', function ($q) use ($rh) {
                    $q->where('user_id', $rh->id);
                })->first();

                if (!$exists) {
                    $thread = \Cmgmyr\Messenger\Models\Thread::create([
                        'subject' => "Support RH - " . $rh->first_name . ' ' . $rh->last_name,
                    ]);

                    // Ajoute les participants
                    $thread->addParticipant([$authId, $rh->id]);

                    // Message initial de la part du RH
                    $thread->messages()->create([
                        'user_id' => $rh->id,
                        'body' => "Bonjour " . $currentUser->first_name . ", comment puis-je vous aider aujourd'hui ?",
                    ]);
                }
            }
        }
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
