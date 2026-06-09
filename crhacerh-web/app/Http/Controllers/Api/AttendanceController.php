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
                return response()->json([
                    'status' => 'error',
                    'message' => 'Identifiant de l\'employé manquant.'
                ], 400);
            }

            // 1. Recherche de l'employé
            $employe = DB::table('employés')
                ->where('id', $idFinal)
                ->whereNull('deleted_at')
                ->first();

            if (!$employe) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employé introuvable.'
                ], 404);
            }

            // 2. Récupérer l'historique complet pour les statistiques (uniquement les ENTREEs)
            $pointagesEntree = DB::table('pointages')
                ->where('employee_id', $idFinal)
                ->where('type', 'ENTREE')
                ->get();

            // 3. Récupérer l'historique récent (les 5 derniers pointages généraux) pour le bas de l'écran
            $historyData = DB::table('pointages')
                ->where('employee_id', $idFinal)
                ->orderBy('timestamp', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($p) {
                    return [
                        'date' => date('d/m/Y', strtotime($p->timestamp)),
                        'time' => date('H:i', strtotime($p->timestamp)),
                        'status' => $p->status ?? $p->type,
                        'is_late' => ($p->status === 'RETARD'),
                    ];
                })->values()->toArray();

            // 4. Déterminer la période à analyser (les jours ouvrés du mois jusqu'à aujourd'hui)
            $debutMois = new \DateTime('first day of this month');
            $aujourdHui = new \DateTime('now');

            // SI L'EMPLOYÉ N'A ENCORE JAMAIS POINTÉ CE MOIS-CI
            if ($pointagesEntree->isEmpty()) {
                $presencePercent = 0;
                $retardPercent = 0;
                $absencePercent = 0; // 👈 On force à 0% au lieu de mettre 100% d'absence
            } else {
                $interval = new \DateInterval('P1D');
                $periode = new \DatePeriod($debutMois, $interval, $aujourdHui->modify('+1 day'));

                $joursOuvresObjectif = 0;
                $joursPresents = 0;
                $joursAbsents = 0;
                $totalRetards = 0;

                // Extrait toutes les dates de pointage uniques au format 'Y-m-d'
                $datesPointes = $pointagesEntree->map(function ($p) {
                    return date('Y-m-d', strtotime($p->timestamp));
                })->toArray();

                // 5. Parcourir le calendrier jour par jour jusqu'à aujourd'hui
                foreach ($periode as $date) {
                    $jourSemaine = $date->format('N'); // 1 (lundi) à 7 (dimanche)

                    // On ne compte que les jours ouvrés (du lundi au vendredi)
                    if ($jourSemaine <= 5) {
                        $joursOuvresObjectif++;
                        $dateStr = $date->format('Y-m-d');

                        // L'employé a-t-il pointé ce jour-là ?
                        if (in_array($dateStr, $datesPointes)) {
                            $joursPresents++;

                            // Vérification du retard sur le statut du pointage de ce jour
                            $pointageDuJour = $pointagesEntree->first(function ($p) use ($dateStr) {
                                return date('Y-m-d', strtotime($p->timestamp)) === $dateStr;
                            });

                            if ($pointageDuJour && $pointageDuJour->status === 'RETARD') {
                                $totalRetards++;
                            }
                        } else {
                            // Si le jour analysé est strictement inférieur à aujourd'hui, c'est une absence
                            // Cela évite de marquer absent pour "aujourd'hui" si l'employé n'a pas encore scanné ce matin
                            if ($date->format('Y-m-d') < date('Y-m-d')) {
                                $joursAbsents++;
                            }
                        }
                    }
                }

                // 6. Calcul des pourcentages globaux
                if ($joursOuvresObjectif === 0) {
                    $presencePercent = 0;
                    $absencePercent = 0;
                    $retardPercent = 0;
                } else {
                    $presencePercent = round(($joursPresents / $joursOuvresObjectif) * 100);
                    $absencePercent = round(($joursAbsents / $joursOuvresObjectif) * 100);
                    $retardPercent = $joursPresents > 0 ? round(($totalRetards / $joursPresents) * 100) : 0;
                }
            }
            // 7. Structure de réponse pour Flutter
            return response()->json([
                'status' => 'success',
                'history' => $historyData,
                'stats' => [
                    'presence' => (int)$presencePercent,
                    'retard' => (int)$retardPercent,
                    'absence' => (int)$absencePercent,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du calcul des statistiques : ' . $e->getMessage()
            ], 500);
        }
    }
}
