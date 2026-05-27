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
        Schema::create('ml_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('prediction_type', 50); // NO-SHOW, ABSENCE_PROBABLE
            $table->decimal('confidence', 3, 2); // Score entre 0.00 et 1.00 (Probabilité)
            $table->text('explanation')->nullable(); // Valeurs SHAP vulgarisées
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_predictions');
    }
};
