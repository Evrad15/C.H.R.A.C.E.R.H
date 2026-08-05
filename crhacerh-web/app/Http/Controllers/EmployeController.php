<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Department;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeController extends Controller
{
    /**
     * Affiche la liste des employés avec pagination (20 par page), ordre alphabétique par NOM Prénom,
     * et recherche textuelle/filtre.
     */
    public function index(Request $request)
    {
        $departments = Department::orderBy('name')->get();

        $query = Employe::with('department');

        // Filtre par recherche textuelle (Nom ou Prénom)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'ILIKE', "%{$search}%")
                  ->orWhere('first_name', 'ILIKE', "%{$search}%");
            });
        }

        // Filtre par département
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }

        // Tri alphabétique strict : NOM d'abord, puis Prénom
        $employes = $query->orderBy('last_name', 'asc')
                          ->orderBy('first_name', 'asc')
                          ->paginate(20)
                          ->withQueryString();

        // Récupérer les IDs des employés de la page courante
        $currentPageEmployeeIds = $employes->pluck('id')->toArray();

        // Agrégation des compteurs d'assiduité depuis PostgreSQL pour les employés affichés
        $statsByEmployee = Pointage::whereIn('employee_id', $currentPageEmployeeIds)
            ->where('type', 'ENTREE')
            ->select('employee_id', 'status', DB::raw('count(*) as count'))
            ->groupBy('employee_id', 'status')
            ->get()
            ->groupBy('employee_id');

        // Formater les données de chaque employé
        $employesData = $employes->getCollection()->map(function ($emp) use ($statsByEmployee) {
            $empStats = $statsByEmployee->get($emp->id, collect());
            
            $absences = $empStats->firstWhere('status', 'Absent')->count ?? 0;
            $retards = $empStats->firstWhere('status', 'Retard')->count ?? 0;
            $alheure = $empStats->firstWhere('status', 'Present')->count ?? 0;

            // NOM en majuscules suivi du Prénom
            $formattedName = strtoupper($emp->last_name) . ' ' . $emp->first_name;
            $initials = strtoupper(substr($emp->last_name, 0, 1) . substr($emp->first_name, 0, 1));

            return (object) [
                'id' => $emp->id,
                'full_name' => $formattedName,
                'initials' => $initials,
                'last_name' => $emp->last_name,
                'first_name' => $emp->first_name,
                'department_name' => $emp->department->name ?? 'Général',
                'absences_count' => $absences,
                'retards_count' => $retards,
                'alheure_count' => $alheure,
                'role' => $emp->role ?? 'Employé',
            ];
        });

        // Remplacer la collection d'origine par notre collection formatée tout en gardant l'objet LengthAwarePaginator
        $employes->setCollection($employesData);

        return view('employes.index', [
            'employes' => $employes,
            'departments' => $departments,
        ]);
    }

    /**
     * Retourne les statistiques détaillées d'un employé en JSON (pour le panneau latéral).
     */
    public function show($id)
    {
        $emp = Employe::with('department')->findOrFail($id);

        // Compteurs globaux d'assiduité
        $absences = Pointage::where('employee_id', $emp->id)->where('type', 'ENTREE')->where('status', 'Absent')->count();
        $retards = Pointage::where('employee_id', $emp->id)->where('type', 'ENTREE')->where('status', 'Retard')->count();
        $presents = Pointage::where('employee_id', $emp->id)->where('type', 'ENTREE')->where('status', 'Present')->count();
        $totalPointages = $absences + $retards + $presents;

        // Taux de présence (%)
        $tauxPresence = $totalPointages > 0 ? round(($presents / $totalPointages) * 100, 1) : 0;
        $tauxRetard = $totalPointages > 0 ? round(($retards / $totalPointages) * 100, 1) : 0;
        $tauxAbsence = $totalPointages > 0 ? round(($absences / $totalPointages) * 100, 1) : 0;

        // 5 derniers pointages
        $lastPointages = Pointage::where('employee_id', $emp->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($p) {
                return [
                    'date' => \Carbon\Carbon::parse($p->created_at)->format('d/m/Y'),
                    'heure' => \Carbon\Carbon::parse($p->timestamp)->format('H:i'),
                    'type' => $p->type,
                    'status' => $p->status,
                ];
            });

        // Assiduité hebdomadaire (7 derniers jours avec données)
        $weeklyStats = Pointage::where('employee_id', $emp->id)
            ->where('type', 'ENTREE')
            ->selectRaw("DATE(created_at) as jour, status")
            ->orderBy('jour', 'desc')
            ->take(28)
            ->get()
            ->groupBy('jour')
            ->take(7)
            ->map(function ($group, $jour) {
                $status = $group->first()->status ?? 'Absent';
                return [
                    'jour' => \Carbon\Carbon::parse($jour)->locale('fr')->isoFormat('ddd DD/MM'),
                    'status' => $status,
                ];
            })
            ->values();

        return response()->json([
            'id' => $emp->id,
            'full_name' => strtoupper($emp->last_name) . ' ' . $emp->first_name,
            'initials' => strtoupper(substr($emp->last_name, 0, 1) . substr($emp->first_name, 0, 1)),
            'matricule' => $emp->matricule ?? '—',
            'department' => $emp->department->name ?? 'Général',
            'role' => $emp->role ?? 'Employé',
            'email' => $emp->email ?? '—',
            'phone' => $emp->phone ?? '—',
            'presents' => $presents,
            'retards' => $retards,
            'absences' => $absences,
            'total_pointages' => $totalPointages,
            'taux_presence' => $tauxPresence,
            'taux_retard' => $tauxRetard,
            'taux_absence' => $tauxAbsence,
            'last_pointages' => $lastPointages,
            'weekly_stats' => $weeklyStats,
        ]);
    }

    /**
     * Enregistre un nouvel employé avec ses identifiants et options d'enrôlement biométrique.
     */
    public function store(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employés,email',
            'phone' => 'nullable|string|max:30',
            'department_id' => 'nullable|exists:departments,id',
            'role' => 'nullable|string|max:50',
            'register_fingerprint' => 'nullable',
            'register_face' => 'nullable',
        ]);

        $emp = Employe::create([
            'last_name' => strtoupper(trim($request->input('last_name'))),
            'first_name' => trim($request->input('first_name')),
            'email' => strtolower(trim($request->input('email'))),
            'phone' => $request->input('phone'),
            'department_id' => $request->input('department_id'),
            'role' => $request->input('role', 'Employés'),
            'password' => 'password123',
            'fingerprint_registered_at' => $request->filled('register_fingerprint') ? now() : null,
            'consent_facial_at' => $request->filled('register_face') ? now() : null,
        ]);

        return redirect()->route('employes.index')->with('success', "Employé {$emp->full_name} créé avec succès !");
    }
}
