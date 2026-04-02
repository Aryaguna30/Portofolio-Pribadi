<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // 'education' or 'experience' (SQLite has no ENUM)
            $table->json('institution');
            $table->json('role');
            $table->unsignedSmallInteger('start_year'); // YEAR type not supported in SQLite
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->json('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_entries');
    }
};
