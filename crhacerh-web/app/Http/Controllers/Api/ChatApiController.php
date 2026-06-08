<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatApiController extends Controller
{
    /**
     * Récupère la liste des messages d'un employé.
     * Si aucune conversation n'existe, en crée une avec l'administration.
     */
    public function getMessages($employeeId)
    {
        $employee = Employe::find($employeeId);
        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employé introuvable.'
            ], 404);
        }

        // Trouve la première discussion de l'employé
        $thread = $employee->threads()->first();

        if (!$thread) {
            // Création d'une nouvelle discussion dédiée
            $thread = Thread::create([
                'subject' => 'Discussion avec ' . $employee->first_name . ' ' . $employee->last_name,
            ]);

            // Ajout de l'employé comme participant
            $thread->addParticipant($employee->id);

            // Ajout des administrateurs et des managers
            $admins = Employe::whereIn('role', ['RH_Admin', 'Manager'])->pluck('id')->toArray();
            if (!empty($admins)) {
                $thread->addParticipant($admins);
            }
        }

        // Marquer le fil comme lu pour l'employé
        $thread->markAsRead($employee->id);

        $messages = $thread->messages()
            ->oldest()
            ->get()
            ->map(function ($message) use ($employeeId) {
                $attachmentUrl = null;
                if ($message->attachment) {
                    $attachmentUrl = Storage::url($message->attachment);
                    // Prefixe l'URL du tunnel si le chemin est relatif
                    if (!str_starts_with($attachmentUrl, 'http')) {
                        $attachmentUrl = config('app.url') . $attachmentUrl;
                    }
                }

                return [
                    'id'             => $message->id,
                    'thread_id'      => $message->thread_id,
                    'user_id'        => $message->user_id,
                    'body'           => $message->body,
                    'attachment'     => $message->attachment,
                    'attachment_url' => $attachmentUrl,
                    'is_sent_by_me'  => (int)$message->user_id === (int)$employeeId,
                    'created_at'     => $message->created_at->toIso8601String(),
                    'time'           => $message->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'status'    => 'success',
            'thread_id' => $thread->id,
            'messages'  => $messages,
        ], 200);
    }

    /**
     * Envoie un message (texte et/ou pièce jointe) pour un employé.
     * Accepte multipart/form-data pour gérer les fichiers, ou application/json.
     */
    public function sendMessage(Request $request, $employeeId)
    {
        $request->validate([
            'body'       => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:20480', // 20 MB max
        ]);

        // Au moins un body ou une pièce jointe doit être présent
        if (!$request->filled('body') && !$request->hasFile('attachment')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Le message ou une pièce jointe est requis.',
            ], 422);
        }

        $employee = Employe::find($employeeId);
        if (!$employee) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Employé introuvable.'
            ], 404);
        }

        $thread = $employee->threads()->first();

        if (!$thread) {
            // Création du fil si inexistant
            $thread = Thread::create([
                'subject' => 'Discussion avec ' . $employee->first_name . ' ' . $employee->last_name,
            ]);
            $thread->addParticipant($employee->id);
            $admins = Employe::whereIn('role', ['RH_Admin', 'Manager'])->pluck('id')->toArray();
            if (!empty($admins)) {
                $thread->addParticipant($admins);
            }
        }

        // Corps du message : soit le texte, soit le nom du fichier
        $body = trim($request->input('body', ''));
        if (empty($body) && $request->hasFile('attachment')) {
            $body = '📎 ' . $request->file('attachment')->getClientOriginalName();
        }

        // Enregistrement du message
        $msg = $thread->messages()->create([
            'thread_id' => $thread->id,
            'user_id'   => $employee->id,
            'body'      => $body,
        ]);

        // Gestion de la pièce jointe
        $attachmentPath = null;
        $attachmentUrl  = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat-attachments', 'public');
            $msg->attachment = $attachmentPath;
            $msg->save();

            $attachmentUrl = Storage::url($attachmentPath);
            if (!str_starts_with($attachmentUrl, 'http')) {
                $attachmentUrl = config('app.url') . $attachmentUrl;
            }
        }

        // Actualisation du fil de discussion
        $thread->touch();
        $thread->activateAllParticipants();
        $thread->markAsRead($employee->id);

        return response()->json([
            'status'  => 'success',
            'message' => [
                'id'             => $msg->id,
                'thread_id'      => $msg->thread_id,
                'user_id'        => $msg->user_id,
                'body'           => $msg->body,
                'attachment'     => $attachmentPath,
                'attachment_url' => $attachmentUrl,
                'is_sent_by_me'  => true,
                'created_at'     => $msg->created_at->toIso8601String(),
                'time'           => $msg->created_at->format('H:i'),
            ],
        ], 201);
    }

    /**
     * Retourne les informations d'un fichier joint pour téléchargement.
     */
    public function downloadAttachment(Request $request, $employeeId, $messageId)
    {
        $employee = Employe::find($employeeId);
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employé introuvable.'], 404);
        }

        $message = Message::find($messageId);
        if (!$message || !$message->attachment) {
            return response()->json(['status' => 'error', 'message' => 'Fichier introuvable.'], 404);
        }

        // Vérifier que l'employé est participant du fil
        $isParticipant = $employee->threads()->where('threads.id', $message->thread_id)->exists();
        if (!$isParticipant) {
            return response()->json(['status' => 'error', 'message' => 'Accès refusé.'], 403);
        }

        if (!Storage::disk('public')->exists($message->attachment)) {
            return response()->json(['status' => 'error', 'message' => 'Fichier introuvable sur le serveur.'], 404);
        }

        return Storage::disk('public')->download($message->attachment);
    }
}
