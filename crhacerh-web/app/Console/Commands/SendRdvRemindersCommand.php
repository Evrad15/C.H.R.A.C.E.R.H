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
    protected $description = 'Envoie automatiquement les SMS de rappel échelonnés (J-6, J-3, J-1) pour les rendez-vous à venir.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== LANCEMENT DU CYCLE DE RAPPELS SMS (J-6, J-3, J-1) ===");

        // Paliers de rappel tous les 3 jours avant le RDV
        $reminderWindows = [
            6 => 'RAPPEL_J6', // 1er rappel : 6 jours avant
            3 => 'RAPPEL_J3', // 2ème rappel : 3 jours avant
            1 => 'RAPPEL_J1', // 3ème rappel : La veille (1 jour avant)
        ];

        $totalSent    = 0;
        $totalSkipped = 0;

        foreach ($reminderWindows as $days => $smsType) {
            $targetDate = Carbon::today()->addDays($days)->toDateString();
            $this->line("\n🔍 Scan des rendez-vous à J-{$days} (Date prévue : {$targetDate})...");

            $rdvs = RendezVous::whereDate('date_rendez_vous', $targetDate)
                ->whereNotIn('statut', ['Annulé', 'Réalisé'])
                ->whereNotNull('telephone_patient')
                ->get();

            if ($rdvs->isEmpty()) {
                $this->line("   ↳ Aucun rendez-vous prévu pour cette date.");
                continue;
            }

            foreach ($rdvs as $rdv) {
                // Vérifier si ce rappel spécifique a déjà été envoyé pour ce rendez-vous
                $alreadySent = $rdv->smsLogs()
                    ->where('type', $smsType)
                    ->exists();

                if ($alreadySent && !$this->option('force')) {
                    $this->line("   ↳ ⏩ Ignoré ({$smsType} déjà envoyé) : RDV #{$rdv->id} ({$rdv->nom_patient})");
                    $totalSkipped++;
                    continue;
                }

                $this->line("   ↳ 📲 Envoi {$smsType} : RDV #{$rdv->id} - {$rdv->nom_patient} ({$rdv->telephone_patient})...");
                SmsService::sendPeriodicReminder($rdv, $days);
                $totalSent++;
            }
        }

        $this->info("\n=== Rapport d'exécution : {$totalSent} rappel(s) envoyé(s), {$totalSkipped} déjà traités. ===");
        return 0;
    }
}
