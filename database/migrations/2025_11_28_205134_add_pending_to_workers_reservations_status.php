<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workers_reservations', function (Blueprint $table) {
            // Rebuild ENUM with PENDING added
            $table->enum('status', [
                'PENDING',
                'RESERVED',
                'CHECKED_IN',
                'CHECKED_OUT',
                'COMPLETED',
                'NO_SHOW',
                'CANCELLED',
            ])->default('PENDING')->change();
        });
    }

    public function down()
    {
        Schema::table('workers_reservations', function (Blueprint $table) {
            // Remove PENDING (restore original)
            $table->enum('status', [
                'RESERVED',
                'CHECKED_IN',
                'CHECKED_OUT',
                'COMPLETED',
                'NO_SHOW',
                'CANCELLED',
            ])->default('RESERVED')->change();
        });
    }
};
