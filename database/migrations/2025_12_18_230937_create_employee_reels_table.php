<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_reels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('video_path');
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->index(['employee_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_reels');
    }
};
