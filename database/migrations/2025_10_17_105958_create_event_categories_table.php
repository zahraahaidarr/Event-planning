<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('event_categories', function (Blueprint $table) {
        $table->id('category_id');
        $table->string('name')->unique();
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('event_categories');
}

};
