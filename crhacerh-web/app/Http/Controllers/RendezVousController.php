<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RendezVousController extends Controller
{
    /**
     * Affiche la liste des rendez-vous avec filtres, recherche et pagination sur-mesure (20 par page).
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();

        // Statistiques globales pour les cartes supérieures
        $statsToday = [
            'total'     => RendezVous::whereDate('date_rendez_vous', $today)->count(),
            'confirme'  => RendezVous::whereDate('date_rendez_vous', $today)->where('statut', 'Confirmé')->count(),
            'realise'   => RendezVous::whereDate('date_rendez_vous', $today)->where('statut', 'Réalisé')->count(),
            'annule'    => RendezVous::whereDate('date_rendez_vous', $today)->whereIn('statut', ['Annulé', 'Reporté'])->count(),
        ];

        // Requête principale
        $query = RendezVous::with('patient');

        // Filtre par recherche textuelle (Patient, Téléphone, Médecin, Service)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($pq) use ($search) {
                    $pq->where('nom_complet', 'ILIKE', "%{$search}%")
                       ->orWhere('numero_dossier', 'ILIKE', "%{$search}%");
                })
                ->orWhere('telephone_patient', 'ILIKE', "%{$search}%")
                ->orWhere('email_patient', 'ILIKE', "%{$search}%")
                ->orWhere('medecin', 'ILIKE', "%{$search}%")
                ->orWhere('service', 'ILIKE', "%{$search}%")
                ->orWhere('motif', 'ILIKE', "%{$search}%");
            });
        }

        // Filtre par Statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->get('statut'));
        }

        // Filtre par Service
        if ($request->filled('service')) {
            $query->where('service', $request->get('service'));
        }

        // Filtre par Période (Aujourd'hui, À venir, Passés)
        if ($request->filled('periode')) {
            switch ($request->get('periode')) {
                case 'today':
                    $query->whereDate('date_rendez_vous', $today);
                    break;
                case 'upcoming':
                    $query->whereDate('date_rendez_vous', '>=', $today);
                    break;
                case 'past':
                    $query->whereDate('date_rendez_vous', '<', $today);
                    break;
            }
        }

        // Tri par date décroissante
        $rendezvous = $query->orderBy('date_rendez_vous', 'desc')
                            ->paginate(20)
                            ->withQueryString();

        // Liste des services disponibles pour les filtres
        $services = [
            'Chirurgie Endoscopique',
            'FIV / Reproduction Humaine',
            'Consultation Gynécologique',
            'Échographie & Imagerie',
            'Suivi de Grossesse',
            'Consultation d’Infertilité',
        ];

        // Liste des médecins du CHRACERH
        $medecins = [
            'Dr. Jean-Paul MBALLA',
            'Dr. Marie-Claire ESSOMBA',
            'Dr. Etienne NKOULOU',
            'Dr. Sophie ATANGANA',
            'Dr. Francis ETOUNDI',
        ];

        return view('rendezvous.index', [
            'rendezvous' => $rendezvous,
            'statsToday' => $statsToday,
            'services'   => $services,
            'medecins'   => $medecins,
        ]);
    }

    /**
     * Enregistre un nouveau rendez-vous.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_patient'       => 'required|string|max:150',
            'telephone_patient' => 'nullable|string|max:30',
            'email_patient'     => 'nullable|email|max:150',
            'service'           => 'required|string|max:100',
            'motif'             => 'required|string|max:255',
            'date_rendez_vous'  => 'required|date',
            'heure_rendez_vous' => 'required|string',
            'medecin'           => 'nullable|string|max:150',
            'notes'             => 'nullable|string',
        ]);

        // Créer ou récupérer le patient
        $patient = Patient::firstOrCreate(
            ['nom_complet' => trim($validated['nom_patient'])],
            [
                'numero_dossier' => 'DOS-' . rand(10000, 99999),
                'telephone'      => $validated['telephone_patient'] ?? null,
            ]
        );

        $dateTimeStr = $validated['date_rendez_vous'] . ' ' . $validated['heure_rendez_vous'];
        $dateStart = Carbon::parse($dateTimeStr);
        $dateEnd   = (clone $dateStart)->addMinutes(30);

        RendezVous::create([
            'patient_id'        => $patient->id,
            'telephone_patient' => $validated['telephone_patient'] ?? $patient->telephone,
            'email_patient'     => $validated['email_patient'] ?? null,
            'service'           => $validated['service'],
            'motif'             => $validated['motif'],
            'date_rendez_vous'  => $dateStart,
            'fin_rendez_vous'   => $dateEnd,
            'medecin'           => $validated['medecin'] ?? 'Généraliste',
            'statut'            => 'Planifié',
            'notes'             => $validated['notes'] ?? null,
        ]);

        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous créé avec succès !');
    }

    /**
     * Met à jour le statut d'un rendez-vous (AJAX ou Form).
     */
    public function updateStatus(Request $request, $id)
    {
        $rdv = RendezVous::findOrFail($id);
        
        $request->validate([
            'statut' => 'required|in:Planifié,Confirmé,Réalisé,Annulé,Reporté',
        ]);

        $rdv->update(['statut' => $request->statut]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'statut' => $rdv->statut]);
        }

        return redirect()->back()->with('success', 'Statut du rendez-vous mis à jour.');
    }
}
