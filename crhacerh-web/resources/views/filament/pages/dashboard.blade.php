<x-filament-panels::page>
    <div class="mb-6">
        <p class="text-gray-500 dark:text-gray-400 mt-2">
            Semaine {{ now()->weekOfYear }} • {{ now()->translatedFormat('l j F Y') }}
        </p>
    </div>

    <div class="mb-8" style="width: 100%;">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-6 bg-primary-500 rounded-full"></span>
            Indicateurs Clés
        </h2>

        <div style="display: flex; flex-direction: row; justify-content: space-between; gap: 1rem; width: 100%;">

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300" style="flex: 1;">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Total Employés
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-lg">
                        👥
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $this->totalEmployes }}
                    </p>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Effectif global actif</p>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-900/30 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 border-l-4 border-l-red-500" style="flex: 1;">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Absents (Sem.)
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-lg">
                        ❌
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                        {{ $this->absentsSemaine }}
                    </p>
                    @if($this->tendanceAbsents >= 0)
                    <span class="text-xs font-bold text-red-600 dark:text-red-400">
                        ↑ +{{ $this->tendanceAbsents }}
                    </span>
                    @else
                    <span class="text-xs font-bold text-green-600 dark:text-green-400">
                        ↓ {{ abs($this->tendanceAbsents) }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">vs jour précédent</p>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-900/30 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 border-l-4 border-l-amber-500" style="flex: 1;">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        En Retard (Sem.)
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-lg">
                        ⏰
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                        {{ $this->retardsSemaine }}
                    </p>
                    @if($this->tendanceRetards >= 0)
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400">
                        ↑ +{{ $this->tendanceRetards }}
                    </span>
                    @else
                    <span class="text-xs font-bold text-green-600 dark:text-green-400">
                        ↓ {{ abs($this->tendanceRetards) }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">vs jour précédent</p>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-900/30 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 border-l-4 border-l-green-500" style="flex: 1;">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        À l'Heure (Sem.)
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-lg">
                        ✅
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ $this->aLHeureSemaine }}
                    </p>
                    @if($this->tendanceALHeure >= 0)
                    <span class="text-xs font-bold text-green-600 dark:text-green-400">
                        ↑ +{{ $this->tendanceALHeure }}
                    </span>
                    @else
                    <span class="text-xs font-bold text-red-600 dark:text-red-400">
                        ↓ {{ abs($this->tendanceALHeure) }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">vs jour précédent</p>
            </div>

        </div>
    </div>

    <div class="lg:col-span-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 flex flex-col justify-between">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-4 bg-primary-500 rounded-full"></span>
            Évolution des Présences
        </h3>
        <div class="h-64 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/50 dark:to-gray-800/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center">
            <div class="text-center">
                <p class="text-gray-400 text-xs font-medium mb-1">Graphique en courbe</p>
                <p class="text-[10px] text-gray-500">
                    [Chart Data: {{ count($this->chartData) }} points]
                </p>
            </div>
        </div>
    </div>

    <div class="mb-8" style="width: 100%;">
        <div style="display: flex; flex-direction: row; gap: 1.5rem; width: 100%; items: stretch;">

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300" style="flex: 1.2; max-width: 380px; aspect-square: 1 / 1; display: flex; flex-direction: column; justify-content: space-between;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                    Anomalies
                </h3>

                <div class="space-y-2 overflow-y-auto pr-1" style="flex: 1; max-height: 260px;">
                    @forelse($this->anomalies as $alerte)
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-900/60 transition-colors">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate">
                                    {{ $alerte->employe->nom ?? 'Inconnu' }}
                                </p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $alerte->created_at->format('H:i') }}
                                </p>
                            </div>
                            <span class="flex-shrink-0 inline-block px-2 py-1 rounded text-[10px] font-bold {{ $alerte->statut === 'Absent' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }}">
                                {{ $alerte->statut }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center h-full text-center">
                        <p class="text-2xl mb-2">✨</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Aucune anomalie!</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300" style="flex: 1; min-height: 380px; display: flex; flex-direction: column; items: center; justify-content: space-between;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 text-center">
                    <span class="text-lg">📈</span>
                    <span class="block text-sm mt-1">Taux Présence</span>
                </h3>

                <div class="relative w-36 h-36 flex items-center justify-center my-auto">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="2.5" class="text-gray-200 dark:text-gray-700" />
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-dasharray="{{ $this->tauxPresenceGlobal }}, 100"
                            stroke-linecap="round" class="text-primary-500 transition-all duration-1000" />
                    </svg>
                    <div class="absolute text-center">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $this->tauxPresenceGlobal }}%
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 text-center font-medium mt-4">
                    Objectif: <span class="font-bold text-primary-600 dark:text-primary-400">95%</span>
                </p>
            </div>

        </div>
    </div>
    <div class="w-full">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-6 bg-primary-500 rounded-full"></span>
            Classements de la Semaine
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full mt-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm min-h-[320px] flex flex-col">
                <h4 class="text-sm font-bold text-red-600 dark:text-red-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    🔻 Top 5 Absents
                </h4>
                <ol class="space-y-4 flex-1">
                    @forelse($this->topAbsents as $index => $row)
                    <li class="flex items-center gap-4 py-1">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <span class="text-xs font-bold text-red-700 dark:text-red-400">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $row->employe->nom ?? 'Inconnu' }}
                            </p>
                        </div>
                        <span class="flex-shrink-0 inline-block px-2.5 py-1 rounded-full bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-bold">
                            {{ $row->total }} j
                        </span>
                    </li>
                    @empty
                    <div class="flex items-center justify-center h-full text-center">
                        <p class="text-sm text-gray-400 py-4">Aucune donnée</p>
                    </div>
                    @endforelse
                </ol>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm min-h-[320px] flex flex-col">
                <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    ⏰ Top 5 Retards
                </h4>
                <ol class="space-y-4 flex-1">
                    @forelse($this->topRetards as $index => $row)
                    <li class="flex items-center gap-4 py-1">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <span class="text-xs font-bold text-amber-700 dark:text-amber-400">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $row->employe->nom ?? 'Inconnu' }}
                            </p>
                        </div>
                        <span class="flex-shrink-0 inline-block px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 text-xs font-bold">
                            {{ $row->total }} j
                        </span>
                    </li>
                    @empty
                    <div class="flex items-center justify-center h-full text-center">
                        <p class="text-sm text-gray-400 py-4">Aucune donnée</p>
                    </div>
                    @endforelse
                </ol>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm min-h-[320px] flex flex-col">
                <h4 class="text-sm font-bold text-green-600 dark:text-green-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    🏆 Top 5 Assidus
                </h4>
                <ol class="space-y-4 flex-1">
                    @forelse($this->topAssidus as $index => $row)
                    <li class="flex items-center gap-4 py-1">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <span class="text-xs font-bold text-green-700 dark:text-green-400">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $row->employe->nom ?? 'Inconnu' }}
                            </p>
                        </div>
                        <span class="flex-shrink-0 inline-block px-2.5 py-1 rounded-full bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs font-bold">
                            {{ $row->total }} j
                        </span>
                    </li>
                    @empty
                    <div class="flex items-center justify-center h-full text-center">
                        <p class="text-sm text-gray-400 py-4">Aucune donnée</p>
                    </div>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
</x-filament-panels::page>