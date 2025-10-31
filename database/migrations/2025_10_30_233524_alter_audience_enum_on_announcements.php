<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // If you already have data and "audience" exists as varchar, change it to enum:
        Schema::table('announcements', function (Blueprint $table) {
            $table->enum('audience', ['workers','employees','both'])
                  ->default('workers')
                  ->nullable(false)
                  ->change();
        });
    }

    public function down(): void
    {
        // Revert back to a string (adjust as your old type was)
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('audience', 50)->nullable(false)->default('workers')->change();
        });
    }
};
