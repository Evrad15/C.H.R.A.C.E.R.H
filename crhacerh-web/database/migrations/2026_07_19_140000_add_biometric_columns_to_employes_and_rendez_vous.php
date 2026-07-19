<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employés', function (Blueprint $table) {
            if (!Schema::hasColumn('employés', 'fingerprint_data')) {
                $table->text('fingerprint_data')->nullable()->after('face_embeddings');
            }
            if (!Schema::hasColumn('employés', 'fingerprint_registered_at')) {
                $table->dateTime('fingerprint_registered_at')->nullable()->after('fingerprint_data');
            }
        });

        Schema::table('rendez_vous', function (Blueprint $table) {
            if (!Schema::hasColumn('rendez_vous', 'face_embeddings')) {
                $table->binary('face_embeddings')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('rendez_vous', 'consent_facial_at')) {
                $table->dateTime('consent_facial_at')->nullable()->after('face_embeddings');
            }
            if (!Schema::hasColumn('rendez_vous', 'fingerprint_data')) {
                $table->text('fingerprint_data')->nullable()->after('consent_facial_at');
            }
            if (!Schema::hasColumn('rendez_vous', 'fingerprint_registered_at')) {
                $table->dateTime('fingerprint_registered_at')->nullable()->after('fingerprint_data');
            }
            if (!Schema::hasColumn('rendez_vous', 'pointage_at')) {
                $table->dateTime('pointage_at')->nullable()->after('fingerprint_registered_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employés', function (Blueprint $table) {
            $table->dropColumn(['fingerprint_data', 'fingerprint_registered_at']);
        });

        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->dropColumn(['face_embeddings', 'consent_facial_at', 'fingerprint_data', 'fingerprint_registered_at', 'pointage_at']);
        });
    }
};
