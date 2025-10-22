<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('role_types', function (Blueprint $table) {
        $table->id('role_type_id');
        $table->string('name')->unique(); // e.g., Photography, Organizer
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('role_types');
}

};
