<x-filament-panels::page>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            Statistiques de {{ $this->employe->first_name }} {{ $this->employe->last_name }}
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-filament::section>
                <x-slot name="heading">Absences</x-slot>
                <p class="text-3xl font-semibold">
                    <p class="text-3xl font-semibold">{{ $this->employe->Absences()->count() }}</p>
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Retards</x-slot>
                <p class="text-3xl font-semibold">
                    {{ $this->employe->pointages()->where('status', 'Retard')->count() }}
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">À l'heure</x-slot>
                <p class="text-3xl font-semibold"> 
                    {{ $this->employe->pointages()->where('status', 'Present')->count() }}
                </p>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>