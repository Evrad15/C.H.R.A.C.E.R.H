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
    public static function getGoogleCalendarService()
    {
        $client = new GoogleClient();
        
        // Chemin vers ton fichier JSON de clé de compte de service Google Cloud
        $client->setAuthConfig(storage_path('app/google-calendar/service-account.json'));
        $client->addScope(GoogleCalendar::CALENDAR);
        
        return new GoogleCalendar($client);
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