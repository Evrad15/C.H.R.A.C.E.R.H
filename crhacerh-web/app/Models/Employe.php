<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Traits\Messagable;

class Employe extends Authenticatable
{
    use HasFactory, Notifiable, Messagable;

    // On force la table sur 'employés' avec son accent
    protected $table = 'employés';

    // Champs modifiables en masse (sécurité Laravel)
    protected $fillable = [
        'matricule',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'department_id',
        'phone',
        'face_embeddings',
        'consent_facial_at',
        'fingerprint_data',
        'fingerprint_registered_at',
        'remember_token',
    ];

    // Sécurisation et mutation automatique des types de données
    protected $casts = [
        'consent_facial_at' => 'datetime',
        'fingerprint_registered_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- RELATIONS ---

    // Un employé appartient à un département
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Un employé peut avoir plusieurs prédictions
    public function predictions()
    {
        return $this->hasMany(MlPrediction::class, 'employee_id');
    }

    // Un employé a plusieurs pointages
    public function pointages()
    {
        return $this->hasMany(Pointage::class, 'employee_id');
    }

    /**
     * Relation absences (pointages avec statut Absent)
     */
    public function absences(): HasMany
    {
        return $this->hasMany(Pointage::class, 'employee_id')
                    ->where('status', 'Absent');
    }

    // Un employé a plusieurs congés/permissions
    public function conges(): HasMany
    {
        return $this->hasMany(Conge::class, 'employe_id');
    }

    // Les messages envoyés par l'employé
    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    /**
     * Récupère le nom complet de l'employé
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Récupère le nom de la colonne pour le "Remember Me"
     */
    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    /**
     * Récupère la valeur du cookie de session
     */
    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    /**
     * Définit la nouvelle valeur du cookie de session
     */
    public function setRememberToken($value): void
    {
        $this->remember_token = $value;
    }
}