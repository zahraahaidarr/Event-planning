<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('media_path'); // required
            $table->timestamp('expires_at'); // 24h etc
            $table->timestamps();

            $table->index(['employee_user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_stories');
    }
};
