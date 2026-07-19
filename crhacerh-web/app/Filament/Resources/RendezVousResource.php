<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RendezVousResource\Pages;
use App\Models\RendezVous;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class RendezVousResource extends Resource
{
    protected static ?string $model = RendezVous::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Rendez-vous Patients';

    protected static ?string $modelLabel = 'Rendez-vous Patient';

    protected static ?string $pluralModelLabel = 'Rendez-vous Patients';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Patient')
                    ->description('Coordonnées du patient reçu en consultation')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('nom_patient')
                            ->label('Nom et Prénom du Patient')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Ex: Mme Aminata DIOP')
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('telephone_patient')
                            ->label('Téléphone de Contact')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('+237 6XX XX XX XX'),
                        Forms\Components\TextInput::make('email_patient')
                            ->label('Adresse Email')
                            ->email()
                            ->maxLength(150)
                            ->placeholder('patient@email.com'),
                    ])->columns(2),

                Forms\Components\Section::make('Détails du Rendez-vous')
                    ->description('Planification et service hospitalier concerné')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\Select::make('service')
                            ->label('Service Hospitalier')
                            ->options([
                                'Chirurgie Endoscopique' => 'Chirurgie Endoscopique',
                                'FIV / Reproduction Humaine' => 'FIV / Reproduction Humaine',
                                'Consultation Gynécologique' => 'Consultation Gynécologique',
                                'Échographie & Imagerie' => 'Échographie & Imagerie',
                                'Suivi de Grossesse' => 'Suivi de Grossesse',
                                'Consultation d’Infertilité' => 'Consultation d’Infertilité',
                                'Autre' => 'Autre',
                            ])
                            ->required()
                            ->default('Chirurgie Endoscopique'),
                        Forms\Components\TextInput::make('motif')
                            ->label('Motif / Type de consultation')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Première consultation FIV / Échographie pelvienne'),
                        Forms\Components\DateTimePicker::make('date_rendez_vous')
                            ->label('Date et Heure de début')
                            ->required()
                            ->default(now()->addDay()->setHour(9)->setMinute(0)),
                        Forms\Components\DateTimePicker::make('fin_rendez_vous')
                            ->label('Date et Heure de fin estimée')
                            ->default(now()->addDay()->setHour(9)->setMinute(30)),
                        Forms\Components\TextInput::make('medecin')
                            ->label('Médecin Spécialiste')
                            ->maxLength(150)
                            ->placeholder('Ex: Dr. KASSA / Prof. KASSA'),
                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options([
                                'Planifié' => 'Planifié',
                                'Confirmé' => 'Confirmé',
                                'Réalisé' => 'Réalisé',
                                'Reporté' => 'Reporté',
                                'Annulé' => 'Annulé',
                            ])
                            ->required()
                            ->default('Planifié'),
                    ])->columns(2),

                Forms\Components\Section::make('Observations & Notes Cliniques')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Indiquez ici les antécédents, instructions de jeûne ou documents à apporter...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom_patient')
                    ->label('Patient')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (RendezVous $record): ?string => $record->telephone_patient ?: $record->email_patient),
                Tables\Columns\TextColumn::make('service')
                    ->label('Service')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'FIV / Reproduction Humaine' => 'info',
                        'Chirurgie Endoscopique' => 'warning',
                        'Consultation Gynécologique' => 'success',
                        'Échographie & Imagerie' => 'gray',
                        default => 'primary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('motif')
                    ->label('Motif')
                    ->limit(35)
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_rendez_vous')
                    ->label('Date et Heure')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('medecin')
                    ->label('Médecin')
                    ->searchable()
                    ->default('Non assigné')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Planifié' => 'warning',
                        'Confirmé' => 'info',
                        'Réalisé' => 'success',
                        'Reporté' => 'gray',
                        'Annulé' => 'danger',
                        default => 'primary',
                    })
                    ->sortable(),
            ])
            ->defaultSort('date_rendez_vous', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('service')
                    ->label('Filtrer par Service')
                    ->options([
                        'Chirurgie Endoscopique' => 'Chirurgie Endoscopique',
                        'FIV / Reproduction Humaine' => 'FIV / Reproduction Humaine',
                        'Consultation Gynécologique' => 'Consultation Gynécologique',
                        'Échographie & Imagerie' => 'Échographie & Imagerie',
                        'Suivi de Grossesse' => 'Suivi de Grossesse',
                        'Consultation d’Infertilité' => 'Consultation d’Infertilité',
                        'Autre' => 'Autre',
                    ]),
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Filtrer par Statut')
                    ->options([
                        'Planifié' => 'Planifié',
                        'Confirmé' => 'Confirmé',
                        'Réalisé' => 'Réalisé',
                        'Reporté' => 'Reporté',
                        'Annulé' => 'Annulé',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Rendez-vous du jour')
                    ->query(fn (Builder $query): Builder => $query->whereDate('date_rendez_vous', Carbon::today())),
            ])
            ->actions([
                Tables\Actions\Action::make('confirmer')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn (RendezVous $record) => in_array($record->statut, ['Planifié', 'Reporté']))
                    ->action(fn (RendezVous $record) => $record->update(['statut' => 'Confirmé'])),
                Tables\Actions\Action::make('realiser')
                    ->label('Marquer Réalisé')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (RendezVous $record) => !in_array($record->statut, ['Réalisé', 'Annulé']))
                    ->action(fn (RendezVous $record) => $record->update(['statut' => 'Réalisé'])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRendezVous::route('/'),
            'create' => Pages\CreateRendezVous::route('/create'),
            'edit' => Pages\EditRendezVous::route('/{record}/edit'),
        ];
    }
}
