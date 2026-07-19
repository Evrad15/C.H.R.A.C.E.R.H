<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Pointage;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KioskController extends Controller
{
    /**
     * Récupère les pointages du jour pour l'affichage de la borne (Onglet 1).
     */
    public function getRecentPointages(): JsonResponse
    {
        $today = Carbon::today();

        $pointages = Pointage::with(['employee:id,first_name,last_name,department_id', 'employee.department:id,name'])
            ->whereDate('timestamp', $today)
            ->orderBy('timestamp', 'desc')
            ->take(30)
            ->get()
            ->map(function ($pointage) {
                return [
                    'id' => $pointage->id,
                    'first_name' => $pointage->employee->first_name ?? 'Employé',
                    'last_name' => substr($pointage->employee->last_name ?? 'X', 0, 1) . '.',
                    'full_name' => ($pointage->employee->first_name ?? '') . ' ' . ($pointage->employee->last_name ?? ''),
                    'department' => $pointage->employee->department ? $pointage->employee->department->name : 'N/A',
                    'type' => $pointage->type,
                    'time' => Carbon::parse($pointage->timestamp)->format('H:i:s'),
                    'source' => $pointage->source,
                    'status' => $pointage->status
                ];
            });

        return response()->json($pointages);
    }

    /**
     * Récupère les rendez-vous du jour pour l'affichage de la borne (Onglet 2).
     */
    public function getDailyRdv(): JsonResponse
    {
        $today = Carbon::today();

        $rdvs = RendezVous::whereDate('date_rendez_vous', $today)
            ->orderBy('date_rendez_vous', 'asc')
            ->get()
            ->map(function ($rdv) {
                return [
                    'id' => $rdv->id,
                    'nom_patient' => $rdv->nom_patient,
                    'service' => $rdv->service ?? 'Consultation',
                    'medecin' => $rdv->medecin ?? 'Médecin assigné',
                    'heure' => Carbon::parse($rdv->date_rendez_vous)->format('H:i'),
                    'statut' => $rdv->statut,
                    'pointage_at' => $rdv->pointage_at ? Carbon::parse($rdv->pointage_at)->format('H:i:s') : null,
                    'has_fingerprint' => !empty($rdv->fingerprint_data),
                    'has_face' => !empty($rdv->consent_facial_at)
                ];
            });

        return response()->json($rdvs);
    }

    /**
     * Récupère la liste des candidats (Employés + RDV du jour) pour la simulation / enregistrement biométrique.
     */
    public function getBiometricCandidates(): JsonResponse
    {
        $today = Carbon::today();

        $employees = Employe::with('department')->get()->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'role_info' => $emp->department ? $emp->department->name : 'Employé',
                'category' => 'EMPLOYE',
                'has_fingerprint' => !empty($emp->fingerprint_data),
                'has_face' => !empty($emp->consent_facial_at)
            ];
        });

        $patients = RendezVous::whereDate('date_rendez_vous', $today)->get()->map(function ($rdv) {
            return [
                'id' => $rdv->id,
                'name' => $rdv->nom_patient,
                'role_info' => 'RDV : ' . ($rdv->service ?? 'Consultation') . ' (' . Carbon::parse($rdv->date_rendez_vous)->format('H:i') . ')',
                'category' => 'PATIENT',
                'has_fingerprint' => !empty($rdv->fingerprint_data),
                'has_face' => !empty($rdv->consent_facial_at)
            ];
        });

        return response()->json([
            'employees' => $employees,
            'patients' => $patients
        ]);
    }

    /**
     * Vérification et validation par empreinte digitale (ou capteur biométrique).
     */
    public function biometricVerify(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|in:EMPLOYE,PATIENT',
            'id' => 'required|integer'
        ]);

        try {
            if ($request->category === 'EMPLOYE') {
                $employee = Employe::with('department')->find($request->id);
                if (!$employee) {
                    return response()->json(['status' => 'error', 'message' => 'Employé introuvable.'], 404);
                }

                // Déterminer dynamiquement le type (ENTREE/SORTIE)
                $lastPointage = Pointage::where('employee_id', $employee->id)
                    ->whereDate('timestamp', Carbon::today())
                    ->orderBy('timestamp', 'desc')
                    ->first();

                $type = ($lastPointage && $lastPointage->type === 'ENTREE') ? 'SORTIE' : 'ENTREE';

                $status = 'Present';
                if ($type === 'ENTREE' && date('H:i') > '08:15') {
                    $status = 'Retard';
                }

                $pointage = Pointage::create([
                    'employee_id' => $employee->id,
                    'type' => $type,
                    'status' => $status,
                    'source' => 'EMPREINTE',
                    'timestamp' => now(),
                    'confidence_score' => 0.99,
                    'anomaly_detected' => false
                ]);

                return response()->json([
                    'status' => 'success',
                    'category' => 'EMPLOYE',
                    'message' => 'Pointage employé enregistré via empreinte digitale !',
                    'data' => [
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'department' => $employee->department ? $employee->department->name : 'N/A',
                        'type' => $pointage->type,
                        'time' => $pointage->timestamp->format('H:i:s')
                    ]
                ]);
            } else {
                $rdv = RendezVous::find($request->id);
                if (!$rdv) {
                    return response()->json(['status' => 'error', 'message' => 'Rendez-vous introuvable.'], 404);
                }

                $rdv->update([
                    'statut' => 'Présent',
                    'pointage_at' => now()
                ]);

                return response()->json([
                    'status' => 'success',
                    'category' => 'PATIENT',
                    'message' => 'Arrivée du patient enregistrée via empreinte digitale !',
                    'data' => [
                        'name' => $rdv->nom_patient,
                        'service' => $rdv->service ?? 'Consultation',
                        'medecin' => $rdv->medecin ?? 'Médecin traitant',
                        'time' => Carbon::now()->format('H:i:s')
                    ]
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('KioskController biometricVerify error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la vérification biométrique : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Enregistre une empreinte digitale pour un employé ou un patient.
     */
    public function registerFingerprint(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|in:EMPLOYE,PATIENT',
            'id' => 'required|integer',
            'fingerprint_data' => 'nullable|string'
        ]);

        try {
            $data = $request->fingerprint_data ?? 'BIOMETRIC_TEMPLATE_' . uniqid();

            if ($request->category === 'EMPLOYE') {
                $emp = Employe::find($request->id);
                if (!$emp) return response()->json(['status' => 'error', 'message' => 'Employé introuvable.'], 404);

                $emp->update([
                    'fingerprint_data' => $data,
                    'fingerprint_registered_at' => now()
                ]);
            } else {
                $rdv = RendezVous::find($request->id);
                if (!$rdv) return response()->json(['status' => 'error', 'message' => 'Patient/RDV introuvable.'], 404);

                $rdv->update([
                    'fingerprint_data' => $data,
                    'fingerprint_registered_at' => now()
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Empreinte digitale enregistrée avec succès !'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Affiche la vue de la borne (Interface Web).
     */
    public function showView()
    {
        return view('kiosk');
    }
}