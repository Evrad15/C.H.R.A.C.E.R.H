<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Rapports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.rapports';

    protected static ?string $navigationLabel = 'Rapports';

    protected static ?string $title = 'Rapports';

    protected static ?int $navigationSort = 5;

    public function getMaxContentWidth(): string
    {
        return 'full';
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
                'url' => $disk->url($path),
                'size' => $disk->size($path),
                'modified' => Carbon::createFromTimestamp($modified),
            ];
        })->sortByDesc('modified')->values()->all();

        return ['files' => $files];
    }
}
