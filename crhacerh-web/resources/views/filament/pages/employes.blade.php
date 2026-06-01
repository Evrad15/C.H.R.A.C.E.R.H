<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Employés Actifs (Inscrits via Mobile)</div>
            <div class="text-3xl font-bold mt-2 text-primary-600">--</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Présents Aujourd'hui</div>
            <div class="text-3xl font-bold mt-2 text-success-600">--</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">Retards Détectés</div>
            <div class="text-3xl font-bold mt-2 text-danger-600">--</div>
        </x-filament::card>

    </div>

    <x-filament::card class="mt-6">
        <div class="border-b border-gray-200 pb-4 mb-4">
            <h3 class="text-lg font-medium">Flux des Pointages en Temps Réel</h3>
            <p class="text-sm text-gray-500">Visualisation des entrées/sorties synchronisées depuis l'application mobile.</p>
        </div>
        
        <div class="p-6 text-center text-gray-400 italic">
            Le tableau des pointages et les filtres d'analyse seront intégrés ici.
        </div>
    </x-filament::card>
</x-filament-panels::page>