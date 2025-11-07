<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('starts_at')->nullable()->after('location');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->decimal('duration_hours', 5, 2)->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'ends_at', 'duration_hours']);
        });
    }
};
