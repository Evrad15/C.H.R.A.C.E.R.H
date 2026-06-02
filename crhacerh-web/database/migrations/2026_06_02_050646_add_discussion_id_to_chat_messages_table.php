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
        Schema::table('chat_messages', function (Blueprint $table) {
            // Assurez-vous que le type correspond à votre clé primaire (généralement unsignedBigInteger)
            $table->foreignId('discussion_id')->constrained('discussions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['discussion_id']);
            $table->dropColumn('discussion_id');
        });
    }
};
