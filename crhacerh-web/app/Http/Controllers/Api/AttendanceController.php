<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
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
}