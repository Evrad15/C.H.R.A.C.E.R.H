<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-sm">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Employés</p>
            <p class="text-3xl font-bold mt-2 text-gray-900 dark:text-white">{{ $this->totalEmployes }}</p>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-l-red-500">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Absents (Cette semaine)</p>
            <p class="text-3xl font-bold mt-2 text-red-600 dark:text-red-400">{{ $this->absentsSemaine }}</p>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-l-amber-500">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">En retard (Cette semaine)</p>
            <p class="text-3xl font-bold mt-2 text-amber-600 dark:text-amber-400">{{ $this->retardsSemaine }}</p>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-l-green-500">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">À l'heure (Cette semaine)</p>
            <p class="text-3xl font-bold mt-2 text-green-600 dark:text-green-400">{{ $this->aLHeureSemaine }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-sm">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Évolution des présences (Lundi - Vendredi)</h3>
        
        <div class="relative h-72 w-full flex items-center justify-center bg-gray-50 dark:bg-gray-950/40 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
            <div class="absolute top-4 right-4 flex gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-green-500"></span> À l'heure</span>
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-amber-500"></span> En retard</span>
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-red-500"></span> Absent</span>
            </div>
            
            <p class="text-gray-400 text-sm">
                [Intégration du composant graphique avec les données : {{ json_encode($this->chartData) }}]
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-sm">
            <h4 class="text-sm font-bold text-red-600 dark:text-red-400 uppercase tracking-wider mb-4">Top 5 les plus absents</h4>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->topAbsents as $index => $row)
                    <li class="py-2 flex justify-between items-center text-sm">
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $index + 1 }}. {{ $row->employe->nom ?? 'Employé inconnu' }}</span>
                        <span class="bg-red-50 dark:bg-red-950/30 text-red-600 px-2.5 py-0.5 rounded-full font-semibold text-xs">{{ $row->total }} fois</span>
                    </li>
                @empty
                    <li class="py-4 text-gray-400 text-sm text-center">Aucune absence cette semaine</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-sm">
            <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-4">Top 5 les plus en retard</h4>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->topRetards as $index => $row)
                    <li class="py-2 flex justify-between items-center text-sm">
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $index + 1 }}. {{ $row->employe->nom ?? 'Employé inconnu' }}</span>
                        <span class="bg-amber-50 dark:bg-amber-950/30 text-amber-600 px-2.5 py-0.5 rounded-full font-semibold text-xs">{{ $row->total }} fois</span>
                    </li>
                @empty
                    <li class="py-4 text-gray-400 text-sm text-center">Aucun retard cette semaine</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-sm">
            <h4 class="text-sm font-bold text-green-600 dark:text-green-400 uppercase tracking-wider mb-4">Top 5 les plus assidus</h4>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->topAssidus as $index => $row)
                    <li class="py-2 flex justify-between items-center text-sm">
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $index + 1 }}. {{ $row->employe->nom ?? 'Employé inconnu' }}</span>
                        <span class="bg-green-50 dark:bg-green-950/30 text-green-600 px-2.5 py-0.5 rounded-full font-semibold text-xs">{{ $row->total }} fois</span>
                    </li>
                @empty
                    <li class="py-4 text-gray-400 text-sm text-center">Aucune présence enregistrée</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-filament-panels::page>