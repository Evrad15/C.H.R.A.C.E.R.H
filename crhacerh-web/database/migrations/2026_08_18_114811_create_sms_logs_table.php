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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rendez_vous_id')->nullable()->constrained('rendez_vous')->onDelete('set null');
            $table->string('patient_name', 150)->nullable();
            $table->string('phone', 30);
            $table->string('type', 40)->default('CONFIRMATION'); // CONFIRMATION, RAPPEL_J1, ANNULATION, REPORTE, MANUEL
            $table->text('message');
            $table->string('status', 30)->default('SENT'); // SENT, FAILED, SIMULATED
            $table->text('response_details')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('phone');
            $table->index('type');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
