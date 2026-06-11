<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presence extends Model
{
    protected $table = 'pointages';

    // Définissez les champs modifiables en masse
    protected $fillable = [
        'employee_id',
        'type',
        'source',
        'timestamp',
        'status',
        'latitude',
        'longitude',
        'confidence_score',
        'anomaly_detected',
    ];

    /**
     * Obtenir l'employé associé à cette présence.
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employee_id');
    }
}
