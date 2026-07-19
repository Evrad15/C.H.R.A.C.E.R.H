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
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->string('nom_patient', 150);
            $table->string('telephone_patient', 30)->nullable();
            $table->string('email_patient', 150)->nullable();
            $table->string('service', 100)->default('Chirurgie Endoscopique');
            $table->string('motif', 255);
            $table->dateTime('date_rendez_vous');
            $table->dateTime('fin_rendez_vous')->nullable();
            $table->string('medecin', 150)->nullable();
            $table->string('statut', 50)->default('Planifié'); // Planifié, Confirmé, Réalisé, Annulé, Reporté
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
