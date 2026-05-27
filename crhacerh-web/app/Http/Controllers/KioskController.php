<?php

namespace App\Http\Controllers;

use App\Models\Pointage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class KioskController extends Controller
{
    /**
     * Récupère les pointages du jour pour l'affichage public.
     */
    public function getRecentPointages(): JsonResponse
    {
        $today = Carbon::today();

        $pointages = Pointage::with(['employee:id,first_name,last_name,department_id', 'employee.department:id,name'])
            ->whereDate('timestamp', $today)
            ->orderBy('timestamp', 'desc')
            ->take(6) // Limité à 6 pour correspondre à la taille du QR Code sans défilement
            ->get()
            ->map(function ($pointage) {
                return [
                    'id' => $pointage->id,
                    'first_name' => $pointage->employee->first_name,
                    'last_name' => substr($pointage->employee->last_name, 0, 1) . '.', // "Dupont" devient "D." pour la confidentialité
                    'department' => $pointage->employee->department ? $pointage->employee->department->name : 'N/A',
                    'type' => $pointage->type,
                    'time' => Carbon::parse($pointage->timestamp)->format('H:i:s'),
                    'source' => $pointage->source
                ];
            });

        return response()->json($pointages);
    }

    /**
     * Affiche la vue de la borne (Interface Web).
     */
    public function showView()
    {
        return view('kiosk');
    }
}