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
     * Envoie vers la passerelle configurée (Orange SMS Cameroon, Camoo SMS, ou Africa's Talking).
     */
    private static function sendToProvider(string $formattedPhone, string $message): array
    {
        $provider = config('services.sms_provider', env('SMS_PROVIDER', 'orange'));

        if ($provider === 'orange' || (!empty(env('ORANGE_AUTH_HEADER')) && $provider !== 'camoo' && $provider !== 'africastalking')) {
            return self::sendViaOrange($formattedPhone, $message);
        }

        if ($provider === 'camoo') {
            return self::sendViaCamoo($formattedPhone, $message);
        }

        return self::sendViaAfricasTalking($formattedPhone, $message);
    }

    /**
     * Passerelle Orange Developer SMS (Cameroun).
     */
    private static function sendViaOrange(string $formattedPhone, string $message): array
    {
        $authHeader    = config('services.orange.auth_header', env('ORANGE_AUTH_HEADER'));
        $senderAddress = config('services.orange.sender_address', env('ORANGE_SENDER_ADDRESS', 'tel:+2370000'));
        $senderName    = config('services.orange.sender_name', env('ORANGE_SENDER_NAME', 'CHRACERH'));

        if (empty($authHeader)) {
            Log::info("[ORANGE SIMULATION LOCALE] To: {$formattedPhone} | Msg: {$message}");
            return [
                'success' => true,
                'status'  => 'SIMULATED',
                'details' => 'Simulation locale réussie (clés Orange non configurées).',
            ];
        }

        try {
            // 1. Obtenir le Token OAuth
            $tokenRes = Http::withoutVerifying()->withHeaders([
                'Authorization' => $authHeader,
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Accept'        => 'application/json',
            ])->asForm()->timeout(15)->post('https://api.orange.com/oauth/v3/token', [
                'grant_type' => 'client_credentials'
            ]);

            $tokenData = $tokenRes->json();
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                return [
                    'success' => false,
                    'status'  => 'FAILED',
                    'details' => 'Erreur obtention token Orange : ' . ($tokenData['error_description'] ?? 'Auth failed'),
                ];
            }

            // 2. Envoyer le SMS
            $cleanPhone = preg_replace('/[^0-9]/', '', $formattedPhone);
            if (!str_starts_with($cleanPhone, '237')) {
                $cleanPhone = '237' . $cleanPhone;
            }
            $recipientAddress = 'tel:+' . $cleanPhone;

            $endpoint = 'https://api.orange.com/smsmessaging/v1/outbound/' . urlencode($senderAddress) . '/requests';

            $sendRes = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(15)->post($endpoint, [
                'outboundSMSMessageRequest' => [
                    'address'       => $recipientAddress,
                    'senderAddress' => $senderAddress,
                    'senderName'    => $senderName ?: 'CHRACERH',
                    'outboundSMSTextMessage' => [
                        'message' => $message
                    ]
                ]
            ]);

            $status = $sendRes->status();
            $data = $sendRes->json();

            if ($status === 201 || $status === 200) {
                return [
                    'success' => true,
                    'status'  => 'SENT',
                    'details' => "Délivré via Orange SMS Cameroon | Dest: {$recipientAddress} | Émetteur: {$senderName}",
                ];
            }

            $errorMsg = $data['requestError']['policyException']['variables'][0] ??
                        ($data['requestError']['serviceException']['variables'][0] ??
                        ($data['message'] ?? ($sendRes->body() ?: "Erreur Orange (HTTP {$status})")));

            return [
                'success' => false,
                'status'  => 'FAILED',
                'details' => "Réponse Orange SMS : {$errorMsg}",
            ];
        } catch (\Throwable $e) {
            Log::error("[SmsService] Erreur appel Orange SMS : " . $e->getMessage());
            return [
                'success' => false,
                'status'  => 'FAILED',
                'details' => 'Erreur de connexion Orange : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Passerelle Nationale Camoo SMS (Cameroun).
     */
    private static function sendViaCamoo(string $formattedPhone, string $message): array
    {
        $apiKey    = config('services.camoo.api_key', env('CAMOO_API_KEY'));
        $apiSecret = config('services.camoo.api_secret', env('CAMOO_API_SECRET'));
        $senderId  = config('services.camoo.sender_id', env('CAMOO_SENDER_ID', 'CHRACERH'));

        if (empty($apiKey) || empty($apiSecret)) {
            Log::info("[CAMOO SIMULATION LOCALE] To: {$formattedPhone} | Msg: {$message}");
            return [
                'success' => true,
                'status'  => 'SIMULATED',
                'details' => 'Simulation locale réussie (clés Camoo non configurées).',
            ];
        }

        // Format national camerounais attendu par Camoo : 9 chiffres (ex: 681964798)
        $cleanPhone = preg_replace('/[^0-9]/', '', $formattedPhone);
        if (str_starts_with($cleanPhone, '237') && strlen($cleanPhone) === 12) {
            $cleanPhone = substr($cleanPhone, 3);
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'X-Api-Key'    => $apiKey,
                'X-Api-Secret' => $apiSecret,
                'Accept'       => 'application/json',
            ])->asForm()->timeout(15)->post('https://api.camoo.cm/v1/sms/send.json', [
                'from'    => $senderId ?: 'CHRACERH',
                'to'      => $cleanPhone,
                'message' => $message,
            ]);

            $statusCode = $response->status();
            $data = $response->json();

            if ($statusCode === 200) {
                $msgCount = $data['sms']['message-count'] ?? null;
                $msgDetails = $data['sms']['messages'][0] ?? ($data['sms']['messages'] ?? []);
                $msgStatus = $msgDetails['status'] ?? null;

                if ($msgStatus === '201' || (isset($data['_message']) && is_string($data['_message']) && str_contains(strtolower($data['_message']), 'low'))) {
                    $errorMsg = $data['_message'] ?? ($msgDetails['error-text'] ?? 'Solde insuffisant');
                    return [
                        'success' => false,
                        'status'  => 'FAILED',
                        'details' => "Réponse Camoo SMS : {$errorMsg}",
                    ];
                }

                $msgId = $msgDetails['message-id'] ?? ('CAMOO_' . uniqid());
                return [
                    'success' => true,
                    'status'  => 'SENT',
                    'details' => "Délivré via Camoo SMS (National) | Dest: {$cleanPhone} | Émetteur: {$senderId}",
                ];
            }

            $errorMsg = $data['_message'] ?? ($data['message'] ?? ($response->body() ?: 'Erreur Camoo'));
            return [
                'success' => false,
                'status'  => 'FAILED',
                'details' => "Réponse Camoo SMS (HTTP {$statusCode}) : {$errorMsg}",
            ];
        } catch (\Throwable $e) {
            Log::error("[SmsService] Erreur appel Camoo SMS : " . $e->getMessage());
            return [
                'success' => false,
                'status'  => 'FAILED',
                'details' => 'Erreur de connexion Camoo : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Passerelle Africa's Talking.
     */
    private static function sendViaAfricasTalking(string $formattedPhone, string $message): array
    {
        $apiKey   = config('services.africastalking.api_key', env('AFRICASTALKING_API_KEY'));
        $username = config('services.africastalking.username', env('AFRICASTALKING_USERNAME', 'sandbox'));
        $senderId = config('services.africastalking.sender_id', env('AFRICASTALKING_SENDER_ID', 'CHRACERH'));

        if (empty($apiKey)) {
            Log::info("[SMS SIMULATION LOCALE] To: {$formattedPhone} | Msg: {$message}");
            return [
                'success' => true,
                'status'  => 'SIMULATED',
                'details' => 'Simulation locale réussie (aucune clé API configurée dans .env).',
            ];
        }

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

            if (!$isSandbox && !empty($senderId)) {
                $postData['from'] = $senderId;
            }

            $response = Http::withoutVerifying()->withHeaders([
                'apiKey' => $apiKey,
                'apikey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->timeout(15)->post($endpoint, $postData);

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
