<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE conges DROP CONSTRAINT IF EXISTS conges_type_check;');
        DB::statement('ALTER TABLE conges DROP CONSTRAINT IF EXISTS conges_statut_check;');
        DB::statement('ALTER TABLE conges ALTER COLUMN type TYPE VARCHAR(100);');
        DB::statement('ALTER TABLE conges ALTER COLUMN statut TYPE VARCHAR(50);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
