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
        Schema::create('employés', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 50)->unique(); // Ajout du matricule unique
            $table->string('email', 150)->unique();
            $table->string('password'); // Recommandé pour l'Auth Laravel
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('role', 50)->default('Employés'); // Employee, Manager, RH_Admin

            // Clé étrangère vers les départements
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');

            $table->string('phone', 20)->nullable();
            $table->binary('face_embeddings')->nullable(); // Stockage binaire du vecteur IA (BYTEA)
            $table->timestamp('consent_facial_at')->nullable(); // RGPD Compliance
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Pour le "deleted_at" (Droit à l'oubli / RGPD)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employés');
    }
};

