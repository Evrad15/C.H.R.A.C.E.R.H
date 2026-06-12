<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-4">Rapports générés</h2>

        @if(empty($files))
        <div class="text-gray-500 dark:text-gray-400 py-8 text-center bg-gray-50 dark:bg-gray-850 rounded-xl border border-dashed border-gray-200 dark:border-gray-800">
            <i class="fa-solid fa-folder-open text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
            Aucun rapport trouvé. Générez d'abord un rapport via l'assistant.
        </div>
        @else
        <div class="space-y-3">
            @foreach($files as $file)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-100 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-800/20 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-primary-50 dark:bg-primary-950/30 text-primary-600 dark:text-primary-400 rounded-xl">
                        <i class="fa-solid fa-file-arrow-down text-2xl"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-950 dark:text-gray-50">{{ $file['name'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Modifié : {{ $file['modified']->format('Y-m-d H:i:s') }} — Taille : {{ number_format($file['size'] / 1024, 2) }} KB</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <a href="{{ $file['url'] }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium text-sm transition">
                        <i class="fa-solid fa-download"></i> Télécharger
                    </a>
                    <button type="button" 
                        wire:click="deleteFile('{{ $file['path'] }}')" 
                        wire:confirm="Supprimer ce fichier ?" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-850 font-medium text-sm transition">
                        <i class="fa-solid fa-trash-can"></i> Supprimer
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-filament-panels::page>