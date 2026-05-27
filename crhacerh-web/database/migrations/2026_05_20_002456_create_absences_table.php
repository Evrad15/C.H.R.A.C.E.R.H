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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date_start');
            $table->date('date_end');
            $table->string('type', 50); // CONGE, MALADIE, SANS_SOLDE
            $table->string('status', 20)->default('DEMANDE'); // DEMANDE, APPROUVEE, REJETEE

            // Personne qui approuve (doit être un employé existant)
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');

            $table->string('justification_url')->nullable(); // Lien vers le certificat médical
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
