<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // drop the old FK first
            $table->dropForeign(['created_by']);

            // make created_by nullable
            $table->unsignedBigInteger('created_by')->nullable()->change();

            // recreate FK, pointing to employees.employee_id with SET NULL on delete
            $table->foreign('created_by')
                  ->references('employee_id')->on('employees')
                  ->nullOnDelete();      // ON DELETE SET NULL
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['created_by']);

            // revert to NOT NULL if you want
            $table->unsignedBigInteger('created_by')->nullable(false)->change();

            $table->foreign('created_by')
                  ->references('employee_id')->on('employees');
        });
    }
};
