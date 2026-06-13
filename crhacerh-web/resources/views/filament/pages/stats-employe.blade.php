<x-filament-panels::page>
    @php
        $pointages = $this->employe->pointages;
        // Total absences (en utilisant la relation ou en filtrant les pointages)
        $absencesCount = $this->employe->absences()->count() ?? 0;
        
        $entrees = $pointages->where('type', 'ENTREE');
        $retardsCount = $entrees->where('status', 'Retard')->count();
        $aLHeureCount = $entrees->where('status', 'Present')->count();
        
        $congesCount = $this->employe->conges ? $this->employe->conges->whereIn('statut', ['approuvé', 'en_cours'])->count() : 0;
        
        $total = $absencesCount + $retardsCount + $aLHeureCount;
        $taux = $total > 0 ? round((($aLHeureCount + $retardsCount) / $total) * 100) : 0;

        $recentPointages = $pointages->sortByDesc('created_at')->take(5);
    @endphp

    <style>
        .r-card-stat {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(156, 163, 175, 0.2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .r-card-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .dark .r-card-stat {
            background: rgba(31, 41, 55, 0.6);
            border: 1px solid rgba(75, 85, 99, 0.4);
        }
    </style>

    <div class="space-y-8">
        <!-- Header Profil Employé -->
        <div class="flex items-center gap-6 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                {{ substr($this->employe->first_name, 0, 1) }}{{ substr($this->employe->last_name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $this->employe->first_name }} {{ $this->employe->last_name }}
                </h1>
                <p class="text-md text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2">
                    <x-heroicon-o-identification class="w-5 h-5" />
                    Matricule: {{ $this->employe->matricule }}
                    <span class="mx-2">•</span>
                    <x-heroicon-o-briefcase class="w-5 h-5" />
                    {{ $this->employe->department->name ?? 'Aucun département' }}
                </p>
            </div>
        </div>

        <!-- Taux d'assiduité global -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-end mb-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Taux d'assiduité global</h2>
                <span class="text-2xl font-bold {{ $taux >= 80 ? 'text-green-500' : ($taux >= 50 ? 'text-orange-500' : 'text-red-500') }}">
                    {{ $taux }}%
                </span>
            </div>
            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $taux >= 80 ? 'bg-green-500' : ($taux >= 50 ? 'bg-orange-500' : 'bg-red-500') }} transition-all duration-1000 ease-out" 
                     style="width: {{ $taux }}%"></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Basé sur {{ $total }} jours enregistrés.</p>
        </div>

        <!-- Grid des Statistiques -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- À l'heure -->
            <div class="r-card-stat p-6 flex flex-col items-center justify-center text-center bg-white dark:bg-gray-800">
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                    <x-heroicon-o-check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">À l'heure</h3>
                <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $aLHeureCount }}</p>
            </div>

            <!-- Retards -->
            <div class="r-card-stat p-6 flex flex-col items-center justify-center text-center bg-white dark:bg-gray-800">
                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-full mb-4">
                    <x-heroicon-o-clock class="w-8 h-8 text-orange-600 dark:text-orange-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Retards</h3>
                <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $retardsCount }}</p>
            </div>

            <!-- Absences -->
            <div class="r-card-stat p-6 flex flex-col items-center justify-center text-center bg-white dark:bg-gray-800">
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                    <x-heroicon-o-x-circle class="w-8 h-8 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Absences</h3>
                <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $absencesCount }}</p>
            </div>

            <!-- Congés -->
            <div class="r-card-stat p-6 flex flex-col items-center justify-center text-center bg-white dark:bg-gray-800">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full mb-4">
                    <x-heroicon-o-calendar-days class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Congés Actifs</h3>
                <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $congesCount }}</p>
            </div>

        </div>

        <!-- Historique Récent -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Derniers pointages (5)</h2>
            </div>
            
            @if($recentPointages->isEmpty())
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    Aucun pointage enregistré pour l'instant.
                </div>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($recentPointages as $p)
                        <li class="p-6 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <div class="flex items-center gap-4">
                                <div class="p-2 rounded-full {{ $p->type === 'ENTREE' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' }}">
                                    @if($p->type === 'ENTREE')
                                        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                                    @else
                                        <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" />
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $p->type === 'ENTREE' ? 'Arrivée' : 'Départ' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($p->created_at)->translatedFormat('l d F Y à H:i') }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($p->type === 'ENTREE')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $p->status === 'Present' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                      ($p->status === 'Retard' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' : 
                                      'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                                    {{ $p->status }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-filament-panels::page>