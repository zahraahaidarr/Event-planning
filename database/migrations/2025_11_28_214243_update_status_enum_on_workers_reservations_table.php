<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // add REJECTED to the enum list
        DB::statement("
            ALTER TABLE workers_reservations
            MODIFY status ENUM(
                'PENDING',
                'RESERVED',
                'CHECKED_IN',
                'CHECKED_OUT',
                'COMPLETED',
                'NO_SHOW',
                'CANCELLED',
                'REJECTED'
            ) NOT NULL DEFAULT 'PENDING'
        ");
    }

    public function down(): void
    {
        // rollback: remove REJECTED from the enum list
        DB::statement("
            ALTER TABLE workers_reservations
            MODIFY status ENUM(
                'PENDING',
                'RESERVED',
                'CHECKED_IN',
                'CHECKED_OUT',
                'COMPLETED',
                'NO_SHOW',
                'CANCELLED'
            ) NOT NULL DEFAULT 'PENDING'
        ");
    }
};
