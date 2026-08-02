<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Employe;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CongeController extends Controller
{
    /**
     * Affiche la liste des congés et permissions avec filtres, recherche et pagination sur-mesure (20 par page).
     */
    public function index(Request $request)
    {
        // Statistiques globales pour les cartes supérieures
        $stats = [
            'total'      => Conge::count(),
            'en_attente' => Conge::where('statut', 'En attente')->count(),
            'approuve'   => Conge::where('statut', 'Approuvé')->count(),
            'refuse'     => Conge::where('statut', 'Refusé')->count(),
        ];

        // Requête principale
        $query = Conge::with('employe.department');

        // Filtre par recherche textuelle (Employé, Motif, Type)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('employe', function ($eq) use ($search) {
                    $eq->where('last_name', 'ILIKE', "%{$search}%")
                       ->orWhere('first_name', 'ILIKE', "%{$search}%");
                })
                ->orWhere('type', 'ILIKE', "%{$search}%")
                ->orWhere('motif', 'ILIKE', "%{$search}%");
            });
        }

        // Filtre par Statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->get('statut'));
        }

        // Filtre par Type de congé
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Tri par date de création décroissante
        $conges = $query->orderBy('created_at', 'desc')
                        ->paginate(20)
                        ->withQueryString();

        // Liste des employés pour le formulaire de création
        $employesList = Employe::orderBy('last_name')->get();

        // Types de congés
        $typesConges = [
            'Congé annuel',
            'Permission exceptionnelle',
            'Congé maladie',
            'Congé maternité',
            'Congé paternité',
            'Formation',
        ];

        return view('conges.index', [
            'conges'       => $conges,
            'stats'        => $stats,
            'employesList' => $employesList,
            'typesConges'  => $typesConges,
        ]);
    }

    /**
     * Enregistre une nouvelle demande de congé ou permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employés,id',
            'type'       => 'required|string|max:100',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'motif'      => 'required|string|max:255',
        ]);

        Conge::create([
            'employe_id' => $validated['employe_id'],
            'type'       => $validated['type'],
            'date_debut' => $validated['date_debut'],
            'date_fin'   => $validated['date_fin'],
            'motif'      => $validated['motif'],
            'statut'     => 'En attente',
        ]);

        return redirect()->route('conges.index')->with('success', 'Demande de congé enregistrée avec succès !');
    }

    /**
     * Met à jour le statut d'une demande (Approuvé / Refusé / En attente).
     */
    public function updateStatus(Request $request, $id)
    {
        $conge = Conge::findOrFail($id);

        $request->validate([
            'statut'          => 'required|in:En attente,Approuvé,Refusé',
            'commentaire_rh' => 'nullable|string',
        ]);

        $conge->update([
            'statut'          => $request->statut,
            'commentaire_rh' => $request->commentaire_rh ?? $conge->commentaire_rh,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'statut' => $conge->statut]);
        }

        return redirect()->back()->with('success', 'Statut de la demande mis à jour.');
    }
}
