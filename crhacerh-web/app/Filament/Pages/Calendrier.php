<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Carbon\Carbon;

class Calendrier extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Calendrier';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.calendrier';

    public array $events = [];
    public array $holidays = [];
    public bool $isConnected = false;
    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->holidays = $this->getCameroonHolidays();

        if (session()->has('google_calendar_token')) {
            $this->isConnected = true;
            $this->loadGoogleEvents();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ajouter')
                ->label('Ajouter un événement')
                ->modalHeading('Nouvel événement Google Calendar')
                ->form([
                    TextInput::make('title')
                        ->label('Titre')
                        ->placeholder('Ex: Réunion RH')
                        ->required(),
                    TextInput::make('startTime')
                        ->label('Début')
                        ->type('datetime-local')
                        ->required(),
                    TextInput::make('endTime')
                        ->label('Fin')
                        ->type('datetime-local')
                        ->required(),
                ])
                ->fillForm(fn() => [
                    'startTime' => $this->selectedDate ? $this->selectedDate . 'T08:00' : null,
                    'endTime' => $this->selectedDate ? $this->selectedDate . 'T09:00' : null,
                ])
                ->action(function (array $data) {
                    $this->createEvent($data['title'], $data['startTime'], $data['endTime']);
                })
                ->modalSubmitActionLabel('Créer'),
        ];
    }

    public function createEvent($title, $startTime, $endTime): void
    {
        $client = new Client();
        $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        $client->setAccessToken(session('google_calendar_token'));
        
        $service = new Calendar($client);
        $event = new Event([
            'summary' => $title,
            'start' => ['dateTime' => date('c', strtotime($startTime))],
            'end' => ['dateTime' => date('c', strtotime($endTime))],
        ]);

        try {
            $service->events->insert('primary', $event);
            Notification::make()->title('✅ Événement ajouté')->success()->send();
            $this->redirect(static::getUrl());
        } catch (\Exception $e) {
            Notification::make()->title('❌ Erreur')->body($e->getMessage())->danger()->send();
        }
    }

    #[On('open-event-modal')]
    public function handleDateSelection(string $date): void
    {
        $this->selectedDate = $date;
        $this->mountAction('ajouter');
    }

    private function loadGoogleEvents(): void
    {
        $client = new Client();
        $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        $client->setAccessToken(session('google_calendar_token'));
        $service = new Calendar($client);
        
        try {
            $results = $service->events->listEvents('primary', ['singleEvents' => true, 'timeMin' => date('c')]);
            foreach ($results->getItems() as $event) {
                $this->events[] = [
                    'title' => $event->getSummary(),
                    'start' => $event->getStart()->getDateTime() ?? $event->getStart()->getDate(),
                    'end' => $event->getEnd()->getDateTime() ?? $event->getEnd()->getDate(),
                    'color' => '#f59e0b',
                ];
            }
        } catch (\Exception $e) { $this->isConnected = false; }
    }

    private function getCameroonHolidays(): array { /* ... ton code existant ... */ return []; }
    private function getEasterDate($year, $days = 0): string { /* ... ton code existant ... */ return ''; }
}