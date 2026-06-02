<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discussion extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
    ];

    // 👇 AJOUTE CETTE RELATION
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'discussion_id');
    }

    // Relations existantes (sender/receiver)
    public function sender()
    {
        return $this->belongsTo(Employe::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Employe::class, 'receiver_id');
    }
}