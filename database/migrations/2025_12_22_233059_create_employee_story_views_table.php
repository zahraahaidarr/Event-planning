<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_story_views', function (Blueprint $table) {
            $table->id();

            // who viewed
            $table->foreignId('viewer_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // which story
            $table->foreignId('employee_story_id')
                  ->constrained('employee_stories')
                  ->cascadeOnDelete();

            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            // ✅ prevent duplicate view records
            $table->unique(['viewer_user_id', 'employee_story_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_story_views');
    }
};

