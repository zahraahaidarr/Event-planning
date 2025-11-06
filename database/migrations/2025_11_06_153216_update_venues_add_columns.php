<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            if (!Schema::hasColumn('venues', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('venues', 'city')) {
                $table->string('city')->nullable()->after('name');
            }
            if (!Schema::hasColumn('venues', 'area_m2')) {
                $table->decimal('area_m2', 8, 2)->default(0)->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['name', 'city', 'area_m2']);
        });
    }
};
