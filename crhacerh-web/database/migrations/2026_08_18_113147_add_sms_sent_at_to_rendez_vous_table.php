<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute sms_sent_at sur rendez_vous pour tracer l'envoi de notifications SMS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rendez_vous', function (Blueprint $table) {
            // Timestamp de dernier envoi SMS — null = jamais notifié
            $table->timestamp('sms_sent_at')->nullable()->after('pointage_at');

            // Compteur pour savoir combien de SMS ont été envoyés (rappels multiples)
            $table->unsignedTinyInteger('sms_count')->default(0)->after('sms_sent_at');

            // Index pour les requêtes de rappels automatiques (J-1)
            $table->index(['date_rendez_vous', 'sms_sent_at'], 'rdv_sms_reminder_idx');
        });
    }

    public function down(): void
    {
        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->dropIndex('rdv_sms_reminder_idx');
            $table->dropColumn(['sms_sent_at', 'sms_count']);
        });
    }
};
