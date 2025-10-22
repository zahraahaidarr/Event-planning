<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('workers_skills', function (Blueprint $table) {
        $table->foreignId('worker_id')
              ->constrained('workers', 'worker_id')->cascadeOnDelete();
        $table->foreignId('skill_id')
              ->constrained('skills', 'skill_id')->cascadeOnDelete();
        $table->string('level')->nullable();
        $table->primary(['worker_id', 'skill_id']);
    });
}

public function down()
{
    Schema::dropIfExists('workers_skills');
}

};
