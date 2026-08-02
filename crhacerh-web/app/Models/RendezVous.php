<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RendezVous extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rendez_vous';

    protected $fillable = [
        'patient_id',
        'telephone_patient',
        'email_patient',
        'service',
        'motif',
        'date_rendez_vous',
        'fin_rendez_vous',
        'medecin',
        'statut',
        'notes',
        'face_embeddings',
        'consent_facial_at',
        'fingerprint_data',
        'fingerprint_registered_at',
        'pointage_at',
    ];

    protected $casts = [
        'date_rendez_vous' => 'datetime',
        'fin_rendez_vous' => 'datetime',
        'consent_facial_at' => 'datetime',
        'fingerprint_registered_at' => 'datetime',
        'pointage_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function getServicesList(): array
    {
        return [
            'Chirurgie Endoscopique' => 'Chirurgie Endoscopique',
            'FIV / Reproduction Humaine' => 'FIV / Reproduction Humaine',
            'Consultation Gynécologique' => 'Consultation Gynécologique',
            'Échographie & Imagerie' => 'Échographie & Imagerie',
            'Suivi de Grossesse' => 'Suivi de Grossesse',
            'Consultation d’Infertilité' => 'Consultation d’Infertilité',
            'Autre' => 'Autre',
        ];
    }
}
