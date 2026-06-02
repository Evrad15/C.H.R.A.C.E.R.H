<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Création de la fonction qui insère la discussion
        DB::unprepared('
            CREATE OR REPLACE FUNCTION create_discussion_for_new_employee()
            RETURNS TRIGGER AS $$
            BEGIN
                INSERT INTO discussions (sender_id, receiver_id, created_at, updated_at)
                VALUES (1, NEW.id, NOW(), NOW());
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Création du trigger qui appelle la fonction après chaque insertion
        DB::unprepared('
            CREATE TRIGGER after_employee_insert
            AFTER INSERT ON employés
            FOR EACH ROW
            EXECUTE FUNCTION create_discussion_for_new_employee();
        ');
    }

    public function down(): void
    {
        // Suppression du trigger et de la fonction si on annule la migration
        DB::unprepared('DROP TRIGGER IF EXISTS after_employee_insert ON employes;');
        DB::unprepared('DROP FUNCTION IF EXISTS create_discussion_for_new_employee;');
    }
};