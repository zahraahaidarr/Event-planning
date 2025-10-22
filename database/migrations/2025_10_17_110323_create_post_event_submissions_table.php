<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('post_event_submissions', function (Blueprint $table) {
        $table->id('submission_id');

        $table->foreignId('event_id')
              ->constrained('events', 'event_id')->cascadeOnDelete();

        $table->foreignId('worker_id')
              ->constrained('workers', 'worker_id')->restrictOnDelete();

        $table->text('description')->nullable();
        $table->string('media_url')->nullable();
        $table->enum('status', ['SUBMITTED','APPROVED','REJECTED','DRAFT'])->default('DRAFT');

        $table->timestamps();

        $table->unique(['event_id', 'worker_id']); // one submission per worker per event
    });
}

public function down()
{
    Schema::dropIfExists('post_event_submissions');
}

};
