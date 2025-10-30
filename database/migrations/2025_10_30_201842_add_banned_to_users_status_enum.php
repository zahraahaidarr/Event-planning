<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        
        DB::statement("
            ALTER TABLE users
            MODIFY status ENUM('ACTIVE', 'SUSPENDED', 'BANNED', 'PENDING')
            NOT NULL DEFAULT 'PENDING'
        ");
    }

    public function down(): void
    {
        // Rollback: remove BANNED again if needed
        DB::statement("
            ALTER TABLE users
            MODIFY status ENUM('ACTIVE', 'SUSPENDED', 'PENDING')
            NOT NULL DEFAULT 'PENDING'
        ");
    }
};
