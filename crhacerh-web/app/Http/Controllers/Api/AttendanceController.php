<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Vérification du matricule de l'employé
     */
    public function checkMatricule(Request $request)
    {
        // 1. Validation du format reçu
        $request->validate([
            'matricule' => 'required|string',
        ]);

        try {
            // Nettoyage : on passe en minuscules au cas où l'employé aurait écrit en MAJUSCULES
            $matriculeSaisi = strtolower(trim($request->matricule));

            // 2. Recherche dans la table 'employés'
            $employee = DB::table('employés')
                ->where(DB::raw('LOWER(matricule)'), $matriculeSaisi)
                ->whereNull('deleted_at') // Ignore les employés supprimés (SoftDeletes)
                ->first();

            // 3. Si aucun employé ne correspond
            if (!$employee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Matricule introuvable. Vérifiez le format (chr-20xx-xxxx).'
                ], 404);
            }

            // 4. Si trouvé, on renvoie une réponse propre à Flutter
            return response()->json([
                'status' => 'success',
                'message' => 'Matricule valide.',
                'employee' => [
                    'id' => $employee->id,
                    'matricule' => $employee->matricule,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'role' => $employee->role
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur serveur lors de la vérification.'
            ], 500);
        }
    }

    /**
     * Récupère les données du tableau de bord pour l'application mobile
     */
    /**
     * Récupère les données du tableau de bord pour l'application mobile
     */
    public function getDashboardData(Request $request, $employeeId = null)
    {
        try {
            $idFinal = $employeeId ?? $request->user()?->id ?? $request->input('employee_id');

            if (!$idFinal) {
                return response()->json(['status' => 'error', 'message' => 'Identifiant manquant.'], 400);
            }

            // 1. Statistiques Globales (Toutes périodes confondues pour les graphiques)
            $pointagesStats = DB::table('pointages')->where('employee_id', $idFinal)->get();
            $totalMouvements = $pointagesStats->count();

            if ($totalMouvements === 0) {
                $presencePercent = 0; $retardPercent = 0; $absencePercent = 0;
            } else {
                $totalPresents = $pointagesStats->where('status', 'Present')->count();
                $totalRetards = $pointagesStats->where('status', 'Retard')->count();
                $totalAbsents = $pointagesStats->where('status', 'Absent')->count();

                // Somme des jours d'activité réels pour ramener le total sur un pourcentage de base
                $denominateur = $totalPresents + $totalRetards + $totalAbsents;
                
                if ($denominateur > 0) {
                    $presencePercent = round(($totalPresents / $denominateur) * 100);
                    $retardPercent = round(($totalRetards / $denominateur) * 100);
                    // S'assurer que la somme fait exactement 100%
                    $absencePercent = 100 - $presencePercent - $retardPercent;
                    if ($absencePercent < 0) {
                        $absencePercent = 0;
                    }
                } else {
                    $presencePercent = 0;
                    $retardPercent = 0;
                    $absencePercent = 0;
                }
            }

            // 2. FILTRE UNIQUEMENT LA JOURNÉE D'AUJOURD'HUI POUR LE TABLEAU FLUTTER
            $historiqueAujourdHui = DB::table('pointages')
                ->where('employee_id', $idFinal)
                ->whereDate('timestamp', \Carbon\Carbon::today()) // 👈 Réinitialisation automatique chaque jour
                ->orderBy('timestamp', 'desc')
                ->get()
                ->map(function($p) {
                    return [
                        'date' => date('d/m/Y', strtotime($p->timestamp)),
                        'time' => date('H:i', strtotime($p->timestamp)),
                        'status' => $p->status, // 'PRESENT', 'RETARD', ou 'ABSENT'
                        'is_late' => ($p->status === 'Retard'),
                    ];
                })->values()->toArray();

            // 3. TOUT L'HISTORIQUE POUR LES RAPPORTS SUR LE MOBILE
            $tousLesPointages = DB::table('pointages')
                ->where('employee_id', $idFinal)
                ->orderBy('timestamp', 'desc')
                ->get()
                ->map(function($p) {
                    return [
                        'date' => date('Y-m-d', strtotime($p->timestamp)), // Format requis pour le mobile
                        'time' => date('H:i', strtotime($p->timestamp)),
                        'status' => $p->status,
                        'is_late' => ($p->status === 'Retard'),
                    ];
                })->values()->toArray();

            return response()->json([
                'status' => 'success',
                'history' => $historiqueAujourdHui, // Envoie seulement l'historique du jour
                'all_history' => $tousLesPointages, // Envoie tout l'historique pour générer les rapports
                'stats' => [
                    'presence' => (int)$presencePercent,
                    'retard' => (int)$retardPercent,
                    'absence' => (int)$absencePercent,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
