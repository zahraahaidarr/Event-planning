<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // add your extra fields
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['ADMIN','EMPLOYEE','WORKER'])
                  ->default('WORKER')
                  ->after('phone');
            $table->enum('status', ['ACTIVE','SUSPENDED','PENDING'])
                  ->default('PENDING')
                  ->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // remove what we added in up()
            $table->dropColumn(['phone', 'role', 'status']);
        });
    }

};
