<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'position')) {
                $table->dropColumn('position');
            }
            if (Schema::hasColumn('employees', 'department')) {
                $table->dropColumn('department');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // optional: re-add them if you rollback
            $table->string('position')->nullable();
            $table->string('department')->nullable();
        });
    }
};
