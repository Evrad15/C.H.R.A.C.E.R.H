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

    /**
     * Contrôle d'accès biométrique strict pour patient (Statut + Fenêtre horaire).
     */
    public function isBiometricAccessAllowed(): array
    {
        // 1. Vérification du Statut
        if ($this->statut === 'Réalisé') {
            return [
                'allowed' => false,
                'status_code' => 403,
                'code' => 'COMPLETED',
                'message' => "Accès refusé : Le rendez-vous de {$this->nom_patient} est déjà 'Réalisé'. L'accès biométrique au centre est désactivé (profil conservé sans suppression)."
            ];
        }

        if (in_array($this->statut, ['Annulé', 'Reporté', 'Non honoré'])) {
            return [
                'allowed' => false,
                'status_code' => 403,
                'code' => 'INACTIVE_STATUS',
                'message' => "Accès refusé : Le rendez-vous de {$this->nom_patient} est '{$this->statut}'. L'accès biométrique est verrouillé."
            ];
        }

        if ($this->statut === 'Présent' || !empty($this->pointage_at)) {
            return [
                'allowed' => false,
                'status_code' => 400,
                'code' => 'ALREADY_CHECKED_IN',
                'message' => "Information : {$this->nom_patient} a déjà validé son arrivée aujourd'hui."
            ];
        }

        // 2. Vérification de la Date
        $now = \Carbon\Carbon::now();
        $rdvDate = \Carbon\Carbon::parse($this->date_rendez_vous);

        if ($rdvDate->toDateString() !== $now->toDateString()) {
            return [
                'allowed' => false,
                'status_code' => 403,
                'code' => 'WRONG_DATE',
                'message' => "Accès refusé : Votre rendez-vous est prévu pour le {$rdvDate->format('d/m/Y')} (non valide aujourd'hui)."
            ];
        }

        // 3. Fenêtre Horaire de Tolérance (Ouverture : H - 1h30 | Fermeture : H + 1h00)
        $openTime = (clone $rdvDate)->subMinutes(90);
        $closeTime = (clone $rdvDate)->addMinutes(60);

        if ($now->lt($openTime)) {
            return [
                'allowed' => false,
                'status_code' => 403,
                'code' => 'TOO_EARLY',
                'message' => "Accès anticipé non autorisé : Votre rendez-vous est prévu à {$rdvDate->format('H:i')}. L'accès au centre ouvre à partir de {$openTime->format('H:i')} (1h30 avant l'heure fixée)."
            ];
        }

        if ($now->gt($closeTime)) {
            return [
                'allowed' => false,
                'status_code' => 403,
                'code' => 'EXPIRED',
                'message' => "Accès expiré : Votre rendez-vous était prévu à {$rdvDate->format('H:i')} (délai de tolérance de 60 min dépassé). Veuillez vous présenter à la réception."
            ];
        }

        return [
            'allowed' => true,
            'status_code' => 200,
            'code' => 'OK',
            'message' => 'Accès autorisé'
        ];
    }
}
