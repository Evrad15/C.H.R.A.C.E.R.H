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
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            
            // Clé étrangère vers la table des employés (ajustez 'employes' si votre table a un autre nom)
            $table->foreignId('employe_id')
                ->constrained('employés')
                ->cascadeOnDelete();
            
            // Le statut de la présence avec les 3 états requis pour votre tableau de bord
            $table->enum('statut', ['Present', 'Retard', 'Absent']);
            
            // Heure exacte du badgeage (nullable car un absent n'a pas d'heure d'arrivée)
            $table->time('heure_arrivee')->nullable();
            
            // timestamps génère 'created_at' et 'updated_at' (essentiel pour filtrer par jour/semaine)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};