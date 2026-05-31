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
            // Pointage vers 'employés' au lieu de 'employees'
            $table->foreignId('employee_id')->constrained('employés')->onDelete('cascade');
            $table->date('date_start');
            $table->date('date_end');
            $table->string('type', 50); 
            $table->string('status', 20)->default('DEMANDE'); 
    
            // Personne qui approuve pointe aussi vers 'employés'
            $table->foreignId('approved_by')->nullable()->constrained('employés')->onDelete('set null');
    
            $table->string('justification_url')->nullable(); 
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
