<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('work_roles', function (Blueprint $table) {
        $table->id('role_id');

        $table->foreignId('event_id')
              ->constrained('events', 'event_id')->cascadeOnDelete();

        $table->foreignId('role_type_id')
              ->constrained('role_types', 'role_type_id')->restrictOnDelete();

        $table->string('role_name')->nullable(); // optional custom label
        $table->unsignedInteger('required_spots')->default(0);

        $table->enum('calc_source', ['AUTO','MANUAL','AUTO_EDITED'])->default('MANUAL');
        $table->decimal('calc_confidence', 3, 2)->nullable(); // 0.00 - 1.00

        $table->text('description')->nullable();
        $table->timestamps();

        $table->unique(['event_id', 'role_type_id']);
        $table->index('event_id');
    });
}

public function down()
{
    Schema::dropIfExists('work_roles');
}

};
