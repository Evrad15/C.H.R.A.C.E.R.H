<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            // On ajoute le status ('PRESENT', 'RETARD', ou null pour les sorties)
            //On met 'PRESENT' par défaut pour ne pas casser tes anciens enregistrements
            $table->string('status')->default('Present')->after('type'); 
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};