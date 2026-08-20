<?php

namespace App\Console\Commands;

use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CloseDailyAppointmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdv:close-daily {--date= : Date spécifique (YYYY-MM-DD) à clôturer, défaut: aujourd\'hui} {--dry-run : Simuler sans modifier en base}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clôture automatiquement les rendez-vous en fin de journée (20h00) pour sécuriser et verrouiller les accès biométriques résiduels.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDateStr = $this->option('date') ?: Carbon::today()->toDateString();
        $isDryRun = (bool)$this->option('dry-run');

        $this->info("=== CLÔTURE AUTOMATIQUE DES RENDEZ-VOUS DU {$targetDateStr} ===");
        if ($isDryRun) {
            $this->warn("⚠️ Mode simulation activé (aucune modification ne sera effectuée).");
        }

        // 1. Clôturer les RDV non honorés (Planifié / Confirmé sans pointage)
        $unattendedRdvs = RendezVous::whereDate('date_rendez_vous', $targetDateStr)
            ->whereIn('statut', ['Planifié', 'Confirmé'])
            ->whereNull('pointage_at')
            ->get();

        $countUnattended = 0;
        foreach ($unattendedRdvs as $rdv) {
            $this->line("⏳ Patient non présenté : {$rdv->nom_patient} (RDV ID #{$rdv->id} à " . Carbon::parse($rdv->date_rendez_vous)->format('H:i') . ") -> Statut 'Non honoré'");
            if (!$isDryRun) {
                $rdv->update([
                    'statut' => 'Non honoré',
                    'notes'  => trim(($rdv->notes ?? '') . " | Clôture automatique 20h00 : Patient non présenté.")
                ]);
            }
            $countUnattended++;
        }

        // 2. Clôturer les RDV dont le patient a pointé présent mais non marqué Réalisé par le médecin
        $pendingCompletedRdvs = RendezVous::whereDate('date_rendez_vous', $targetDateStr)
            ->where('statut', 'Présent')
            ->whereNotNull('pointage_at')
            ->get();

        $countCompleted = 0;
        foreach ($pendingCompletedRdvs as $rdv) {
            $this->line("✅ Consultation à clôturer : {$rdv->nom_patient} (RDV ID #{$rdv->id}) -> Statut 'Réalisé'");
            if (!$isDryRun) {
                $rdv->update([
                    'statut' => 'Réalisé',
                    'notes'  => trim(($rdv->notes ?? '') . " | Clôture automatique 20h00 : Consultation terminée.")
                ]);
            }
            $countCompleted++;
        }

        $totalClosed = $countUnattended + $countCompleted;

        $this->info("\n📊 BILAN DE CLÔTURE DU {$targetDateStr} :");
        $this->info("   • {$countUnattended} rendez-vous marqués 'Non honoré' (Accès biométriques bloqués)");
        $this->info("   • {$countCompleted} rendez-vous marqués 'Réalisé' (Accès biométriques clôturés)");
        $this->info("   • Total rendez-vous traités : {$totalClosed}");

        Log::info("CloseDailyAppointmentsCommand exécuté pour le {$targetDateStr} : {$countUnattended} non honorés, {$countCompleted} réalisés clôturés.");

        return Command::SUCCESS;
    }
}
