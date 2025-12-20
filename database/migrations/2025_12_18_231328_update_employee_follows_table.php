<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_follows', function (Blueprint $table) {

            // ✅ DON'T add unique again (it already exists)

            // Only add this if you really need it:
            $table->index(['followed_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('employee_follows', function (Blueprint $table) {
            $table->dropIndex('employee_follows_followed_id_created_at_index');
        });
    }
};
