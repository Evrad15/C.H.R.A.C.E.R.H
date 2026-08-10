<?php

/**
 * Script de migration : Recalcule les embeddings ArcFace 512D depuis les photos
 * deja enregistrees dans storage/app/public/faces/*.jpg
 *
 * Remplace les anciens embeddings SFace 128D (ou aleatoires) par de vrais
 * vecteurs ArcFace (buffalo_l, 512 dimensions) bien plus discriminants.
 *
 * Usage :
 *   php scripts/migrate_embeddings.php
 */

// Bootstrap Laravel si execute en standalone
if (!function_exists('app')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

use App\Models\Employe;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

$scriptPath = base_path('scripts/face_recognize.py');
$facesDir   = storage_path('app/public/faces');

echo "=== Migration des embeddings ArcFace 512D (InsightFace buffalo_l) ===\n";
echo "Moteur : ArcFace R100 — Seuil cosinus 0.40 (anti-jumeaux)\n\n";

$employees = Employe::whereNotNull('consent_facial_at')->get();
$total     = $employees->count();
$success   = 0;
$failed    = 0;
$skipped   = 0;

foreach ($employees as $emp) {
    $photoPath = $facesDir . '/' . $emp->id . '.jpg';

    if (!file_exists($photoPath)) {
        echo "  [SKIP]  Employé #{$emp->id} ({$emp->first_name} {$emp->last_name}) — Photo absente\n";
        $skipped++;
        continue;
    }

    $cmd      = 'python ' . escapeshellarg($scriptPath) . ' extract ' . escapeshellarg($photoPath);
    $pyOutput = shell_exec($cmd);

    if (!$pyOutput) {
        echo "  [FAIL]  Employé #{$emp->id} — Python n'a retourné aucune sortie\n";
        $failed++;
        continue;
    }

    $pyData = json_decode(trim($pyOutput), true);

    if (!$pyData || ($pyData['status'] ?? '') !== 'success' || empty($pyData['embedding'])) {
        $msg = $pyData['message'] ?? 'Erreur inconnue';
        echo "  [FAIL]  Employé #{$emp->id} ({$emp->first_name} {$emp->last_name}) — {$msg}\n";
        $failed++;
        continue;
    }

    $embedding       = $pyData['embedding']; // tableau de 512 floats ArcFace
    $binaryEmbedding = pack('f*', ...$embedding);
    $emp->update(['face_embeddings' => base64_encode($binaryEmbedding)]);

    $dims = count($embedding);
    echo "  [OK]    Employe #{$emp->id} ({$emp->first_name} {$emp->last_name}) — Embedding ArcFace {$dims}D recalcule\n";
    $success++;
}

echo "\n=== Resultat ===\n";
echo "Total employes avec consentement : {$total}\n";
echo "Embeddings recalcules avec succes : {$success}\n";
echo "Ignores (photo absente)           : {$skipped}\n";
echo "Echecs (visage non detecte)        : {$failed}\n";
echo "\nMigration terminee. Moteur : InsightFace ArcFace 512D. Seuil : 0.40.\n";
