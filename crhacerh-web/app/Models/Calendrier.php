<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;

class Calendrier extends Model
{
    use HasFactory;

    // Si ta table s'appelle autrement, spécifie-le ici
    protected $table = 'calendriers';

    protected $fillable = [
        'title',
        'google_event_id', // Stocke l'ID de l'événement Google pour les futures MAJ/Suppressions
        'start_at',
        'end_at',
        'description',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * Initialise et récupère le service Google Calendar API.
     */
    /**
     * Initialise et récupère le service Google Calendar API via OAuth2 (sans fichier JSON).
     */
    public static function getGoogleCalendarService()
    {
        $client = new \Google\Client();

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->addScope(\Google\Service\Calendar::CALENDAR);

        // 1. On récupère l'identifiant de l'admin connecté via Filament
        $authAdmin = \Filament\Facades\Filament::auth()->user();

        if ($authAdmin) {
            // 2. On force le chargement du vrai modèle Employe depuis la BDD pour avoir accès à update()
            $admin = \App\Models\Employe::find($authAdmin->getAuthIdentifier());

            if ($admin && $admin->google_access_token) {
                $client->setAccessToken([
                    'access_token'  => $admin->google_access_token,
                    'refresh_token' => $admin->google_refresh_token,
                    'expires_in'    => $admin->google_token_expires_at ? $admin->google_token_expires_at->diffInSeconds(now()) : 0
                ]);

                // Si le jeton d'accès a expiré, on le rafraîchit automatiquement
                if ($client->isAccessTokenExpired()) {
                    if ($client->getRefreshToken()) {
                        $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                        // Cette fois, l'object Éloquent mettra bien à jour ta table PostgreSQL
                        $admin->update([
                            'google_access_token'     => $newToken['access_token'],
                            'google_token_expires_at' => now()->addSeconds($newToken['expires_in']),
                        ]);
                    }
                }
            }
        }

        return new \Google\Service\Calendar($client);
    }

    /**
     * Exemple de méthode pour synchroniser un événement vers Google Agenda
     */
    public function syncToGoogle()
    {
        $calendarService = self::getGoogleCalendarService();
        $calendarId = config('services.google.calendar_id'); // À configurer dans config/services.php

        $eventData = new \Google\Service\Calendar\Event([
            'summary' => $this->title,
            'description' => $this->description,
            'start' => [
                'dateTime' => $this->start_at->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => $this->end_at->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
        ]);

        if ($this->google_event_id) {
            // Mettre à jour un événement existant
            $calendarService->events->update($calendarId, $this->google_event_id, $eventData);
        } else {
            // Créer un nouvel événement
            $googleEvent = $calendarService->events->insert($calendarId, $eventData);
            $this->updateQuietly(['google_event_id' => $googleEvent->id]);
        }
    }

    /**
     * Supprimer l'événement sur Google Agenda lors de la suppression locale
     */
    public function deleteFromGoogle()
    {
        if ($this->google_event_id) {
            $calendarService = self::getGoogleCalendarService();
            $calendarId = config('services.google.calendar_id');

            try {
                $calendarService->events->delete($calendarId, $this->google_event_id);
            } catch (\Exception $e) {
                // Évite de bloquer la suppression locale si l'événement n'existe plus sur Google
                logger('Erreur lors de la suppression Google Calendar: ' . $e->getMessage());
            }
        }
    }
}
