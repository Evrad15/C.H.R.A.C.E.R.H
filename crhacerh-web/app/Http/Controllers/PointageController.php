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
        $validated = $request->validate([
            'employee_id'  => 'required|exists:employés,id',
            'type'         => 'required|in:ENTREE,SORTIE',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
        ]);

        $lastPointage = Pointage::where('employee_id', $validated['employee_id'])
            ->orderBy('timestamp', 'desc')
            ->first();

        if ($lastPointage && $lastPointage->type === $validated['type']) {
            return response()->json([
                'status' => 'error',
                'message' => "Vous avez déjà enregistré une " . strtolower($validated['type']) . "."
            ], 400);
        }

        if (strtoupper($validated['type']) === 'SORTIE') {
            $hasEntreeToday = Pointage::where('employee_id', $validated['employee_id'])
                ->where('type', 'ENTREE')
                ->whereDate('timestamp', now()->toDateString())
                ->exists();

            if (!$hasEntreeToday) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Op�ration impossible : Vous devez pointer � l'ENTREE avant de pouvoir enregistrer une SORTIE aujourd'hui."
                ], 400);
            }
        }

        // --- CALCUL DU STATUT ---
        $status = 'Present';
        if (strtoupper($validated['type']) === 'ENTREE') {
            // Si l'employé arrive après 08:15, le statut passe à 'RETARD'
            if (date('H:i') > '08:15') {
                $status = 'Retard';
            }
        } else {
            $status = 'SORTIE';
        }

        $pointage = Pointage::create([
            'employee_id'      => $validated['employee_id'],
            'type'             => $validated['type'],
            'status'           => $status, // Enregistrement de ta nouvelle colonne !
            'source'           => 'QR',
            'timestamp'        => now(),
            'latitude'         => $validated['latitude'] ?? null,
            'longitude'        => $validated['longitude'] ?? null,
            'confidence_score' => 1.00,
            'anomaly_detected' => false
        ]);

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

