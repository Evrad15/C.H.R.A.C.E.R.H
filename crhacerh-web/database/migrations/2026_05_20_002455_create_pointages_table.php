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
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employés')->onDelete('cascade');
            $table->string('type', 10); // ENTREE ou SORTIE
            $table->string('source', 20); // QR, FACE, BIO, PIN
            $table->timestamp('timestamp')->useCurrent();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->decimal('confidence_score', 3, 2)->nullable(); // Score IA
            $table->boolean('anomaly_detected')->default(false); // Isolation Forest target
            $table->timestamps();

            // Index pour booster les performances de l'API mobile et web
            $table->index('employee_id');
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};
