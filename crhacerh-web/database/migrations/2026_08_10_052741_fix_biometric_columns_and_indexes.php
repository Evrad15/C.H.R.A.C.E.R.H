<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration correctrice biométrique ArcFace :
 *
 * 1. pointages.confidence_score : decimal(3,2) → decimal(8,6)
 *    Le score cosinus ArcFace a besoin de 6 décimales (ex: 0.747500)
 *
 * 2. Index sur employés.consent_facial_at + rendez_vous.consent_facial_at
 *    Accélère les recherches de candidats biométriques (WHERE consent_facial_at IS NOT NULL)
 *
 * 3. rendez_vous.nom_patient : colonne manquante utilisée par FaceApiController
 *
 * 4. employés.face_embedding_at : timestamp de mise à jour de l'embedding
 *    Permet de savoir si l'embedding est récent (< 1 an recommandé)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Précision du score cosinus ArcFace ──────────────────────────
        Schema::table('pointages', function (Blueprint $table) {
            $table->decimal('confidence_score', 8, 6)->nullable()->change();
        });

        // ── 2. Index performance sur consent_facial_at ─────────────────────
        Schema::table('rendez_vous', function (Blueprint $table) {
            // Colonne nom_patient manquante dans la migration d'origine
            if (!Schema::hasColumn('rendez_vous', 'nom_patient')) {
                $table->string('nom_patient', 200)->nullable()->after('patient_id');
            }
            // Index pour accélérer la recherche de candidats biométriques du jour
            $table->index(['date_rendez_vous', 'consent_facial_at'], 'rdv_biometric_search_idx');
        });

        // ── 3. Timestamp de l'embedding ArcFace sur employés ──────────────
        Schema::table('employés', function (Blueprint $table) {
            // Index sur consent_facial_at (requête 1:N ArcFace)
            $table->index('consent_facial_at', 'emp_consent_facial_idx');

            // Date de la dernière mise à jour de l'embedding (utile pour la rotation annuelle)
            if (!Schema::hasColumn('employés', 'face_embedding_updated_at')) {
                $table->timestamp('face_embedding_updated_at')->nullable()->after('consent_facial_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->decimal('confidence_score', 3, 2)->nullable()->change();
        });

        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->dropIndex('rdv_biometric_search_idx');
            if (Schema::hasColumn('rendez_vous', 'nom_patient')) {
                $table->dropColumn('nom_patient');
            }
        });

        Schema::table('employés', function (Blueprint $table) {
            $table->dropIndex('emp_consent_facial_idx');
            if (Schema::hasColumn('employés', 'face_embedding_updated_at')) {
                $table->dropColumn('face_embedding_updated_at');
            }
        });
    }
};
