<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Pointage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceApiController extends Controller
{
    /**
     * Enregistre le visage d'un employé envoyé depuis son mobile.
     */
    public function registerFace(Request $request, $id): JsonResponse
    {
        $employee = Employe::find($id);
        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employé introuvable.'
            ], 404);
        }

        $request->validate([
            'image' => 'required'
        ]);

        try {
            $imageContent = null;
            if ($request->hasFile('image')) {
                $imageContent = file_get_contents($request->file('image')->getRealPath());
            } else {
                $rawBase64 = $request->input('image');
                $cleanedBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $rawBase64);
                $imageContent = base64_decode($cleanedBase64);
            }

            if (!$imageContent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format d\'image invalide.'
                ], 400);
            }

            // Assurer l'existence du dossier faces
            if (!Storage::disk('public')->exists('faces')) {
                Storage::disk('public')->makeDirectory('faces');
            }

            $filename = "faces/{$employee->id}.jpg";
            Storage::disk('public')->put($filename, $imageContent);

            // Vecteur binaire fictif (128 floats)
            $floats = [];
            for ($i = 0; $i < 128; $i++) {
                $floats[] = (float) rand() / (float) getrandmax();
            }
            $binaryEmbeddings = pack('f*', ...$floats);

            $employee->update([
                'consent_facial_at' => now(),
                'face_embeddings' => $binaryEmbeddings,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Visage enregistré avec succès !',
                'consent_facial_at' => $employee->consent_facial_at->toDateTimeString()
            ], 200);

        } catch (\Throwable $e) {
            Log::error('FaceApiController registerFace error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'enregistrement facial : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authentifie un employé au Kiosk par reconnaissance faciale.
     */
    public function verifyFace(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|string',
            'category' => 'nullable|in:EMPLOYE,PATIENT',
            'id' => 'nullable|integer'
        ]);

        try {
            $rawBase64 = $request->input('image');
            $cleanedBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $rawBase64);
            $imageContent = base64_decode($cleanedBase64);

            if (!$imageContent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Image invalide.'
                ], 400);
            }

            // Si catégorie et ID sont fournis explicitement (sélection/simulation de la borne ou mode hybride)
            if ($request->category && $request->id) {
                if ($request->category === 'EMPLOYE') {
                    $employee = Employe::with('department')->find($request->id);
                    if (!$employee) return response()->json(['status' => 'error', 'message' => 'Employé introuvable.'], 404);

                    $lastPointage = Pointage::where('employee_id', $employee->id)
                        ->whereDate('timestamp', now()->toDateString())
                        ->orderBy('timestamp', 'desc')
                        ->first();

                    $type = ($lastPointage && $lastPointage->type === 'ENTREE') ? 'SORTIE' : 'ENTREE';
                    $status = ($type === 'ENTREE' && date('H:i') > '08:15') ? 'Retard' : 'Present';

                    $pointage = Pointage::create([
                        'employee_id' => $employee->id,
                        'type' => $type,
                        'status' => $status,
                        'source' => 'FACE',
                        'timestamp' => now(),
                        'confidence_score' => 0.98,
                        'anomaly_detected' => false
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'category' => 'EMPLOYE',
                        'message' => 'Pointage facial réussi pour l\'employé !',
                        'data' => [
                            'name' => $employee->first_name . ' ' . $employee->last_name,
                            'department' => $employee->department ? $employee->department->name : 'N/A',
                            'type' => $pointage->type,
                            'time' => $pointage->timestamp->format('H:i:s'),
                            'simulated' => true
                        ]
                    ], 200);
                } else {
                    $rdv = \App\Models\RendezVous::find($request->id);
                    if (!$rdv) return response()->json(['status' => 'error', 'message' => 'Rendez-vous introuvable.'], 404);

                    $rdv->update([
                        'statut' => 'Présent',
                        'pointage_at' => now()
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'category' => 'PATIENT',
                        'message' => 'Arrivée du patient enregistrée par reconnaissance faciale !',
                        'data' => [
                            'name' => $rdv->nom_patient,
                            'service' => $rdv->service ?? 'Consultation',
                            'medecin' => $rdv->medecin ?? 'Médecin traitant',
                            'time' => Carbon::now()->format('H:i:s'),
                            'simulated' => true
                        ]
                    ], 200);
                }
            }

            // Sinon recherche automatique (Gemini IA) pour les employés et patients
            $employees = Employe::whereNotNull('consent_facial_at')->get()->filter(function ($emp) {
                return Storage::disk('public')->exists("faces/{$emp->id}.jpg");
            });

            $patients = \App\Models\RendezVous::whereDate('date_rendez_vous', Carbon::today())
                ->whereNotNull('consent_facial_at')->get()->filter(function ($rdv) {
                return Storage::disk('public')->exists("faces/patients/{$rdv->id}.jpg");
            });

            if ($employees->isEmpty() && $patients->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun profil facial enregistré pour aujourd\'hui. Veuillez sélectionner votre profil.'
                ], 400);
            }

            $matchedEmployeeId = null;
            $matchedPatientId = null;
            $confidence = 0.0;
            $usedGemini = false;
            $apiKey = env('GEMINI_API_KEY');

            if (!empty($apiKey)) {
                try {
                    $client = \Gemini::client($apiKey);
                    $blobCaptured = new \Gemini\Data\Blob(
                        mimeType: 'image/jpeg',
                        data: base64_encode($imageContent)
                    );

                    $parts = [
                        "Tu es un système de reconnaissance faciale haute précision pour l'hôpital CHRACERH. " .
                        "L'image 0 est la photo de la personne à la borne de pointage. " .
                        "Les images suivantes représentent les visages officiels enregistrés (Employés ou Patients). " .
                        "Compare attentivement le visage de l'image 0 avec chacun. " .
                        "Réponds exclusivement sous forme de JSON valide avec trois clés :\n" .
                        "- 'matched_type' : 'EMPLOYE' ou 'PATIENT' ou null.\n" .
                        "- 'matched_id' : l'ID numérique correspondant (int ou null).\n" .
                        "- 'confidence' : le score de confiance entre 0.0 et 1.0.\n" .
                        "Si aucune correspondance sûre (> 0.75), renvoie null pour matched_id.",
                        $blobCaptured
                    ];

                    $hasCandidateFiles = false;
                    foreach ($employees as $emp) {
                        $fileData = base64_encode(Storage::disk('public')->get("faces/{$emp->id}.jpg"));
                        $parts[] = "EMPLOYE ID: {$emp->id}";
                        $parts[] = new \Gemini\Data\Blob(mimeType: 'image/jpeg', data: $fileData);
                        $hasCandidateFiles = true;
                    }
                    foreach ($patients as $pt) {
                        $fileData = base64_encode(Storage::disk('public')->get("faces/patients/{$pt->id}.jpg"));
                        $parts[] = "PATIENT ID: {$pt->id}";
                        $parts[] = new \Gemini\Data\Blob(mimeType: 'image/jpeg', data: $fileData);
                        $hasCandidateFiles = true;
                    }

                    if ($hasCandidateFiles) {
                        $response = $client->generativeModel('gemini-2.5-flash')->generateContent(...$parts);
                        $responseText = trim($response->text() ?? '');

                        if (preg_match('/\{.*?\}/s', $responseText, $matches)) {
                            $jsonData = json_decode($matches[0], true);
                            if ($jsonData && isset($jsonData['matched_id'])) {
                                if (($jsonData['matched_type'] ?? '') === 'PATIENT') {
                                    $matchedPatientId = $jsonData['matched_id'];
                                } else {
                                    $matchedEmployeeId = $jsonData['matched_id'];
                                }
                                $confidence = (float)($jsonData['confidence'] ?? 0.0);
                                $usedGemini = true;
                            }
                        }
                    }
                } catch (\Throwable $geminiError) {
                    Log::error('FaceApiController verifyFace Gemini Error: ' . $geminiError->getMessage());
                }
            }

            if (!$usedGemini || ($confidence < 0.75 && !$matchedEmployeeId && !$matchedPatientId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reconnaissance automatique échouée. Veuillez sélectionner votre profil sur l\'écran pour pointer.'
                ], 401);
            }

            if ($matchedPatientId) {
                $rdv = \App\Models\RendezVous::find($matchedPatientId);
                if (!$rdv) return response()->json(['status' => 'error', 'message' => 'Rendez-vous introuvable.'], 404);

                $rdv->update(['statut' => 'Présent', 'pointage_at' => now()]);

                return response()->json([
                    'status' => 'success',
                    'category' => 'PATIENT',
                    'message' => 'Arrivée du patient enregistrée avec succès !',
                    'data' => [
                        'name' => $rdv->nom_patient,
                        'service' => $rdv->service ?? 'Consultation',
                        'medecin' => $rdv->medecin ?? 'Médecin traitant',
                        'time' => Carbon::now()->format('H:i:s'),
                        'simulated' => !$usedGemini
                    ]
                ], 200);
            }

            $employee = Employe::with('department')->find($matchedEmployeeId);
            if (!$employee) return response()->json(['status' => 'error', 'message' => 'Employé identifié introuvable.'], 404);

            $lastPointage = Pointage::where('employee_id', $employee->id)
                ->whereDate('timestamp', now()->toDateString())
                ->orderBy('timestamp', 'desc')
                ->first();

            $type = ($lastPointage && $lastPointage->type === 'ENTREE') ? 'SORTIE' : 'ENTREE';
            $status = ($type === 'ENTREE' && date('H:i') > '08:15') ? 'Retard' : 'Present';

            $pointage = Pointage::create([
                'employee_id' => $employee->id,
                'type' => $type,
                'status' => $status,
                'source' => 'FACE',
                'timestamp' => now(),
                'confidence_score' => $confidence,
                'anomaly_detected' => false
            ]);

            return response()->json([
                'status' => 'success',
                'category' => 'EMPLOYE',
                'message' => 'Pointage enregistré avec succès !',
                'data' => [
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'department' => $employee->department ? $employee->department->name : 'N/A',
                    'type' => $pointage->type,
                    'time' => $pointage->timestamp->format('H:i:s'),
                    'simulated' => !$usedGemini
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('FaceApiController verifyFace error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la vérification faciale : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifie si un employé a enregistré son visage.
     */
    public function checkFaceStatus($id): JsonResponse
    {
        $employee = Employe::find($id);
        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employé introuvable.'
            ], 404);
        }

        $isRegistered = !empty($employee->consent_facial_at) && Storage::disk('public')->exists("faces/{$employee->id}.jpg");

        return response()->json([
            'status' => 'success',
            'registered' => $isRegistered,
            'consent_facial_at' => $employee->consent_facial_at ? $employee->consent_facial_at->toDateTimeString() : null
        ], 200);
    }
}
