<?php

namespace App\Filament\Resources\RapportResource\Pages;

use App\Filament\Resources\RapportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ListRapports extends ListRecords
{
    protected static string $resource = RapportResource::class;

    protected static string $view = 'filament.pages.rapports';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getViewData(): array
    {
        $disk = Storage::disk('public');
        $folder = 'reports';

        if (! $disk->exists($folder)) {
            return ['files' => []];
        }

        $files = collect($disk->files($folder))->map(function ($path) use ($disk) {
            $modified = $disk->lastModified($path);
            return [
                'path' => $path,
                'name' => basename($path),
                'url' => '/storage/' . $path,
                'size' => $disk->size($path),
                'modified' => Carbon::createFromTimestamp($modified),
            ];
        })->sortByDesc('modified')->values()->all();

        return ['files' => $files];
    }

    public function deleteFile(string $path): void
    {
        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
            
            Notification::make()
                ->title('Rapport supprimé')
                ->success()
                ->send();
        }
    }
}
