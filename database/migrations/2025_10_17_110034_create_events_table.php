<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('events', function (Blueprint $table) {
        $table->id('event_id');
        $table->string('title');
        $table->text('description')->nullable();

        $table->foreignId('category_id')->nullable()
              ->constrained('event_categories', 'category_id')->nullOnDelete();

        $table->string('location');
        $table->decimal('venue_area_sqm', 10, 2)->nullable();
        $table->unsignedInteger('expected_attendance')->nullable();

        $table->unsignedInteger('total_spots')->nullable();
        $table->enum('status', ['DRAFT','PUBLISHED','ACTIVE','COMPLETED','CANCELLED'])->default('DRAFT');
        $table->text('requirements')->nullable();

        $table->enum('staffing_mode', ['AUTO','MANUAL','AUTO_EDITED'])->default('MANUAL');
        $table->text('staffing_notes')->nullable();
        $table->string('staffing_model')->nullable();
        $table->timestamp('staffing_generated_at')->nullable();

        $table->foreignId('created_by')
              ->constrained('employees', 'employee_id')->restrictOnDelete();

        $table->timestamps();

        $table->index(['status', 'category_id']);
        $table->index('expected_attendance');
    });
}

public function down()
{
    Schema::dropIfExists('events');
}

};
