<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Http\Request;

class ChatApiController extends Controller
{
    public function getMessages(Request $request, $id)
    {
        $employee = Employe::find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employé introuvable.',
            ], 404);
        }

        $threads = Thread::forUser($employee->id)
            ->with(['participants', 'messages.user'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $result = $threads->map(function (Thread $thread) use ($employee) {
            return [
                'thread_id' => $thread->id,
                'subject' => $thread->subject ?? 'Conversation',
                'updated_at' => optional($thread->updated_at)->toDateTimeString(),
                'participants' => $thread->participants->map(function ($participant) {
                    return [
                        'user_id' => $participant->user_id,
                        'last_read' => optional($participant->last_read)->toDateTimeString(),
                    ];
                })->values(),
                'messages' => $thread->messages->map(function (Message $message) {
                    return [
                        'message_id' => $message->id,
                        'sender_id' => $message->user_id,
                        'body' => $message->body,
                        'sent_at' => optional($message->created_at)->toDateTimeString(),
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->getFullNameAttribute(),
            ],
            'threads' => $result,
        ]);
    }

    public function sendMessage(Request $request, $id)
    {
        $sender = Employe::find($id);

        if (!$sender) {
            return response()->json([
                'success' => false,
                'message' => 'Employé expéditeur introuvable.',
            ], 404);
        }

        $validated = $request->validate([
            'recipient_id' => 'required|integer|exists:employés,id',
            'body' => 'required|string|max:2000',
            'subject' => 'nullable|string|max:255',
        ]);

        if ($sender->id === $validated['recipient_id']) {
            return response()->json([
                'success' => false,
                'message' => 'L’expéditeur et le destinataire ne peuvent pas être identiques.',
            ], 422);
        }

        $recipient = Employe::find($validated['recipient_id']);

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Employé destinataire introuvable.',
            ], 404);
        }

        $thread = Thread::whereHas('participants', function ($query) use ($sender) {
                $query->where('user_id', $sender->id);
            })
            ->whereHas('participants', function ($query) use ($recipient) {
                $query->where('user_id', $recipient->id);
            })
            ->first();

        if (!$thread) {
            $thread = Thread::create([
                'subject' => $validated['subject'] ?? 'Conversation entre employés',
            ]);
            $thread->addParticipant($sender->id);
            $thread->addParticipant($recipient->id);
        }

        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'body' => $validated['body'],
        ]);

        $thread->touch();

        return response()->json([
            'success' => true,
            'thread_id' => $thread->id,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->user_id,
                'body' => $message->body,
                'sent_at' => optional($message->created_at)->toDateTimeString(),
            ],
        ], 201);
    }

    public function downloadAttachment($id, $messageId)
    {
        return response()->json([
            'success' => false,
            'message' => 'Téléchargement d’attachement non pris en charge dans cette version.',
        ], 404);
    }
}
