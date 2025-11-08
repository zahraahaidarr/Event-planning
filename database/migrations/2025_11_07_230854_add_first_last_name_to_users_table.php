<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_first_last_name_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->index(['last_name','first_name']);
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['users_last_name_first_name_index']);
            $table->dropColumn(['first_name','last_name']);
        });
    }
};