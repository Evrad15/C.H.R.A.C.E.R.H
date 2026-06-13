<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conge;
use App\Models\Employe;
use Illuminate\Http\Request;

class CongeApiController extends Controller
{
    /**
     * Récupère l'historique des congés et permissions pour un employé.
     */
    public function index($employeId)
    {
        $employe = Employe::find($employeId);
        
        if (!$employe) {
            return response()->json(['error' => 'Employé non trouvé'], 404);
        }

        $conges = Conge::where('employe_id', $employeId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conges
        ]);
    }

    /**
     * Soumet une nouvelle demande de congé ou de permission.
     */
    public function store(Request $request, $employeId)
    {
        $employe = Employe::find($employeId);
        
        if (!$employe) {
            return response()->json(['error' => 'Employé non trouvé'], 404);
        }

        $validatedData = $request->validate([
            'type' => 'required|in:Congé,Permission,Maladie',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'required|string|max:1000',
        ]);

        $conge = Conge::create([
            'employe_id' => $employeId,
            'type' => $validatedData['type'],
            'date_debut' => $validatedData['date_debut'],
            'date_fin' => $validatedData['date_fin'],
            'motif' => $validatedData['motif'],
            'statut' => 'En attente',
        ]);

        // (Optionnel) Ici, on pourrait envoyer une notification aux RH (ex: Filament Notification)

        return response()->json([
            'success' => true,
            'message' => 'Demande soumise avec succès.',
            'data' => $conge
        ], 201);
    }
}
