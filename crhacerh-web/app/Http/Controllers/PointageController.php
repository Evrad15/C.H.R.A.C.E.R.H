<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PointageController extends Controller
{
    /**
     * Enregistre un pointage via le scan du QR Code mobile.
     */
    public function scan(Request $request): JsonResponse
    {
        // 1. Validation stricte des données reçues du mobile
        $validated = $request->validate([
            'employee_id'  => 'required|exists:employés,id',
            'type'         => 'required|in:ENTREE,SORTIE',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
        ]);

        // 1.1 Vérification de la cohérence (Optionnel pour MVP mais recommandé)
        $lastPointage = Pointage::where('employee_id', $validated['employee_id'])
            ->orderBy('timestamp', 'desc')
            ->first();

        if ($lastPointage && $lastPointage->type === $validated['type']) {
            return response()->json([
                'status' => 'error',
                'message' => "Vous avez déjà enregistré une " . strtolower($validated['type']) . "."
            ], 400);
        }

        // 2. Création du pointage dans PostgreSQL
        $pointage = Pointage::create([
            'employee_id'      => $validated['employee_id'],
            'type'             => $validated['type'],
            'source'           => 'QR', // Provient du scan QR Code
            'timestamp'        => now(), // Heure actuelle exacte
            'latitude'         => $validated['latitude'] ?? null,
            'longitude'        => $validated['longitude'] ?? null,
            'confidence_score' => 1.00,  // Un scan de QR Code valide est sûr à 100%
            'anomaly_detected' => false  // Modulo IA plus tard
        ]);

        // 3. Récupérer l'employé pour personnaliser la réponse
        $employee = Employe::find($validated['employee_id']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pointage enregistré avec succès ! 👋',
            'data'    => [
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'type'     => $pointage->type,
                'time'     => $pointage->timestamp->format('H:i:s'),
            ]
        ], 201);
    }
}