<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert JSON translatable columns to plain strings for existing data
        $projects = DB::table('projects')->get();

        foreach ($projects as $project) {
            $updates = [];

            foreach (['title', 'description'] as $field) {
                $raw = $project->$field;
                if ($raw === null) continue;

                $decoded = json_decode($raw, true);

                if (is_array($decoded)) {
                    $value = $decoded['id'] ?? $decoded['en'] ?? array_values($decoded)[0] ?? '';
                    $updates[$field] = $value;
                }
            }

            if (!empty($updates)) {
                DB::table('projects')->where('id', $project->id)->update($updates);
            }
        }

        // Change title from json to string, description stays as text
        Schema::table('projects', function (Blueprint $table) {
            $table->string('title', 255)->change();
            $table->text('description')->change();
            $table->string('thumbnail_path', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('title')->change();
            $table->string('thumbnail_path', 500)->change();
        });
    }
};
