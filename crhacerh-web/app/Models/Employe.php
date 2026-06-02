<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// 💡 On remplace le Model de base par la classe Authenticatable
use Illuminate\Foundation\Auth\User as Authenticatable;
// 💡 On importe le contrat requis pour que Filament accepte ce modèle
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;

class Employe extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, SoftDeletes; // Ajout de SoftDeletes car ton schéma SQL a un deleted_at

    // On force la table sur 'employés' avec son accent
    protected $table = 'employés';

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
        'consent_facial_at',
        'remember_token',
    ];

    // Sécurisation et mutation automatique des types de données
    protected $casts = [
        'consent_facial_at' => 'datetime',
        'password' => 'hashed', // Hashe automatiquement le mot de passe à l'inscription/création
    ];

    /**
     * 🔐 RÈGLE D'ACCÈS AU PANEL FILAMENT
     * Détermine qui a le droit de se connecter à l'interface d'administration.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Seuls les utilisateurs ayant le rôle 'RH_Admin' ou 'Manager' peuvent se connecter
        return in_array($this->role, ['RH_Admin', 'Manager', 'Employés']);
    }

    // --- RELATIONS ---

    // Un employé appartient à un département
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Un employé a plusieurs pointages
    public function pointages()
    {
        // On spécifie 'employee_id' car c'est le nom de la clé étrangère dans ta table pointages
        return $this->hasMany(Pointage::class, 'employee_id');
    }

    // Un employé a plusieurs absences
    public function absences()
    {
        return $this->hasMany(Absence::class, 'employee_id');
    }

    // Un employé peut avoir plusieurs messages
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'employee_id');
    }

    // Un employé peut avoir plusieurs prédictions
    public function predictions()
    {
        return $this->hasMany(MlPrediction::class, 'employee_id');
    }

    // Les messages envoyés par l'employé
    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function getFilamentName(): string
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

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
