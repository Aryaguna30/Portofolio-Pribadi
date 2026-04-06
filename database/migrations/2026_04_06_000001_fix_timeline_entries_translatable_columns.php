<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert JSON translatable columns to plain strings
        $entries = DB::table('timeline_entries')->get();

        foreach ($entries as $entry) {
            $updates = [];

            foreach (['institution', 'role', 'description'] as $field) {
                $raw = $entry->$field;
                if ($raw === null) continue;

                $decoded = json_decode($raw, true);

                // If it's a JSON object like {"en":"..."}, extract the value
                if (is_array($decoded)) {
                    $value = $decoded['id'] ?? $decoded['en'] ?? array_values($decoded)[0] ?? '';
                    $updates[$field] = $value;
                }
                // If it's already a plain string, leave it
            }

            if (!empty($updates)) {
                DB::table('timeline_entries')
                    ->where('id', $entry->id)
                    ->update($updates);
            }
        }

        // Change columns from json to text
        Schema::table('timeline_entries', function (Blueprint $table) {
            $table->text('institution')->change();
            $table->text('role')->change();
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('timeline_entries', function (Blueprint $table) {
            $table->json('institution')->change();
            $table->json('role')->change();
            $table->json('description')->nullable()->change();
        });
    }
};
