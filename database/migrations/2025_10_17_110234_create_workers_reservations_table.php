<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('workers_reservations', function (Blueprint $table) {
        $table->id('reservation_id');

        $table->foreignId('event_id')
              ->constrained('events', 'event_id')->cascadeOnDelete();

        $table->foreignId('work_role_id')
              ->constrained('work_roles', 'role_id')->restrictOnDelete();

        $table->foreignId('worker_id')
              ->constrained('workers', 'worker_id')->restrictOnDelete();

        $table->timestamp('reserved_at')->useCurrent();
        $table->enum('status', ['RESERVED','CHECKED_IN','CHECKED_OUT','COMPLETED','NO_SHOW','CANCELLED'])->default('RESERVED');

        $table->timestamp('check_in_time')->nullable();
        $table->timestamp('check_out_time')->nullable();
        $table->decimal('credited_hours', 8, 2)->nullable();

        $table->timestamps();

        $table->unique(['work_role_id', 'worker_id']); // prevent duplicate active bookings
        $table->index(['work_role_id', 'status']);
        $table->index(['worker_id', 'status']);
    });
}

public function down()
{
    Schema::dropIfExists('workers_reservations');
}

};
