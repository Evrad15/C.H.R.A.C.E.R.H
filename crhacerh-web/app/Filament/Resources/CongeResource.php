<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CongeResource\Pages;
use App\Filament\Resources\CongeResource\RelationManagers;
use App\Models\Conge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CongeResource extends Resource
{
    protected static ?string $model = Conge::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Congés & Permissions';
    protected static ?string $modelLabel = 'Demande de congé';
    protected static ?string $pluralModelLabel = 'Demandes de congés';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employe_id')
                    ->relationship('employe', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('type')
                    ->options([
                        'Congé' => 'Congé',
                        'Permission' => 'Permission',
                        'Maladie' => 'Maladie',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('date_debut')
                    ->required(),
                Forms\Components\DateTimePicker::make('date_fin')
                    ->required(),
                Forms\Components\Textarea::make('motif')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('statut')
                    ->options([
                        'En attente' => 'En attente',
                        'Approuvée' => 'Approuvée',
                        'Rejetée' => 'Rejetée',
                    ])
                    ->default('En attente')
                    ->required(),
                Forms\Components\Textarea::make('commentaire_rh')
                    ->label('Commentaire RH (optionnel)')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employe.full_name')
                    ->label('Employé')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Congé' => 'info',
                        'Permission' => 'gray',
                        'Maladie' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('date_debut')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'En attente' => 'warning',
                        'Approuvée' => 'success',
                        'Rejetée' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'En attente' => 'En attente',
                        'Approuvée' => 'Approuvée',
                        'Rejetée' => 'Rejetée',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approuver')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Conge $record) => $record->statut === 'En attente')
                    ->action(function (Conge $record, array $data) {
                        $record->update([
                            'statut' => 'Approuvée',
                            'commentaire_rh' => $data['commentaire_rh'] ?? null,
                        ]);
                    })
                    ->form([
                        Forms\Components\Textarea::make('commentaire_rh')
                            ->label('Commentaire RH (optionnel)'),
                    ]),
                Tables\Actions\Action::make('rejeter')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Conge $record) => $record->statut === 'En attente')
                    ->action(function (Conge $record, array $data) {
                        $record->update([
                            'statut' => 'Rejetée',
                            'commentaire_rh' => $data['commentaire_rh'] ?? null,
                        ]);
                    })
                    ->form([
                        Forms\Components\Textarea::make('commentaire_rh')
                            ->label('Raison du rejet (optionnel)'),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConges::route('/'),
            'create' => Pages\CreateConge::route('/create'),
            'edit' => Pages\EditConge::route('/{record}/edit'),
        ];
    }
}
