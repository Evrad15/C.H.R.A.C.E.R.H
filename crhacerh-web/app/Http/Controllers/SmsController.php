<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    /**
     * Affiche le journal et l'historique des SMS envoyés automatiquement.
     */
    public function index(Request $request)
    {
        $query = SmsLog::with('rendezVous')->orderBy('created_at', 'desc');

        // ── Filtre par Période ──────────────────────────────
        $periode = $request->get('periode', 'today');
        match ($periode) {
            'today'      => $query->whereDate('created_at', Carbon::today()),
            'yesterday'  => $query->whereDate('created_at', Carbon::yesterday()),
            'week'       => $query->whereBetween('created_at', [
                                Carbon::now()->startOfWeek(),
                                Carbon::now()->endOfWeek(),
                            ]),
            'month'      => $query->whereMonth('created_at', Carbon::now()->month)
                                  ->whereYear('created_at', Carbon::now()->year),
            default      => null, // 'all'
        };

        // ── Filtre par Type de SMS ──────────────────────────
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // ── Filtre par Statut ───────────────────────────────
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // ── Recherche textuelle ─────────────────────────────
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // ── Statistiques Globales du Journal ─────────────────
        $stats = [
            'total_today'    => SmsLog::whereDate('created_at', Carbon::today())->count(),
            'confirmations'  => SmsLog::where('type', 'CONFIRMATION')->count(),
            'rappels_j1'     => SmsLog::where('type', 'RAPPEL_J1')->count(),
            'echecs'         => SmsLog::where('status', 'FAILED')->count(),
        ];

        return view('sms.index', compact('logs', 'stats'));
    }

    /**
     * Réessaie d'envoyer un SMS en cas d'échec opérateur ou réseau.
     * Route : POST /sms/{id}/retry
     */
    public function retry($id): JsonResponse
    {
        $log = SmsLog::findOrFail($id);

        $updatedLog = SmsService::resend($log);

        if ($updatedLog->status === 'SENT' || $updatedLog->status === 'SIMULATED') {
            return response()->json([
                'status'  => 'success',
                'message' => "SMS renvoyé avec succès au {$updatedLog->phone} !",
                'log'     => $updatedLog,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => "Échec de renvoi : " . ($updatedLog->response_details ?? 'Erreur opérateur'),
            'log'     => $updatedLog,
        ], 422);
    }
}
