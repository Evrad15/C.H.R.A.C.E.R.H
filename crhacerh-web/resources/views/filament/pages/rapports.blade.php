@php
/** @var array $files */
@endphp

<div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Rapports générés</h2>

    @if(empty($files))
    <div class="text-gray-500">Aucun rapport trouvé. Génère d'abord un rapport via l'assistant.</div>
    @else
    <div class="space-y-3">
        @foreach($files as $file)
        <div class="flex items-center justify-between p-4 border rounded-lg bg-white">
            <div class="flex items-center gap-4">
                <i class="fa-solid fa-file-arrow-down text-2xl text-gray-600"></i>
                <div>
                    <div class="font-medium">{{ $file['name'] }}</div>
                    <div class="text-sm text-gray-500">Modifié : {{ $file['modified']->format('Y-m-d H:i:s') }} — Taille : {{ number_format($file['size'] / 1024, 2) }} KB</div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ $file['url'] }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-primary-600 text-white">Télécharger</a>
                <form method="POST" action="{{ route('filament.pages.rapports.delete') }}" onsubmit="return confirm('Supprimer ce fichier ?');">
                    @csrf
                    <input type="hidden" name="path" value="{{ $file['path'] }}">
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded border">Supprimer</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>