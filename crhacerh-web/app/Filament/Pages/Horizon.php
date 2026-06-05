<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Presence;
use App\Models\Employe;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Horizon extends Page
{
    protected static string $view = 'filament.pages.horizon';
    protected static ?string $title = 'Horizon';
    protected static ?int $navigationSort = 5;

    public string $messageInterne = '';
    public array $messages = [];

    public static function getNavigationIcon(): string | Htmlable | null
    {
        // Exemple avec une image stockée dans public/images/mon-icone.png
        return new HtmlString('
            <img src="' . asset('images/ia-avatar.png') . 
                 'style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover; max-width: 20px;"' . 
                 'alt="Horizon">');
    }

    public function mount(): void
    {
        // Message d'accueil initial de l'IA
        $this->messages[] = [
            'sender' => 'ia',
            'text' => "Bonjour ! Je suis votre assistant C.H.R.A.C.E.R.H. Je peux vous aider à analyser les présences, détecter les anomalies ou résumer les tendances de la semaine. Que puis-je faire pour vous aujourd'hui ?",
            'time' => now()->format('H:i')
        ];
    }

    public function envoyerMessage(): void
    {
        if (empty(trim($this->messageInterne))) {
            return;
        }

        $userMessage = $this->messageInterne;

        // 1. Sauvegarder le message de l'utilisateur dans l'interface
        $this->messages[] = [
            'sender' => 'moi',
            'text' => $userMessage,
            'time' => now()->format('H:i')
        ];

        // Réinitialiser le champ de saisie immédiatement
        $this->messageInterne = '';

        // 2. Préparer les données du Dashboard pour donner du contexte à l'IA
        $totalEmployes = Employe::count() ?? 0;
        $absentsDuJour = Presence::whereDate('created_at', Carbon::today())->where('statut', 'Absent')->count();
        $retardsDuJour = Presence::whereDate('created_at', Carbon::today())->where('statut', 'Retard')->count();

        // 3. Définir le rôle d'Horizon (System Prompt)
        $systemPrompt = "Tu es Horizon, un assistant virtuel expert en Ressources Humaines intégré à l'application C.H.R.A.C.E.R.H. "
                      . "Ton rôle est d'aider le gestionnaire à analyser les présences et gérer les employés. "
                      . "Voici les statistiques réelles de l'entreprise aujourd'hui : "
                      . "Effectif total : {$totalEmployes} employés, Absents aujourd'hui : {$absentsDuJour}, Retards aujourd'hui : {$retardsDuJour}. "
                      . "Réponds de manière concise, professionnelle et directement en français.";

        try {
            // 4. Initialiser le client Gemini avec la clé du fichier .env
            $client = Gemini::client(env('GEMINI_API_KEY'));

            // On fusionne le prompt système avec la question de l'utilisateur
            $promptComplet = $systemPrompt . "\n\nL'utilisateur demande : " . $userMessage;

            // 5. Appel direct à l'API (Modèle flash, très rapide pour le chat)
            $response = $client->geminiPro()->generateContent($promptComplet);
            $reponseTexte = $response->text();

        } catch (\Exception $e) {
            // En cas de problème technique ou de clé manquante, on bascule sur une réponse de secours
            $reponseTexte = "Désolé, je rencontre des difficultés pour joindre lire le serveur. Veuillez vérifier votre connexion ou votre clé API.";
        }

        // 6. Ajouter la réponse de l'IA à l'historique de l'écran
        $this->messages[] = [
            'sender' => 'ia',
            'text' => $reponseTexte,
            'time' => now()->format('H:i')
        ];
    }
}