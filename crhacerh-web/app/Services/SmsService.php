<?php

namespace App\Services;

use App\Models\RendezVous;
use App\Models\SmsLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Envoie un SMS de confirmation immédiat lors de la création d'un rendez-vous.
     */
    public static function sendConfirmation(RendezVous $rdv): SmsLog
    {
        $nom     = self::getPatientName($rdv);
        $date    = Carbon::parse($rdv->date_rendez_vous)->format('d/m/Y');
        $heure   = Carbon::parse($rdv->date_rendez_vous)->format('H:i');
        $service = $rdv->service ?? 'Consultation';
        $docName = preg_replace('/^Dr\.?\s*/i', '', $rdv->medecin ?? '');
        $medecin = !empty($docName) ? " (Dr {$docName})" : '';

        $message = "Bonjour {$nom}, votre RDV au C.H.R.A.C.E.R.H est bien confirmé pour le {$date} à {$heure} en {$service}{$medecin}. Merci d'arriver 10 min en avance.";

        return self::dispatch($rdv->telephone_patient, $message, 'CONFIRMATION', $rdv, $nom);
    }

    /**
     * Envoie un SMS de rappel J-1 (par défaut la veille).
     */
    public static function sendReminder(RendezVous $rdv): SmsLog
    {
        return self::sendPeriodicReminder($rdv, 1);
    }

    /**
     * Envoie un SMS de rappel périodique (Cycle de 3 rappels tous les 3 jours : J-6, J-3, J-1).
     */
    public static function sendPeriodicReminder(RendezVous $rdv, int $daysBefore = 1): SmsLog
    {
        $nom     = self::getPatientName($rdv);
        $date    = Carbon::parse($rdv->date_rendez_vous)->format('d/m/Y');
        $heure   = Carbon::parse($rdv->date_rendez_vous)->format('H:i');
        $service = $rdv->service ?? 'Consultation';
        $docName = preg_replace('/^Dr\.?\s*/i', '', $rdv->medecin ?? '');
        $medecin = !empty($docName) ? " (Dr {$docName})" : '';

        if ($daysBefore === 1) {
            $type = 'RAPPEL_J1';
            $message = "Rappel C.H.R.A.C.E.R.H (3/3) : Bonjour {$nom}, votre RDV a lieu DEMAIN le {$date} à {$heure} en {$service}{$medecin}. Merci d'arriver 10 min en avance avec votre pièce d'identité.";
        } elseif ($daysBefore === 3) {
            $type = 'RAPPEL_J3';
            $message = "Rappel C.H.R.A.C.E.R.H (2/3) : Bonjour {$nom}, votre RDV aura lieu dans 3 jours (le {$date} à {$heure}) en {$service}{$medecin}. Merci de préparer votre dossier médical.";
        } elseif ($daysBefore === 6) {
            $type = 'RAPPEL_J6';
            $message = "Rappel C.H.R.A.C.E.R.H (1/3) : Bonjour {$nom}, nous vous rappelons votre RDV dans 6 jours, le {$date} à {$heure} en {$service}{$medecin}. Pour toute modification : +237 222 00 00 00.";
        } else {
            $type = 'RAPPEL_J' . $daysBefore;
            $message = "Rappel C.H.R.A.C.E.R.H : Bonjour {$nom}, nous vous rappelons votre RDV dans {$daysBefore} jours, le {$date} à {$heure} en {$service}{$medecin}. Contact : +237 222 00 00 00.";
        }

        return self::dispatch($rdv->telephone_patient, $message, $type, $rdv, $nom);
    }

    /**
     * Envoie un SMS en cas d'annulation de rendez-vous.
     */
    public static function sendCancellation(RendezVous $rdv): SmsLog
    {
        $nom     = self::getPatientName($rdv);
        $date    = Carbon::parse($rdv->date_rendez_vous)->format('d/m/Y');
        $heure   = Carbon::parse($rdv->date_rendez_vous)->format('H:i');
        $service = $rdv->service ?? 'Consultation';

        $message = "C.H.R.A.C.E.R.H : Bonjour {$nom}, votre rendez-vous du {$date} à {$heure} ({$service}) a été annulé. Contactez-nous au +237 222 00 00 00 pour toute reprogrammation.";

        return self::dispatch($rdv->telephone_patient, $message, 'ANNULATION', $rdv, $nom);
    }

    /**
     * Envoie un SMS en cas de report de rendez-vous.
     */
    public static function sendRescheduled(RendezVous $rdv): SmsLog
    {
        $nom     = self::getPatientName($rdv);
        $date    = Carbon::parse($rdv->date_rendez_vous)->format('d/m/Y');
        $heure   = Carbon::parse($rdv->date_rendez_vous)->format('H:i');
        $service = $rdv->service ?? 'Consultation';
        $docName = preg_replace('/^Dr\.?\s*/i', '', $rdv->medecin ?? '');
        $medecin = !empty($docName) ? " (Dr {$docName})" : '';

        $message = "C.H.R.A.C.E.R.H : Bonjour {$nom}, votre rendez-vous a été reporté au {$date} à {$heure} en {$service}{$medecin}. En cas d'indisponibilité, appelez le +237 222 00 00 00.";

        return self::dispatch($rdv->telephone_patient, $message, 'REPORTE', $rdv, $nom);
    }

    /**
     * Envoie un SMS lorsque la consultation est honorée / terminée (Statut Réalisé).
     */
    public static function sendCompleted(RendezVous $rdv): SmsLog
    {
        $nom     = self::getPatientName($rdv);
        $date    = Carbon::parse($rdv->date_rendez_vous)->format('d/m/Y');
        $service = $rdv->service ?? 'Consultation';

        $message = "C.H.R.A.C.E.R.H : Bonjour {$nom}, votre consultation du {$date} en {$service} a bien été effectuée. Nous vous remercions pour votre confiance et restons à votre disposition.";

        return self::dispatch($rdv->telephone_patient, $message, 'REALISE', $rdv, $nom);
    }

    /**
     * Envoie un SMS lors du pointage d'arrivée à la Borne Kiosque (Statut Présent).
     */
    public static function sendCheckIn(RendezVous $rdv): SmsLog
    {
        $nom     = self::getPatientName($rdv);
        $heure   = now()->format('H:i');
        $service = $rdv->service ?? 'Consultation';

        $message = "C.H.R.A.C.E.R.H : Bonjour {$nom}, votre arrivée a bien été enregistrée à la borne interactive à {$heure} pour le service {$service}. Vous serez reçu(e) sous peu.";

        return self::dispatch($rdv->telephone_patient, $message, 'ARRIVEE', $rdv, $nom);
    }

    /**
     * Réessaie d'envoyer un SMS ayant échoué.
     */
    public static function resend(SmsLog $log): SmsLog
    {
        $tel = self::formatCameroonPhone($log->phone);
        if (!$tel) {
            $log->update([
                'status'           => 'FAILED',
                'response_details' => 'Numéro invalide lors de la réexpédition : ' . $log->phone,
            ]);
            return $log;
        }

        $result = self::sendToProvider($tel, $log->message);

        $log->update([
            'status'           => $result['status'],
            'response_details' => $result['details'],
            'sent_at'          => $result['success'] ? now() : $log->sent_at,
        ]);

        if ($result['success'] && $log->rendez_vous_id) {
            RendezVous::where('id', $log->rendez_vous_id)->update([
                'sms_sent_at' => now(),
            ]);
            RendezVous::where('id', $log->rendez_vous_id)->increment('sms_count');
        }

        return $log;
    }

    /**
     * Moteur central d'envoi et d'enregistrement du log SMS.
     */
    public static function dispatch(?string $phone, string $message, string $type = 'MANUEL', ?RendezVous $rdv = null, ?string $patientName = null): SmsLog
    {
        $nom = $patientName ?? ($rdv ? self::getPatientName($rdv) : 'Patient');

        if (empty($phone)) {
            Log::warning("[SmsService] Aucun numéro de téléphone pour le RDV #" . ($rdv->id ?? 'N/A'));
            return SmsLog::create([
                'rendez_vous_id'   => $rdv?->id,
                'patient_name'     => $nom,
                'phone'            => 'N/A',
                'type'             => $type,
                'message'          => $message,
                'status'           => 'FAILED',
                'response_details' => 'Aucun numéro de téléphone fourni.',
                'sent_at'          => null,
            ]);
        }

        $telFormatted = self::formatCameroonPhone($phone);
        if (!$telFormatted) {
            Log::warning("[SmsService] Numéro invalide : {$phone} pour RDV #" . ($rdv->id ?? 'N/A'));
            return SmsLog::create([
                'rendez_vous_id'   => $rdv?->id,
                'patient_name'     => $nom,
                'phone'            => $phone,
                'type'             => $type,
                'message'          => $message,
                'status'           => 'FAILED',
                'response_details' => "Format de numéro camerounais non reconnu (+237 / 6XXXXXXXX) : {$phone}",
                'sent_at'          => null,
            ]);
        }

        $result = self::sendToProvider($telFormatted, $message);

        $log = SmsLog::create([
            'rendez_vous_id'   => $rdv?->id,
            'patient_name'     => $nom,
            'phone'            => $telFormatted,
            'type'             => $type,
            'message'          => $message,
            'status'           => $result['status'],
            'response_details' => $result['details'],
            'sent_at'          => $result['success'] ? now() : null,
        ]);

        if ($result['success'] && $rdv) {
            $rdv->update(['sms_sent_at' => now()]);
            $rdv->increment('sms_count');
        }

        return $log;
    }

    /**
     * Envoie vers Africa's Talking ou simule en mode local/sandbox.
     */
    private static function sendToProvider(string $formattedPhone, string $message): array
    {
        $apiKey   = config('services.africastalking.api_key', env('AFRICASTALKING_API_KEY'));
        $username = config('services.africastalking.username', env('AFRICASTALKING_USERNAME', 'sandbox'));
        $senderId = config('services.africastalking.sender_id', env('AFRICASTALKING_SENDER_ID', 'CHRACERH'));

        // 1. Si aucune clé API n'est configurée : simulation locale sans crash
        if (empty($apiKey)) {
            Log::info("[SMS SIMULATION LOCALE] To: {$formattedPhone} | Msg: {$message}");
            return [
                'success' => true,
                'status'  => 'SIMULATED',
                'details' => 'Simulation locale réussie (aucune clé API configurée dans .env).',
            ];
        }

        // 2. Déterminer l'URL (Sandbox AT ou Production AT)
        $isSandbox = ($username === 'sandbox');
        $endpoint  = $isSandbox
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';

        try {
            $postData = [
                'username' => $username,
                'to'       => $formattedPhone,
                'message'  => $message,
            ];

            // Le Sender ID n'est envoyé qu'en production (non supporté en Sandbox AT)
            if (!$isSandbox && !empty($senderId)) {
                $postData['from'] = $senderId;
            }

            $response = Http::withoutVerifying()->withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->timeout(10)->post($endpoint, $postData);

            $data = $response->json();
            $recipients = $data['SMSMessageData']['Recipients'] ?? [];
            $recipientStatus = $recipients[0]['status'] ?? null;

            if ($recipientStatus === 'Success') {
                $msgId = $recipients[0]['messageId'] ?? 'N/A';
                $cost  = $recipients[0]['cost'] ?? '0';
                return [
                    'success' => true,
                    'status'  => 'SENT',
                    'details' => "Délivré via Africa's Talking ({$username}) | ID: {$msgId} | Coût: {$cost}",
                ];
            }

            $errorMessage = $data['SMSMessageData']['Message'] ?? ($data ? json_encode($data) : ('HTTP ' . $response->status() . ' - ' . $response->body()));
            return [
                'success' => false,
                'status'  => 'FAILED',
                'details' => "Réponse Africa's Talking : {$errorMessage}",
            ];
        } catch (\Throwable $e) {
            Log::error("[SmsService] Erreur appel Africa's Talking : " . $e->getMessage());
            return [
                'success' => false,
                'status'  => 'FAILED',
                'details' => 'Erreur de connexion : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Récupère le nom du patient (via relation ou champ direct).
     */
    private static function getPatientName(RendezVous $rdv): string
    {
        if (!empty($rdv->nom_patient)) {
            return $rdv->nom_patient;
        }
        if ($rdv->patient && !empty($rdv->patient->nom_complet)) {
            return $rdv->patient->nom_complet;
        }
        return 'Patient';
    }

    /**
     * Normalise un numéro camerounais au format international +237XXXXXXXXX
     */
    public static function formatCameroonPhone(?string $phone): ?string
    {
        if (empty($phone)) return null;

        $clean = preg_replace('/[\s\-\(\)\.]+/', '', $phone);

        if (preg_match('/^\+237[6-9]\d{8}$/', $clean)) {
            return $clean;
        }
        if (preg_match('/^00237([6-9]\d{8})$/', $clean, $m)) {
            return '+237' . $m[1];
        }
        if (preg_match('/^([6-9]\d{8})$/', $clean, $m)) {
            return '+237' . $m[1];
        }
        return null;
    }
}
