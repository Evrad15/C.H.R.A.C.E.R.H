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

            // Sauvegarder la photo brute
            $filename = "faces/{$employee->id}.jpg";
            Storage::disk('public')->put($filename, $imageContent);

            // --- Extraction de l'embedding ArcFace 512D via Python ---
            $realEmbedding = null;
            try {
                $tempPath = storage_path('app/public/_temp_register_' . $employee->id . '_' . time() . '.jpg');
                file_put_contents($tempPath, $imageContent);
                $scriptPath = base_path('scripts/face_recognize.py');
                $cmd = 'python ' . escapeshellarg($scriptPath) . ' extract ' . escapeshellarg($tempPath);
                $pyOutput = shell_exec($cmd);
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                // InsightFace écrit des logs sur stdout avant le JSON — on extrait uniquement la ligne JSON
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
                        if ($pyData && isset($pyData['status']) && $pyData['status'] === 'success'
                            && isset($pyData['dims']) && $pyData['dims'] === 512) {
                            $embedding = $pyData['embedding']; // tableau de 512 floats ArcFace
                            $binaryEmbeddings = pack('f*', ...$embedding);
                            $realEmbedding = base64_encode($binaryEmbeddings);

                            // --- Vérification anti-doublon biométrique ---
                            // Empêche d'enregistrer le même visage pour deux employés différents
                            $newVec = array_values(unpack('f*', $binaryEmbeddings));
                            $duplicateId = null;
                            $otherEmployees = Employe::where('id', '!=', $employee->id)
                                ->whereNotNull('face_embeddings')
                                ->get(['id', 'first_name', 'last_name', 'face_embeddings']);
                            foreach ($otherEmployees as $other) {
                                $otherBin = $other->face_embeddings;
                                if (is_resource($otherBin)) $otherBin = stream_get_contents($otherBin);
                                if (empty($otherBin)) continue;
                                $otherVec = array_values(unpack('f*', base64_decode($otherBin)));
                                if (count($otherVec) !== 512) continue;
                                // Similarité cosinus rapide en PHP
                                $dot = 0.0; $n1 = 0.0; $n2 = 0.0;
                                for ($i = 0; $i < 512; $i++) {
                                    $dot += $newVec[$i] * $otherVec[$i];
                                    $n1  += $newVec[$i] * $newVec[$i];
                                    $n2  += $otherVec[$i] * $otherVec[$i];
                                }
                                $cos = ($n1 > 0 && $n2 > 0) ? $dot / (sqrt($n1) * sqrt($n2)) : 0.0;
                                if ($cos >= 0.55) { // seuil strict pour doublons évidents
                                    $duplicateId = $other->id;
                                    Log::warning("registerFace: Visage doublon détecté. Employé #{$employee->id} ressemble à #{$other->id} (cos={$cos}).");
                                    break;
                                }
                            }
                            if ($duplicateId) {
                                $dup = Employe::find($duplicateId);
                                return response()->json([
                                    'status'  => 'error',
                                    'message' => "Enregistrement refusé : ce visage est déjà associé à l'employé #{$duplicateId} ({$dup->first_name} {$dup->last_name}). Contactez l'administrateur."
                                ], 409);
                            }
                        }
                    }
                }
            } catch (\Throwable $pyEx) {
                Log::warning('registerFace: Python ArcFace extraction failed: ' . $pyEx->getMessage());
            }

            // Si Python n'est pas disponible, on log un avertissement (pas de fallback aléatoire)
            if (!$realEmbedding) {
                Log::warning("registerFace: Impossible d'extraire l'embedding ArcFace pour l'employé {$employee->id}. Photo sauvegardée uniquement.");
            }

            $updateData = ['consent_facial_at' => now()];
            if ($realEmbedding) {
                $updateData['face_embeddings']          = $realEmbedding;
                $updateData['face_embedding_updated_at'] = now(); // Traçabilité fraîcheur ArcFace
            }
            $employee->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Visage enregistré avec succès !',
                'embedding_extracted' => !is_null($realEmbedding),
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

            // Si catégorie et ID sont fournis explicitement (sélection sur la borne)
            // → Vérification biométrique RÉELLE via ArcFace Python (plus de rand())
            if ($request->category && $request->id) {

                $scriptPath = base_path('scripts/face_recognize.py');
                $tempPath   = storage_path('app/public/_temp_verify_manual.jpg');
                file_put_contents($tempPath, $imageContent);

                if ($request->category === 'EMPLOYE') {
                    $employee = Employe::with('department')->find($request->id);
                    if (!$employee) return response()->json(['status' => 'error', 'message' => 'Employé introuvable.'], 404);

                    // Vérifier qu'un profil facial existe
                    $embeddings = $employee->face_embeddings;
                    if (is_resource($embeddings)) $embeddings = stream_get_contents($embeddings);

                    if (empty($embeddings)) {
                        if (file_exists($tempPath)) @unlink($tempPath);
                        return response()->json([
                            'status'  => 'error',
                            'message' => "Aucun profil facial ArcFace enregistré pour {$employee->first_name} {$employee->last_name}. Veuillez d'abord enregistrer votre visage."
                        ], 400);
                    }

                    // Appel ArcFace réel : compare le visage capturé contre l'embedding DB de cet employé
                    $binary    = base64_decode($embeddings);
                    $candVector = array_values(unpack('f*', $binary));
                    $similarityPercent = 0.0;
                    $cosSimRaw = 0.0;

                    if (count($candVector) === 512) {
                        $candidatesJson = json_encode([[
                            'id'        => $employee->id,
                            'type'      => 'EMPLOYE',
                            'embedding' => $candVector,
                        ]]);
                        $cmd       = 'python ' . escapeshellarg($scriptPath)
                                   . ' compare_vectors ' . escapeshellarg($tempPath)
                                   . ' ' . escapeshellarg($candidatesJson);
                        $pyOutput  = shell_exec($cmd);
                        $pyData    = $pyOutput ? json_decode(trim($pyOutput), true) : null;

                        if ($pyData && ($pyData['status'] ?? '') === 'success') {
                            $similarityPercent = (float)($pyData['similarity'] ?? 0.0);
                            $cosSimRaw         = (float)($pyData['cos_sim']   ?? 0.0);
                        }
                    }

                    if (file_exists($tempPath)) @unlink($tempPath);

                    // Seuil ArcFace 95% affiché (cosinus >= 0.40 interne)
                    if ($similarityPercent < 95.0) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => "Rejet Biométrique ArcFace : Similitude ({$similarityPercent}%) insuffisante pour {$employee->first_name} {$employee->last_name}. Votre visage ne correspond pas au profil enregistré."
                        ], 401);
                    }

                    $lastPointage = Pointage::where('employee_id', $employee->id)
                        ->whereDate('timestamp', now()->toDateString())
                        ->orderBy('timestamp', 'desc')
                        ->first();

                    $type   = ($lastPointage && $lastPointage->type === 'ENTREE') ? 'SORTIE' : 'ENTREE';
                    $status = ($type === 'ENTREE' && date('H:i') > '08:15') ? 'Retard' : 'Present';

                    $pointage = Pointage::create([
                        'employee_id'      => $employee->id,
                        'type'             => $type,
                        'status'           => $status,
                        'source'           => 'FACE',
                        'timestamp'        => now(),
                        'confidence_score' => round($cosSimRaw, 4),
                        'anomaly_detected' => false
                    ]);

                    return response()->json([
                        'status'   => 'success',
                        'category' => 'EMPLOYE',
                        'message'  => "Pointage facial ArcFace validé ! (Similitude : {$similarityPercent}% ≥ 95%)",
                        'data'     => [
                            'name'       => $employee->first_name . ' ' . $employee->last_name,
                            'department' => $employee->department ? $employee->department->name : 'N/A',
                            'type'       => $pointage->type,
                            'time'       => $pointage->timestamp->format('H:i:s'),
                            'similarity' => "{$similarityPercent}%",
                            'engine'     => 'arcface_512d'
                        ]
                    ], 200);

                } else {
                    // PATIENT
                    $rdv = \App\Models\RendezVous::find($request->id);
                    if (!$rdv) {
                        if (file_exists($tempPath)) @unlink($tempPath);
                        return response()->json(['status' => 'error', 'message' => 'Rendez-vous introuvable.'], 404);
                    }

                    // Comparer contre la photo stockée sur disque (patients n'ont pas forcément d'embedding DB)
                    $patientFacePath = Storage::disk('public')->path("faces/patients/{$rdv->id}.jpg");
                    $similarityPercent = 0.0;
                    $cosSimRaw = 0.0;

                    if (file_exists($patientFacePath)) {
                        $cmd      = 'python ' . escapeshellarg($scriptPath)
                                  . ' compare ' . escapeshellarg($tempPath)
                                  . ' ' . escapeshellarg(Storage::disk('public')->path('faces/patients'));
                        // Comparaison simplifiée : on extrait l'embedding du patient et on compare
                        $cmdExtract = 'python ' . escapeshellarg($scriptPath) . ' extract ' . escapeshellarg($patientFacePath);
                        $pyExtract  = shell_exec($cmdExtract);
                        $extData    = $pyExtract ? json_decode(trim($pyExtract), true) : null;

                        if ($extData && ($extData['status'] ?? '') === 'success' && count($extData['embedding']) === 512) {
                            $candidatesJson = json_encode([[
                                'id'        => $rdv->id,
                                'type'      => 'PATIENT',
                                'embedding' => $extData['embedding'],
                            ]]);
                            $cmdCmp    = 'python ' . escapeshellarg($scriptPath)
                                       . ' compare_vectors ' . escapeshellarg($tempPath)
                                       . ' ' . escapeshellarg($candidatesJson);
                            $pyOutput  = shell_exec($cmdCmp);
                            $pyData    = $pyOutput ? json_decode(trim($pyOutput), true) : null;

                            if ($pyData && ($pyData['status'] ?? '') === 'success') {
                                $similarityPercent = (float)($pyData['similarity'] ?? 0.0);
                                $cosSimRaw         = (float)($pyData['cos_sim']   ?? 0.0);
                            }
                        }
                    } elseif (empty($rdv->consent_facial_at)) {
                        if (file_exists($tempPath)) @unlink($tempPath);
                        return response()->json([
                            'status'  => 'error',
                            'message' => "Aucun profil facial enregistré pour le patient {$rdv->nom_patient}."
                        ], 400);
                    }

                    if (file_exists($tempPath)) @unlink($tempPath);

                    if ($similarityPercent < 95.0) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => "Rejet Biométrique ArcFace : Similitude ({$similarityPercent}%) insuffisante pour le patient {$rdv->nom_patient}."
                        ], 401);
                    }

                    $rdv->update(['statut' => 'Présent', 'pointage_at' => now()]);

                    return response()->json([
                        'status'   => 'success',
                        'category' => 'PATIENT',
                        'message'  => "Arrivée patient validée par ArcFace ! (Similitude : {$similarityPercent}% ≥ 95%)",
                        'data'     => [
                            'name'       => $rdv->nom_patient,
                            'service'    => $rdv->service  ?? 'Consultation',
                            'medecin'    => $rdv->medecin  ?? 'Médecin traitant',
                            'time'       => Carbon::now()->format('H:i:s'),
                            'similarity' => "{$similarityPercent}%",
                            'engine'     => 'arcface_512d'
                        ]
                    ], 200);
                }
            }

            // Recherche automatique des candidats enregistrés avec leur propre fichier photo
            $employees = Employe::whereNotNull('consent_facial_at')->get()->filter(function ($emp) {
                return Storage::disk('public')->exists("faces/{$emp->id}.jpg");
            });

            $patients = \App\Models\RendezVous::whereDate('date_rendez_vous', Carbon::today())
                ->where(function ($q) {
                    $q->whereNotNull('consent_facial_at')->orWhereNotNull('fingerprint_data');
                })->get();

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

            // --- Moteur InsightFace ArcFace : comparaison 1:N via vecteurs DB ---
            // ArcFace produit des embeddings 512D, nettement plus discriminants que
            // SFace 128D. Seuil cosinus ArcFace : >= 0.40 pour une correspondance.
            $ARCFACE_THRESHOLD = 0.40;
            try {
                // Fichier temp unique par requête pour éviter les race conditions
                $tempPath = storage_path('app/public/_temp_verify_' . uniqid() . '.jpg');
                file_put_contents($tempPath, $imageContent);
                $scriptPath = base_path('scripts/face_recognize.py');

                // Construire la liste JSON des candidats avec leurs embeddings DB
                $candidatesForPy = [];
                foreach ($employees as $emp) {
                    $embeddings = $emp->face_embeddings;
                    if (is_resource($embeddings)) {
                        $embeddings = stream_get_contents($embeddings);
                    }
                    if (empty($embeddings)) continue;
                    $binary = base64_decode($embeddings);
                    $candVector = array_values(unpack('f*', $binary));
                    // ArcFace : embeddings 512D uniquement
                    if (count($candVector) === 512) {
                        $candidatesForPy[] = [
                            'id'        => $emp->id,
                            'type'      => 'EMPLOYE',
                            'embedding' => $candVector,
                        ];
                    }
                }

                if (!empty($candidatesForPy)) {
                    $candidatesJson = json_encode($candidatesForPy);
                    $cmd = 'python ' . escapeshellarg($scriptPath)
                         . ' compare_vectors ' . escapeshellarg($tempPath)
                         . ' ' . escapeshellarg($candidatesJson);
                    $pyOutput = shell_exec($cmd);

                    if (file_exists($tempPath)) {
                        @unlink($tempPath);
                    }

                    if ($pyOutput) {
                        // Extraire la ligne JSON depuis stdout mixte (InsightFace écrit ses logs avant)
                        $jsonLine = null;
                        foreach (explode("\n", $pyOutput) as $line) {
                            $line = trim($line);
                            if (str_starts_with($line, '{')) {
                                $jsonLine = $line;
                                break;
                            }
                        }
                        $pyData = $jsonLine ? json_decode($jsonLine, true) : null;

                        if ($pyData && isset($pyData['status'])) {

                            // Cas : ambiguïté biométrique (delta < 0.05 entre Top-2)
                            if ($pyData['status'] === 'ambiguous') {
                                Log::info('verifyFace: Ambiguïté biométrique détectée', [
                                    'candidates' => $pyData['candidates'],
                                    'delta'      => $pyData['delta'] ?? null,
                                ]);
                                return response()->json([
                                    'status'     => 'ambiguous',
                                    'message'    => 'Ambiguïté biométrique détectée. Veuillez confirmer votre identité.',
                                    'candidates' => array_map(function ($c) {
                                        $emp = Employe::find($c['id']);
                                        return [
                                            'id'         => $c['id'],
                                            'type'       => $c['type'] ?? 'EMPLOYE',
                                            'name'       => $emp ? $emp->first_name . ' ' . $emp->last_name : 'Inconnu',
                                            'similarity' => $c['similarity'],
                                        ];
                                    }, $pyData['candidates']),
                                ], 200);
                            }

                            // Cas : correspondance trouvée
                            if ($pyData['status'] === 'success' && isset($pyData['matched_id'])) {
                                $matchedEmployeeId = (int)$pyData['matched_id'];
                                $confidence = (float)(($pyData['similarity'] ?? 95.0) / 100.0);
                                $usedGemini = true;
                            }
                        }
                    }
                } else {
                    if (file_exists($tempPath)) {
                        @unlink($tempPath);
                    }
                }
            } catch (\Throwable $pyEx) {
                Log::warning('ArcFace 1:N compare_vectors error: ' . $pyEx->getMessage());
            }

            // Vérification rapide de la connexion internet (timeout 400ms) pour éviter les freezes en mode hors-ligne intranet
            $isOnline = false;
            if (!empty($apiKey)) {
                $connection = @fsockopen("8.8.8.8", 53, $errno, $errstr, 0.4);
                if ($connection) {
                    $isOnline = true;
                    fclose($connection);
                }
            }

            if (!$matchedEmployeeId && $isOnline && !empty($apiKey)) {
                try {
                    $client = \Gemini::client($apiKey);
                    $blobCaptured = new \Gemini\Data\Blob(
                        mimeType: 'image/jpeg',
                        data: base64_encode($imageContent)
                    );

                    $parts = [
                        "Tu es un expert biométrique et système de reconnaissance faciale IA de très haute précision pour le centre hospitalier CHRACERH.\n" .
                        "L'image 0 (en premier) est la photo capturée à l'instant sur la borne de pointage.\n" .
                        "Les images suivantes (numérotées avec leur ID et Nom) sont les profils officiels enregistrés dans notre base de données (Employés et Patients).\n\n" .
                        "INSTRUCTIONS D'ANALYSE BIOMÉTRIQUE :\n" .
                        "1. Analyse méticuleusement les points clés du visage de l'image 0 : distance inter-pupillaire, forme du nez, arcades sourcillières, structure de la mâchoire, ligne des lèvres et géométrie du visage.\n" .
                        "2. Compare ces points géométriques avec chaque profil officiel.\n" .
                        "3. Ignore les variations mineures dues à l'éclairage de la pièce, un léger flou de la webcam, une légère rotation ou inclinaison de la tête, le port de lunettes ou les changements de coiffure.\n" .
                        "4. Identifie le profil qui correspond à la personne sur l'image 0.\n\n" .
                        "RÉPONSE JSON ATTENDUE (Réponds STRICTEMENT en JSON pur sans texte avant ou après) :\n" .
                        "{\n" .
                        "  \"matched_type\": \"EMPLOYE\" | \"PATIENT\" | null,\n" .
                        "  \"matched_id\": ID_NUMERIQUE | null,\n" .
                        "  \"confidence\": 0.95,\n" .
                        "  \"reasoning\": \"Brève explication biométrique\"\n" .
                        "}\n" .
                        "Note: Si le score de ressemblance biométrique est supérieur à 0.60, considère qu'il s'agit d'une correspondance positive et renvoie le 'matched_id' et 'matched_type' correspondants.",
                        $blobCaptured
                    ];

                    $hasCandidateFiles = false;
                    foreach ($employees as $emp) {
                        $fileData = base64_encode(Storage::disk('public')->get("faces/{$emp->id}.jpg"));
                        $parts[] = "PROFIL OFFICIEL - EMPLOYE ID: {$emp->id} (Nom : {$emp->first_name} {$emp->last_name})";
                        $parts[] = new \Gemini\Data\Blob(mimeType: 'image/jpeg', data: $fileData);
                        $hasCandidateFiles = true;
                    }
                    foreach ($patients as $pt) {
                        $fileData = base64_encode(Storage::disk('public')->get("faces/patients/{$pt->id}.jpg"));
                        $parts[] = "PROFIL OFFICIEL - PATIENT ID: {$pt->id} (Nom : {$pt->nom_patient})";
                        $parts[] = new \Gemini\Data\Blob(mimeType: 'image/jpeg', data: $fileData);
                        $hasCandidateFiles = true;
                    }

                    if ($hasCandidateFiles) {
                        $response = $client->generativeModel('gemini-2.5-flash')->generateContent(...$parts);
                        $responseText = trim($response->text() ?? '');

                        if (preg_match('/\{.*?\}/s', $responseText, $matches)) {
                            $jsonData = json_decode($matches[0], true);
                            if ($jsonData && isset($jsonData['matched_id']) && $jsonData['matched_id'] !== null) {
                                if (($jsonData['matched_type'] ?? '') === 'PATIENT') {
                                    $matchedPatientId = $jsonData['matched_id'];
                                } else {
                                    $matchedEmployeeId = $jsonData['matched_id'];
                                }
                                $confidence = (float)($jsonData['confidence'] ?? 0.85);
                                $usedGemini = true;
                            }
                        }
                    }
                } catch (\Throwable $geminiError) {
                    Log::error('FaceApiController verifyFace Gemini Error: ' . $geminiError->getMessage());
                }
            }

            if (!$usedGemini || (!$matchedEmployeeId && !$matchedPatientId)) {
                $offlineMatch = $this->verifyFaceOfflineLocal($imageContent, $employees, $patients);
                if ($offlineMatch) {
                    if ($offlineMatch['type'] === 'PATIENT') {
                        $matchedPatientId = $offlineMatch['id'];
                    } else {
                        $matchedEmployeeId = $offlineMatch['id'];
                    }
                    $confidence = $offlineMatch['score'];
                    $usedGemini = false;
                }
            }

            if (!$matchedEmployeeId && !$matchedPatientId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reconnaissance faciale non concluante. Veuillez rapprocher votre visage de la caméra ou sélectionner votre profil sur la borne.'
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
                    'name' => $employee->last_name . ' ' . $employee->first_name,
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

    /**
     * Moteur de comparaison biométrique 100% Hors Ligne en PHP local (Perceptual & Structural Hash).
     */
    private function verifyFaceOfflineLocal($capturedImageContent, $employees, $patients)
    {
        try {
            if (!function_exists('imagecreatefromstring')) return null;
            $srcImg = @imagecreatefromstring($capturedImageContent);
            if (!$srcImg) return null;

            $srcGrid = $this->computeImageStructureGrid($srcImg);
            imagedestroy($srcImg);
            if (!$srcGrid) return null;

            $candidates = [];

            foreach ($employees as $emp) {
                $file = Storage::disk('public')->path("faces/{$emp->id}.jpg");
                if (file_exists($file)) {
                    $candImg = @imagecreatefromstring(file_get_contents($file));
                    if ($candImg) {
                        $candGrid = $this->computeImageStructureGrid($candImg);
                        imagedestroy($candImg);
                        $sim = $this->computeGridSimilarity($srcGrid, $candGrid);
                        $candidates[] = ['type' => 'EMPLOYE', 'id' => $emp->id, 'score' => $sim];
                    }
                }
            }

            foreach ($patients as $pt) {
                $file = Storage::disk('public')->path("faces/patients/{$pt->id}.jpg");
                if (file_exists($file)) {
                    $candImg = @imagecreatefromstring(file_get_contents($file));
                    if ($candImg) {
                        $candGrid = $this->computeImageStructureGrid($candImg);
                        imagedestroy($candImg);
                        $sim = $this->computeGridSimilarity($srcGrid, $candGrid);
                        $candidates[] = ['type' => 'PATIENT', 'id' => $pt->id, 'score' => $sim];
                    }
                }
            }

            usort($candidates, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            if (!empty($candidates) && $candidates[0]['score'] >= 0.40) {
                // On retourne le vrai score cosinus normalisé (pas de rand())
                return $candidates[0];
            }
        } catch (\Throwable $e) {
            Log::error("Offline local face engine error: " . $e->getMessage());
        }
        return null;
    }

    private function computeImageStructureGrid($gdImg)
    {
        $w = 32;
        $h = 32;
        $origW = imagesx($gdImg);
        $origH = imagesy($gdImg);

        // Isolement centré strict du visage (55% largeur x 65% hauteur) pour éliminer le décor
        $cropW = (int)($origW * 0.55);
        $cropH = (int)($origH * 0.65);
        $cropX = (int)(($origW - $cropW) / 2);
        $cropY = (int)(($origH - $cropH) / 2);

        $thumb = imagecreatetruecolor($w, $h);
        imagecopyresampled($thumb, $gdImg, 0, 0, $cropX, $cropY, $w, $h, $cropW, $cropH);
        
        $grid = [];
        $sum = 0;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgb = imagecolorat($thumb, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray = ($r * 0.299 + $g * 0.587 + $b * 0.114);
                $grid[] = $gray;
                $sum += $gray;
            }
        }
        imagedestroy($thumb);
        
        $mean = $sum / ($w * $h);
        $normGrid = [];
        foreach ($grid as $val) {
            $normGrid[] = ($val - $mean);
        }
        return $normGrid;
    }

    private function computeGridSimilarity($grid1, $grid2)
    {
        $dot = 0.0;
        $norm1 = 0.0;
        $norm2 = 0.0;
        $count = count($grid1);
        for ($i = 0; $i < $count; $i++) {
            $v1 = $grid1[$i];
            $v2 = $grid2[$i];
            $dot += ($v1 * $v2);
            $norm1 += ($v1 * $v1);
            $norm2 += ($v2 * $v2);
        }
        if ($norm1 <= 0 || $norm2 <= 0) return 0.0;
        $cosine = $dot / (sqrt($norm1) * sqrt($norm2));
        return max(0.0, ($cosine + 1.0) / 2.0);
    }
}
