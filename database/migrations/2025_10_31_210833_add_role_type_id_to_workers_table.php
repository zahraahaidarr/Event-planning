<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_role_type_id_to_workers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('workers', function (Blueprint $table) {
            $table->unsignedBigInteger('role_type_id')->nullable()->after('user_id');

            // if your role_types PK is role_type_id (per your models)
            $table->foreign('role_type_id')
                  ->references('role_type_id')->on('role_types')
                  ->cascadeOnUpdate()->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropForeign(['role_type_id']);
            $table->dropColumn('role_type_id');
        });
    }
};
