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
        .stat-banner {
            background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%) !important;
            position: relative;
            overflow: hidden;
        }

        .stat-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -30%;
            width: 80%;
            height: 150%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .premium-stat-card {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            border-bottom: 4px solid currentColor;
        }

        .premium-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.15);
        }

        /* Timeline styles */
        .timeline-container::before {
            content: '';
            position: absolute;
            top: 24px;
            bottom: 24px;
            left: 27px;
            width: 2px;
            background: rgba(226, 232, 240, 0.8);
        }

        .dark .timeline-container::before {
            background: rgba(51, 65, 85, 0.5);
        }
    </style>

    <div class="space-y-6">
        <!-- Banner Profile Header -->
        <div class="stat-banner p-6 rounded-3xl shadow-xl border border-indigo-950/50 flex flex-col md:flex-row md:items-center justify-between gap-6" style="background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%) !important;">
            <div class="flex items-center gap-6">
                <!-- Glowing avatar initials -->
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center text-white text-3xl font-extrabold shadow-lg shadow-indigo-500/20 border border-white/10">
                    {{ substr($this->employe->first_name, 0, 1) }}{{ substr($this->employe->last_name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight" style="color: #ffffff !important;">
                        {{ $this->employe->first_name }} {{ $this->employe->last_name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm" style="color: #e0e7ff !important;">
                        <span class="flex items-center gap-1.5 bg-white/10 px-2.5 py-1 rounded-full border border-white/5" style="color: #e0e7ff !important;">
                            <x-heroicon-o-identification class="w-4 h-4" style="color: #c7d2fe !important;" />
                            Matricule: {{ $this->employe->matricule }}
                        </span>
                        <span class="flex items-center gap-1.5 bg-white/10 px-2.5 py-1 rounded-full border border-white/5" style="color: #e0e7ff !important;">
                            <x-heroicon-o-briefcase class="w-4 h-4" style="color: #c7d2fe !important;" />
                            {{ $this->employe->department->name ?? 'Aucun département' }}
                        </span>
                    </div>
                </div>
            </div>
            <!-- Back Action Button -->
            <div>
                <a href="/ressources-humaines/employes" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 rounded-2xl border border-white/10 text-sm font-semibold transition shadow-sm backdrop-blur-md"
                   style="color: #ffffff !important;">
                    <x-heroicon-o-arrow-left class="w-4 h-4" style="color: #ffffff !important;" />
                    Retour aux employés
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Taux d'assiduité global card -->
            <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-presentation-chart-line class="w-5 h-5 text-indigo-500" />
                            Assiduité Globale
                        </h2>
                        <span class="px-3 py-1.5 text-xs font-bold rounded-full {{ $taux >= 80 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : ($taux >= 50 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20') }}">
                            @if($taux >= 80)
                                Excellente assiduité
                            @elseif($taux >= 50)
                                Assiduité moyenne
                            @else
                                Assiduité critique
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center gap-4 my-4">
                        <span class="text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                            {{ $taux }}%
                        </span>
                        <div class="text-xs text-gray-500 dark:text-gray-400 leading-normal">
                            Pourcentage global calculé sur <span class="font-bold text-gray-900 dark:text-white">{{ $total }}</span> présences, retards et absences enregistrés.
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="w-full h-4 bg-gray-100 dark:bg-gray-700/50 rounded-full overflow-hidden p-0.5 border border-gray-200/50 dark:border-gray-700">
                        <div class="h-full rounded-full {{ $taux >= 80 ? 'bg-gradient-to-r from-emerald-500 to-teal-500 shadow-md shadow-emerald-500/20' : ($taux >= 50 ? 'bg-gradient-to-r from-amber-500 to-orange-500 shadow-md shadow-amber-500/20' : 'bg-gradient-to-r from-rose-500 to-red-500 shadow-md shadow-rose-500/20') }} transition-all duration-1000 ease-out" 
                             style="width: {{ $taux }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Mini Summary Card -->
            <div class="p-6 rounded-3xl shadow-lg border border-indigo-400/20 flex flex-col justify-between" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; color: #ffffff !important;">
                <div>
                    <h2 class="text-lg font-bold" style="color: #ffffff !important; opacity: 0.95;">Synthèse mensuelle</h2>
                    <p class="text-xs mt-1" style="color: #e0e7ff !important; opacity: 0.8;">Données calculées en temps réel</p>
                </div>
                
                <div class="my-4 space-y-3">
                    <div class="flex justify-between items-center text-sm border-b border-white/10 pb-2">
                        <span style="color: #e0e7ff !important; opacity: 0.85;">Jours ouvrés actifs</span>
                        <span class="font-bold" style="color: #ffffff !important;">{{ $total }} jours</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-white/10 pb-2">
                        <span style="color: #e0e7ff !important; opacity: 0.85;">Présent en clinique</span>
                        <span class="font-bold" style="color: #ffffff !important;">{{ $aLHeureCount + $retardsCount }} jours</span>
                    </div>
                    <div class="flex justify-between items-center text-sm pb-1">
                        <span style="color: #e0e7ff !important; opacity: 0.85;">Taux de retard</span>
                        <span class="font-bold" style="color: #ffffff !important;">{{ $total > 0 ? round(($retardsCount / $total) * 100) : 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Statistics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- À l'heure card -->
            <div class="premium-stat-card p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/50 hover:border-emerald-500/30 text-emerald-500">
                <div class="flex items-center justify-between">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full border border-emerald-500/20">Présences</span>
                </div>
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">À l'heure</h3>
                    <p class="text-4xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $aLHeureCount }}</p>
                </div>
            </div>

            <!-- Retards card -->
            <div class="premium-stat-card p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/50 hover:border-amber-500/30 text-amber-500">
                <div class="flex items-center justify-between">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/20 rounded-2xl">
                        <x-heroicon-o-clock class="w-8 h-8 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-full border border-amber-500/20">Alerte</span>
                </div>
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Retards</h3>
                    <p class="text-4xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $retardsCount }}</p>
                </div>
            </div>

            <!-- Absences card -->
            <div class="premium-stat-card p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/50 hover:border-rose-500/30 text-rose-500">
                <div class="flex items-center justify-between">
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/20 rounded-2xl">
                        <x-heroicon-o-x-circle class="w-8 h-8 text-rose-600 dark:text-rose-400" />
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-full border border-rose-500/20">Manqués</span>
                </div>
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Absences</h3>
                    <p class="text-4xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $absencesCount }}</p>
                </div>
            </div>

            <!-- Congés card -->
            <div class="premium-stat-card p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/50 hover:border-sky-500/30 text-sky-500">
                <div class="flex items-center justify-between">
                    <div class="p-3 bg-sky-50 dark:bg-sky-950/20 rounded-2xl">
                        <x-heroicon-o-calendar-days class="w-8 h-8 text-sky-600 dark:text-sky-400" />
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-sky-500/10 text-sky-600 dark:text-sky-400 rounded-full border border-sky-500/20">Validés</span>
                </div>
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Congés Actifs</h3>
                    <p class="text-4xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $congesCount }}</p>
                </div>
            </div>
        </div>

        <!-- Recent History Section -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/50 overflow-hidden relative">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="w-5 h-5 text-indigo-500" />
                    Historique Récent de Pointage
                </h2>
                <span class="text-xs font-medium bg-gray-50 dark:bg-gray-700 px-3 py-1.5 rounded-xl border border-gray-200/50 dark:border-gray-600/50 text-gray-500 dark:text-gray-400">
                    5 derniers enregistrements
                </span>
            </div>
            
            @if($recentPointages->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="inline-flex p-4 bg-gray-50 dark:bg-gray-700/30 rounded-full mb-3 text-gray-400">
                        <x-heroicon-o-folder-open class="w-8 h-8" />
                    </div>
                    <p class="text-sm font-semibold">Aucun pointage trouvé</p>
                    <p class="text-xs text-gray-400 mt-1">Cet employé n'a pas encore enregistré d'activité.</p>
                </div>
            @else
                <div class="p-6 relative timeline-container">
                    <div class="space-y-6 relative">
                        @foreach($recentPointages as $p)
                            <div class="flex items-start gap-4 pl-4 relative group">
                                <!-- Timeline Point Icon -->
                                <div class="z-10 flex items-center justify-center w-8 h-8 rounded-full shadow-sm border border-white dark:border-gray-800 transition group-hover:scale-110 
                                    {{ $p->type === 'ENTREE' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400' }}">
                                    @if($p->type === 'ENTREE')
                                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                    @else
                                        <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4" />
                                    @endif
                                </div>
                                
                                <!-- Card Item Content -->
                                <div class="flex-1 p-4 bg-gray-50 dark:bg-gray-700/20 border border-gray-200/30 dark:border-gray-700/30 hover:border-gray-300/40 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition duration-200">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                            {{ $p->type === 'ENTREE' ? 'Arrivée (Check-in)' : 'Départ (Check-out)' }}
                                            <span class="text-xs font-normal text-gray-400">via {{ $p->source ?? 'QR' }}</span>
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                                            <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                                            {{ \Carbon\Carbon::parse($p->created_at)->safeTranslatedFormat('l d F Y à H:i') }}
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        @if($p->type === 'ENTREE')
                                            <span class="px-3 py-1 text-xs font-bold rounded-full border 
                                                {{ $p->status === 'Present' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 
                                                  ($p->status === 'Retard' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' : 
                                                  'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20') }}">
                                                {{ $p->status === 'Present' ? 'À l\'heure' : ($p->status === 'Retard' ? 'En retard' : $p->status) }}
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-bold rounded-full border bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 border-gray-200 dark:border-gray-600">
                                                Terminé
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>