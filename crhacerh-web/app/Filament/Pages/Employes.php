<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Employe;

class Employes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Employés';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.employes';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employe::query()->where('role', '!=', 'RH_Admin')
                    ->withCount('absences')
                    ->withCount([
                        'pointages as retards_count' => fn($query) => $query->where('type', 'ENTREE')->where('status', 'Retard'),
                        'pointages as a_lheure_count' => fn($query) => $query->where('type', 'ENTREE')->where('status', 'Present'),
                    ])
            )
            ->columns([
                TextColumn::make('full_name')->label('Nom et Prénoms')->searchable(),
                TextColumn::make('department.name')->label('Départements'),
                TextColumn::make('absences_count')->label('Absences')->default(0),
                TextColumn::make('retards_count')->label('Retards')->color('warning')->default(0),
                TextColumn::make('a_lheure_count')->label('À l\'heure')->color('primary')->default(0),
            ])
            // Dans votre tableau dans Employes.php
            ->recordUrl(fn($record) => \App\Filament\Pages\StatsEmploye::getUrl(['employeId' => $record->id]));
    }
}
