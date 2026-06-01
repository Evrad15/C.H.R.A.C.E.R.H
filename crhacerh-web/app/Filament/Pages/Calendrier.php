<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

class Calendrier extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Calendrier';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.calendrier';

    public array $events = [];
    public bool $isConnected = false;

    public function mount()
    {
        if (session()->has('google_calendar_token')) {
            $this->isConnected = true;

            $client = new Client();

            // 👇 AJOUTE CETTE LIGNE
            $client->setHttpClient(new \GuzzleHttp\Client([
                'verify' => false,  // Désactive SSL verification
            ]));

            $client->setAccessToken(session('google_calendar_token'));

            if ($client->isAccessTokenExpired()) {
                session()->forget('google_calendar_token');
                $this->isConnected = false;
                return;
            }

            $service = new Calendar($client);
            try {
                $calendarId = 'primary';
                $results = $service->events->listEvents($calendarId, [
                    'maxResults' => 50,
                    'orderBy' => 'startTime',
                    'singleEvents' => true,
                    'timeMin' => date('c'),
                ]);

                foreach ($results->getItems() as $event) {
                    $this->events[] = [
                        'title' => $event->getSummary(),
                        'start' => $event->getStart()->getDateTime() ?? $event->getStart()->getDate(),
                        'end' => $event->getEnd()->getDateTime() ?? $event->getEnd()->getDate(),
                    ];
                }
            } catch (\Exception $e) {
                $this->events = [];
            }
        }
    }

    public function createEvent($title, $startTime, $endTime)
    {
        $client = new \Google\Client();
        $client->setAccessToken(session('google_calendar_token'));
        $service = new \Google\Service\Calendar($client);

        $event = new Event([
            'summary' => $title,
            'start' => ['dateTime' => $startTime],
            'end' => ['dateTime' => $endTime],
        ]);

        $service->events->insert('primary', $event);

        // Rafraîchir la page ou notifier l'utilisateur
        $this->redirect('/ressources-humaines/calendrier');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ajouter')
                ->label('Ajouter un événement')
                ->size('xs') // Taille réduite
                ->form([
                    TextInput::make('titre')->required(),
                ])
                ->action(function (array $data) {
                    // Ici tu ajouteras la logique d'envoi vers Google Calendar
                    \Filament\Notifications\Notification::make()
                        ->title('Événement : ' . $data['titre'] . ' créé !')
                        ->success()
                        ->send();
                }),
        ];
    }
}
