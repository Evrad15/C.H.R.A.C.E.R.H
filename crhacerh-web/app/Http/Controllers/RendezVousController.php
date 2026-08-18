<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Models\Patient;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RendezVousController extends Controller
{
    /**
     * Affiche la liste des rendez-vous avec filtres, recherche et pagination sur-mesure (20 par page).
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();

        // Statistiques globales pour les cartes supérieures
        $statsToday = [
            'total'     => RendezVous::whereDate('date_rendez_vous', $today)->count(),
            'confirme'  => RendezVous::whereDate('date_rendez_vous', $today)->where('statut', 'Confirmé')->count(),
            'realise'   => RendezVous::whereDate('date_rendez_vous', $today)->where('statut', 'Réalisé')->count(),
            'annule'    => RendezVous::whereDate('date_rendez_vous', $today)->whereIn('statut', ['Annulé', 'Reporté'])->count(),
        ];

        // Requête principale
        $query = RendezVous::with('patient');

        // Filtre par recherche textuelle (Patient, Téléphone, Médecin, Service)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($pq) use ($search) {
                    $pq->where('nom_complet', 'ILIKE', "%{$search}%")
                       ->orWhere('numero_dossier', 'ILIKE', "%{$search}%");
                })
                ->orWhere('telephone_patient', 'ILIKE', "%{$search}%")
                ->orWhere('email_patient', 'ILIKE', "%{$search}%")
                ->orWhere('medecin', 'ILIKE', "%{$search}%")
                ->orWhere('service', 'ILIKE', "%{$search}%")
                ->orWhere('motif', 'ILIKE', "%{$search}%");
            });
        }

        // Filtre par Statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->get('statut'));
        }

        // Filtre par Service
        if ($request->filled('service')) {
            $query->where('service', $request->get('service'));
        }

        // Filtre par Période (Aujourd'hui, À venir, Passés)
        if ($request->filled('periode')) {
            switch ($request->get('periode')) {
                case 'today':
                    $query->whereDate('date_rendez_vous', $today);
                    break;
                case 'upcoming':
                    $query->whereDate('date_rendez_vous', '>=', $today);
                    break;
                case 'past':
                    $query->whereDate('date_rendez_vous', '<', $today);
                    break;
            }
        }

        // Tri par date décroissante
        $rendezvous = $query->orderBy('date_rendez_vous', 'desc')
                            ->paginate(20)
                            ->withQueryString();

        // Liste des services disponibles pour les filtres
        $services = [
            'Chirurgie Endoscopique',
            'FIV / Reproduction Humaine',
            'Consultation Gynécologique',
            'Échographie & Imagerie',
            'Suivi de Grossesse',
            'Consultation d’Infertilité',
        ];

        // Liste des médecins du CHRACERH
        $medecins = [
            'Dr. Jean-Paul MBALLA',
            'Dr. Marie-Claire ESSOMBA',
            'Dr. Etienne NKOULOU',
            'Dr. Sophie ATANGANA',
            'Dr. Francis ETOUNDI',
        ];

        return view('rendezvous.index', [
            'rendezvous' => $rendezvous,
            'statsToday' => $statsToday,
            'services'   => $services,
            'medecins'   => $medecins,
        ]);
    }

    /**
     * Enregistre un nouveau rendez-vous.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_patient'       => 'required|string|max:150',
            'telephone_patient' => 'nullable|string|max:30',
            'email_patient'     => 'nullable|email|max:150',
            'service'           => 'required|string|max:100',
            'motif'             => 'required|string|max:255',
            'date_rendez_vous'  => 'required|date',
            'heure_rendez_vous' => 'required|string',
            'medecin'           => 'nullable|string|max:150',
            'notes'             => 'nullable|string',
        ]);

        // Créer ou récupérer le patient
        $patient = Patient::firstOrCreate(
            ['nom_complet' => trim($validated['nom_patient'])],
            [
                'numero_dossier' => 'DOS-' . rand(10000, 99999),
                'telephone'      => $validated['telephone_patient'] ?? null,
            ]
        );

        $dateTimeStr = $validated['date_rendez_vous'] . ' ' . $validated['heure_rendez_vous'];
        $dateStart = Carbon::parse($dateTimeStr);
        $dateEnd   = (clone $dateStart)->addMinutes(30);

        $rdv = RendezVous::create([
            'patient_id'        => $patient->id,
            'nom_patient'       => trim($validated['nom_patient']),
            'telephone_patient' => $validated['telephone_patient'] ?? $patient->telephone,
            'email_patient'     => $validated['email_patient'] ?? null,
            'service'           => $validated['service'],
            'motif'             => $validated['motif'],
            'date_rendez_vous'  => $dateStart,
            'fin_rendez_vous'   => $dateEnd,
            'medecin'           => $validated['medecin'] ?? 'Généraliste',
            'statut'            => 'Planifié',
            'notes'             => $validated['notes'] ?? null,
        ]);

        // ── ENREGISTREMENT BIOMÉTRIQUE FACIAL PATIENT (ArcFace 512D) ──
        if ($request->filled('register_face')) {
            try {
                $rawBase64 = $request->input('register_face');
                $cleanedBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $rawBase64);
                $imageContent = base64_decode($cleanedBase64);

                if ($imageContent) {
                    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('faces/patients')) {
                        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('faces/patients');
                    }
                    \Illuminate\Support\Facades\Storage::disk('public')->put("faces/patients/{$rdv->id}.jpg", $imageContent);

                    $tempPath = storage_path('app/public/_temp_pat_' . $rdv->id . '_' . uniqid() . '.jpg');
                    file_put_contents($tempPath, $imageContent);
                    $scriptPath = base_path('scripts/face_recognize.py');
                    $cmd = 'python ' . escapeshellarg($scriptPath) . ' extract ' . escapeshellarg($tempPath);
                    $pyOutput = shell_exec($cmd);
                    if (file_exists($tempPath)) @unlink($tempPath);

                    if ($pyOutput) {
                        $jsonLine = null;
                        foreach (explode("\n", $pyOutput) as $line) {
                            $line = trim($line);
                            if (str_starts_with($line, '{')) {
                                $jsonLine = $line;
                                break;
                            }
                        }
                        if ($jsonLine) {
                            $pyData = json_decode($jsonLine, true);
                            if ($pyData && isset($pyData['status']) && $pyData['status'] === 'success' && isset($pyData['dims']) && $pyData['dims'] === 512) {
                                $binaryEmbeddings = pack('f*', ...$pyData['embedding']);
                                $rdv->update([
                                    'face_embeddings'   => base64_encode($binaryEmbeddings),
                                    'consent_facial_at' => now(),
                                ]);
                                Log::info("Visage ArcFace 512D enregistré avec succès pour RDV #{$rdv->id} ({$rdv->nom_patient})");
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Erreur enregistrement visage RDV : " . $e->getMessage());
            }
        }

        // ── ENREGISTREMENT EMPREINTE DIGITALE PATIENT ──
        if ($request->filled('register_fingerprint')) {
            $rdv->update([
                'fingerprint_data'          => $request->input('register_fingerprint'),
                'fingerprint_registered_at' => now(),
            ]);
        }

        // ── DÉCLENCHEUR SMS AUTOMATIQUE : Confirmation immédiate ──
        try {
            if (!empty($rdv->telephone_patient)) {
                SmsService::sendConfirmation($rdv);
            }
        } catch (\Throwable $e) {
            Log::error('Erreur envoi SMS confirmation auto : ' . $e->getMessage());
        }

        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous créé avec succès et profil biométrique enregistré !');
    }

    /**
     * Met à jour le statut d'un rendez-vous (AJAX ou Form).
     */
    public function updateStatus(Request $request, $id)
    {
        $rdv = RendezVous::findOrFail($id);
        $oldStatus = $rdv->statut;
        
        $request->validate([
            'statut' => 'required|in:Planifié,Confirmé,Présent,Réalisé,Annulé,Reporté',
        ]);

        // ── RÈGLE MÉTIER STRICTE : Verrouillage des statuts avancés/terminaux ──
        // On ne peut plus revenir à "Confirmé" ou "Planifié" si le RDV est déjà Réalisé, Présent, Reporté, Annulé ou si le patient a pointé.
        $isAdvancedOrTerminal = in_array($oldStatus, ['Présent', 'Réalisé', 'Reporté', 'Annulé']) || !empty($rdv->pointage_at);
        if ($isAdvancedOrTerminal && in_array($request->statut, ['Confirmé', 'Planifié'])) {
            $msg = "Action non autorisée : Impossible de repasser à l'état '{$request->statut}' car ce rendez-vous est déjà '{$oldStatus}'" . (!empty($rdv->pointage_at) ? " (présence déjà enregistrée à la borne)" : "") . ".";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $rdv->update(['statut' => $request->statut]);

        // ── DÉCLENCHEURS SMS AUTOMATIQUES SPÉCIFIQUES À CHAQUE STATUT ──
        try {
            if (!empty($rdv->telephone_patient) && $oldStatus !== $request->statut) {
                switch ($request->statut) {
                    case 'Confirmé':
                        SmsService::sendConfirmation($rdv);
                        break;
                    case 'Réalisé':
                        SmsService::sendCompleted($rdv);
                        break;
                    case 'Reporté':
                        SmsService::sendRescheduled($rdv);
                        break;
                    case 'Annulé':
                        SmsService::sendCancellation($rdv);
                        break;
                    case 'Présent':
                        SmsService::sendCheckIn($rdv);
                        break;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Erreur envoi SMS statut auto : ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'statut' => $rdv->statut, 'message' => 'Statut mis à jour et SMS de notification envoyé.']);
        }

        return redirect()->back()->with('success', "Statut du rendez-vous mis à jour en '{$rdv->statut}' et SMS envoyé au patient.");
    }

    /**
     * Met à jour les informations complètes d'un rendez-vous.
     */
    public function update(Request $request, $id)
    {
        $rdv = RendezVous::findOrFail($id);

        $validated = $request->validate([
            'nom_patient'       => 'required|string|max:150',
            'telephone_patient' => 'nullable|string|max:30',
            'email_patient'     => 'nullable|email|max:150',
            'service'           => 'required|string|max:100',
            'motif'             => 'required|string|max:255',
            'date_rendez_vous'  => 'required|date',
            'heure_rendez_vous' => 'required|string',
            'medecin'           => 'nullable|string|max:150',
            'notes'             => 'nullable|string',
        ]);

        if ($rdv->patient) {
            $rdv->patient->update([
                'nom_complet' => trim($validated['nom_patient']),
                'telephone'   => $validated['telephone_patient'] ?? $rdv->patient->telephone,
            ]);
        }

        $dateTimeStr = $validated['date_rendez_vous'] . ' ' . $validated['heure_rendez_vous'];
        $dateStart = Carbon::parse($dateTimeStr);
        $dateEnd   = (clone $dateStart)->addMinutes(30);

        $rdv->update([
            'nom_patient'       => trim($validated['nom_patient']),
            'telephone_patient' => $validated['telephone_patient'],
            'email_patient'     => $validated['email_patient'],
            'service'           => $validated['service'],
            'motif'             => $validated['motif'],
            'date_rendez_vous'  => $dateStart,
            'fin_rendez_vous'   => $dateEnd,
            'medecin'           => $validated['medecin'],
            'notes'             => $validated['notes'],
        ]);

        // ── MISE À JOUR BIOMÉTRIQUE FACIALE (ArcFace 512D) ──
        if ($request->filled('register_face')) {
            try {
                $rawBase64 = $request->input('register_face');
                $cleanedBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $rawBase64);
                $imageContent = base64_decode($cleanedBase64);

                if ($imageContent) {
                    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('faces/patients')) {
                        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('faces/patients');
                    }
                    \Illuminate\Support\Facades\Storage::disk('public')->put("faces/patients/{$rdv->id}.jpg", $imageContent);

                    $tempPath = storage_path('app/public/_temp_pat_' . $rdv->id . '_' . uniqid() . '.jpg');
                    file_put_contents($tempPath, $imageContent);
                    $scriptPath = base_path('scripts/face_recognize.py');
                    $cmd = 'python ' . escapeshellarg($scriptPath) . ' extract ' . escapeshellarg($tempPath);
                    $pyOutput = shell_exec($cmd);
                    if (file_exists($tempPath)) @unlink($tempPath);

                    if ($pyOutput) {
                        $jsonLine = null;
                        foreach (explode("\n", $pyOutput) as $line) {
                            $line = trim($line);
                            if (str_starts_with($line, '{')) {
                                $jsonLine = $line;
                                break;
                            }
                        }
                        if ($jsonLine) {
                            $pyData = json_decode($jsonLine, true);
                            if ($pyData && isset($pyData['status']) && $pyData['status'] === 'success' && isset($pyData['dims']) && $pyData['dims'] === 512) {
                                $binaryEmbeddings = pack('f*', ...$pyData['embedding']);
                                $rdv->update([
                                    'face_embeddings'   => base64_encode($binaryEmbeddings),
                                    'consent_facial_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Erreur mise à jour visage RDV : " . $e->getMessage());
            }
        }

        if ($request->filled('register_fingerprint')) {
            $rdv->update([
                'fingerprint_data'          => $request->input('register_fingerprint'),
                'fingerprint_registered_at' => now(),
            ]);
        }

        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous mis à jour avec succès !');
    }

    /**
     * Supprime un rendez-vous.
     */
    public function destroy($id)
    {
        $rdv = RendezVous::findOrFail($id);
        $rdv->delete();

        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous supprimé avec succès !');
    }
}
