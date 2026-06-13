<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Employe;
use App\Services\HorizonAiService;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;

class Horizon extends Page
{
    use WithFileUploads;

    protected static string $view = 'filament.pages.horizon';
    protected static ?string $title = 'Horizon';
    protected ?string $heading = '';
    protected static ?int $navigationSort = 5;

    public string $messageInterne = '';
    public array $messages = [];
    public ?TemporaryUploadedFile $fileUpload = null;
    public bool $isRecording = false;
    protected ?HorizonAiService $assistant = null;

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return new HtmlString(
            '<img src="' . asset('images/Horizon.jpg') . '" ' .
                'style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover; max-width: 20px;" ' .
                'alt="Horizon">'
        );
    }

    public function mount(): void
    {
        $this->messages[] = [
            'sender' => 'ia',
            'text' => "Bonjour ! Je suis Horizon, l'assistant RH intelligent de C.H.R.A.C.E.R.H. Je peux générer des rapports, détecter des anomalies et envoyer des notifications aux responsables RH.",
            'time' => now()->format('H:i'),
        ];

        $this->messages[] = [
            'sender' => 'ia',
            'text' => "Commandes rapides : rapport journalier, rapport hebdomadaire, rapport mensuel, rapport annuel, détecte les anomalies, détecte la fraude.",
            'time' => now()->format('H:i'),
        ];
    }

    protected function assistant(): HorizonAiService
    {
        if ($this->assistant === null) {
            $this->assistant = new HorizonAiService();
        }

        return $this->assistant;
    }

    public function envoyerMessage(): void
    {
        $userMessage = trim($this->messageInterne);
        $file = $this->fileUpload;

        if (empty($userMessage) && !$file) {
            return;
        }

        $textToShow = $userMessage ?: "Analyse de fichier joint";
        if ($file) {
            $textToShow .= " 📎 (Fichier : " . $file->getClientOriginalName() . ")";
        }

        $this->messages[] = [
            'sender' => 'moi',
            'text' => $textToShow,
            'time' => now()->format('H:i'),
        ];

        $this->messageInterne = '';

        if ($file) {
            try {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('horizon_uploads', $fileName, 'public');
                $filePath = storage_path('app/public/horizon_uploads/' . $fileName);
                $mimeType = $file->getMimeType();

                $reponse = $this->assistant()->analyzeUserRequestWithFile($userMessage ?: "Analyse ce document", $filePath, $mimeType);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Erreur d'analyse fichier: " . $e->getMessage());
                $reponse = "Une erreur est survenue lors de l'analyse du fichier : " . $e->getMessage();
            }
            $this->fileUpload = null;
        } else {
            $reponse = $this->assistant()->analyzeUserRequest($userMessage);
        }

        $this->appendAssistantMessage($reponse);
    }

    public function genererRapport(string $period): void
    {
        $report = $this->assistant()->generateReport($period);
        $texte = $this->assistant()->formatReportText($report, 'Rapport ' . ucfirst($period));
        
        try {
            $excelUrl = $this->assistant()->generateAndExport($period, 'excel');
            $texte .= "\n\n📥 **[Télécharger le rapport Excel (XLSX)](" . $excelUrl . ")**";
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Erreur lors de la génération Excel : " . $e->getMessage());
        }

        $this->assistant()->createReportRecord('Rapport automatique ' . ucfirst($period), $texte);
        $this->appendAssistantMessage($texte);
    }

    public function detecterAnomalies(string $period = 'week'): void
    {
        $anomalies = $this->assistant()->detectAnomalies($period);
        $texte = $this->assistant()->formatAnomaliesText($anomalies, 'Analyse d\'anomalies ' . ucfirst($period));

        if ($anomalies['count'] > 0) {
            $this->assistant()->sendNotification(
                'Horizon RH – anomalie détectée',
                "Des anomalies ont été trouvées pendant la période {$anomalies['from']} à {$anomalies['to']}."
            );
        }

        $this->appendAssistantMessage($texte);
    }

    public function uploadFile(): void
    {
        if (!$this->fileUpload) {
            return;
        }

        $filePath = $this->fileUpload->store('horizon_uploads', 'public');

        $this->messages[] = [
            'sender' => 'ia',
            'text' => "Fichier reçu : {$this->fileUpload->getClientOriginalName()}. Je l'ai ajouté à la discussion.",
            'time' => now()->format('H:i'),
        ];

        $this->fileUpload = null;
    }

    public function toggleRecording(): void
    {
        $this->isRecording = !$this->isRecording;

        if ($this->isRecording) {
            $this->messages[] = [
                'sender' => 'ia',
                'text' => 'Mode vocal activé. Enregistrement en cours...',
                'time' => now()->format('H:i'),
            ];
        } else {
            $this->messages[] = [
                'sender' => 'ia',
                'text' => 'Enregistrement vocal arrêté. Le message vocal est prêt à être envoyé.',
                'time' => now()->format('H:i'),
            ];
        }
    }

    protected function appendAssistantMessage(string $text): void
    {
        $this->messages[] = [
            'sender' => 'ia',
            'text' => $text,
            'time' => now()->format('H:i'),
        ];
    }
}
