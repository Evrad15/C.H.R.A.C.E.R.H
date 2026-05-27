<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id', 'recipient_id', 'message', 'is_ai'
    ];

    // L'expéditeur du message
    public function sender()
    {
        return $this->belongsTo(Employee::class, 'sender_id');
    }

    // Le destinataire du message
    public function recipient()
    {
        return $this->belongsTo(Employee::class, 'recipient_id');
    }
}
