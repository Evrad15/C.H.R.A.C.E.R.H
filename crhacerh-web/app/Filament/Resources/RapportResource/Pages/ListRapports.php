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

    // Livewire state for filtering and searching
    public ?string $selectedCategory = null;
    public ?string $selectedFormat   = null;
    public ?string $selectedPeriod   = null;
    public string  $search           = '';

    // Drill-down: when not null we show the folder detail view
    public ?string $openedFolder     = null; // e.g. '2026-06'
    public string  $openedFolderName = '';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getViewData(): array
    {
        $disk = Storage::disk('public');
        $folder = 'reports';

        $monthsFr = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        if (! $disk->exists($folder)) {
            $currentMonthKey = now()->format('Y-m');
            $currentMonthName = $monthsFr[now()->month] . ' ' . now()->year;
            return [
                'files' => [],
                'folders' => [
                    [
                        'key' => $currentMonthKey,
                        'name' => $currentMonthName,
                        'count' => 0,
                    ]
                ],
                'counts' => [
                    'all' => 0,
                ],
                'employees' => [],
            ];
        }

        // Get all files
        $allFiles = collect($disk->files($folder))->map(function ($path) use ($disk, $monthsFr) {
            $modified = $disk->lastModified($path);
            $name = basename($path);
            $carbonDate = Carbon::createFromTimestamp($modified);
            
            $month = $carbonDate->month;
            $year = $carbonDate->year;
            $folderKey = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT);
            $folderName = $monthsFr[$month] . ' ' . $year;

            // Determine extension/format
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $format = match ($extension) {
                'xlsx' => 'Excel',
                'pdf' => 'PDF',
                default => 'Fichier',
            };

            return [
                'path'        => $path,
                'name'        => $name,
                'url'         => $disk->url($path),
                'size'        => $disk->size($path),
                'modified'    => $carbonDate,
                'folder_key'  => $folderKey,
                'folder_name' => $folderName,
                'format'      => $format,
            ];
        });

        // Unique folders list with counts
        $foldersGrouped = $allFiles->groupBy('folder_key');
        $foldersList = $foldersGrouped->map(function ($group, $key) {
            return [
                'key' => $key,
                'name' => $group->first()['folder_name'],
                'count' => $group->count(),
            ];
        })->sortByDesc('key')->values()->all();

        // Ensure current month is always in the folders list
        $currentMonthKey = now()->format('Y-m');
        $currentMonthName = $monthsFr[now()->month] . ' ' . now()->year;
        $hasCurrent = collect($foldersList)->contains('key', $currentMonthKey);
        if (!$hasCurrent) {
            array_unshift($foldersList, [
                'key' => $currentMonthKey,
                'name' => $currentMonthName,
                'count' => 0,
            ]);
            // Sort again desc
            usort($foldersList, fn($a, $b) => strcmp($b['key'], $a['key']));
        }

        // Default to the first folder key (most recent month with reports or current month)
        if ($this->selectedCategory === null && count($foldersList) > 0) {
            $this->selectedCategory = $foldersList[0]['key'];
        }

        // Compute counts before filtering
        $counts = [
            'all' => $allFiles->count(),
        ];

        // Apply folder filter
        if ($this->selectedCategory !== null && $this->selectedCategory !== 'all') {
            $allFiles = $allFiles->where('folder_key', $this->selectedCategory);
        }

        // Apply format filter
        if ($this->selectedFormat !== null && $this->selectedFormat !== '') {
            $allFiles = $allFiles->where('format', $this->selectedFormat);
        }

        // Apply period filter (by matching filename pattern)
        if ($this->selectedPeriod !== null && $this->selectedPeriod !== '') {
            $allFiles = $allFiles->filter(function($file) {
                $name = strtolower($file['name']);
                if ($this->selectedPeriod === 'journalier') {
                    return str_contains($name, '_day') || str_contains($name, 'journalier');
                } elseif ($this->selectedPeriod === 'hebdomadaire') {
                    return str_contains($name, '_week') || str_contains($name, 'hebdomadaire');
                } elseif ($this->selectedPeriod === 'mensuel') {
                    return str_contains($name, '_month') || str_contains($name, 'mensuel');
                } elseif ($this->selectedPeriod === 'annuel') {
                    return str_contains($name, '_year') || str_contains($name, 'annuel');
                }
                return true;
            });
        }

        // Apply search query filter
        if (!empty($this->search)) {
            $searchLower = strtolower($this->search);
            $allFiles = $allFiles->filter(function ($file) use ($searchLower) {
                return str_contains(strtolower($file['name']), $searchLower);
            });
        }

        $filteredFiles = $allFiles->sortByDesc('modified')->values()->all();

        // Get some employees for avatar displaying
        $employees = \App\Models\Employe::whereIn('role', ['RH_Admin', 'Manager'])->take(3)->get()->map(function($emp) {
            return [
                'initials' => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                'name' => $emp->getFullNameAttribute(),
            ];
        })->toArray();

        return [
            'files'            => $filteredFiles,
            'folders'          => $foldersList,
            'counts'           => $counts,
            'employees'        => $employees,
            'openedFolder'     => $this->openedFolder,
            'openedFolderName' => $this->openedFolderName,
        ];
    }

    public function selectCategory(?string $category): void
    {
        $this->selectedCategory = $category;
    }

    /** Open a folder in drill-down mode */
    public function openFolder(string $folderKey, string $folderName): void
    {
        $this->openedFolder     = $folderKey;
        $this->openedFolderName = $folderName;
        $this->selectedCategory = $folderKey;
    }

    /** Go back to the folder grid */
    public function closeFolder(): void
    {
        $this->openedFolder     = null;
        $this->openedFolderName = '';
        $this->selectedCategory = null;
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
