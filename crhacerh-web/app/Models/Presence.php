<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presence extends Model
{
    // Définissez les champs modifiables en masse
    protected $fillable = [
        'employe_id',
        'statut', // 'Absent', 'Retard', 'Present'
        'heure_arrivee',
    ];

    /**
     * Obtenir l'employé associé à cette présence.
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }
}