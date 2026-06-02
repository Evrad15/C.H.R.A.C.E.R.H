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
            ->query(Employe::query()->withCount(['absences', 'pointages']))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nom et Prénoms')
                    ->searchable(),
                
                TextColumn::make('department.name')
                    ->label('Départements'),
                
                TextColumn::make('absences_count')
                    ->label('Absences')
                    ->color('danger'),
                
                TextColumn::make('retards_count')
                    ->label('Retards')
                    ->color('warning'),
                
                TextColumn::make('a_lheure_count')
                    ->label('À l\'heure')
                    ->color('primary'),
            ])
            ->recordUrl(fn ($record) => "/ressources-humaines/statistiques/" . $record->id)
            ->actions([])
            ->bulkActions([]);
    }
}