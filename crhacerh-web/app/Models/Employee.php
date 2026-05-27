<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Champs modifiables en masse (sécurité Laravel)
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'department_id',
        'phone',
        'face_embeddings',
        'consent_facial_at'
    ];

    // Un employé appartient à un département
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Un employé a plusieurs pointages
    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

    // Un employé a plusieurs absences
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    // Un employé peut avoir plusieurs messages
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    // Un employé peut avoir plusieurs prédictions
    public function predictions()
    {
        return $this->hasMany(MlPrediction::class);
    }

    // Les messages envoyés par l'employé
    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }
}
