<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    use HasFactory;

    protected $table = 'rendez_vous';

    protected $fillable = [
        'patient_id',
        'nom_patient',
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
        'sms_sent_at',
        'sms_count',
    ];

    protected $casts = [
        'date_rendez_vous' => 'datetime',
        'fin_rendez_vous' => 'datetime',
        'consent_facial_at' => 'datetime',
        'fingerprint_registered_at' => 'datetime',
        'pointage_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'sms_count' => 'integer',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class, 'rendez_vous_id');
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
