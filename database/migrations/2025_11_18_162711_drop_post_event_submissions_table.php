<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('post_event_submissions');
    }

    public function down(): void
    {
        // recreate the table if you want rollback
        Schema::create('post_event_submissions', function (Blueprint $table) {
            $table->id();
            // add fields again if you want rollback to work
            $table->timestamps();
        });
    }
};
