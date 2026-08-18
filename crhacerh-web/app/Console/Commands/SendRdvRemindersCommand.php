<?php

namespace App\Console\Commands;

use App\Models\RendezVous;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendRdvRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdv:send-reminders {--force : Envoyer même si un rappel a déjà été envoyé aujourd\'hui}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie automatiquement les SMS de rappel J-1 pour tous les rendez-vous prévus demain.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $this->info("Scan des rendez-vous prévus pour demain ({$tomorrow})...");

        $rdvs = RendezVous::whereDate('date_rendez_vous', $tomorrow)
            ->whereNotIn('statut', ['Annulé', 'Réalisé'])
            ->whereNotNull('telephone_patient')
            ->get();

        if ($rdvs->isEmpty()) {
            $this->info("Aucun rendez-vous éligible trouvé pour demain.");
            return 0;
        }

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($rdvs as $rdv) {
            // Vérifier si un rappel J-1 a déjà été envoyé aujourd'hui
            $alreadyRemindedToday = $rdv->smsLogs()
                ->where('type', 'RAPPEL_J1')
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if ($alreadyRemindedToday && !$this->option('force')) {
                $this->line(" - Ignoré (déjà rappelé aujourd'hui) : RDV #{$rdv->id} ({$rdv->nom_patient})");
                $skippedCount++;
                continue;
            }

            $this->line(" + Envoi du rappel J-1 : RDV #{$rdv->id} - {$rdv->nom_patient} ({$rdv->telephone_patient})...");
            SmsService::sendReminder($rdv);
            $sentCount++;
        }

        $this->info("Traitement terminé : {$sentCount} rappel(s) envoyé(s), {$skippedCount} ignoré(s).");
        return 0;
    }
}
