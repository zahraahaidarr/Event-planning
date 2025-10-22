<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('announcements', function (Blueprint $table) {
        $table->id('announcement_id');
        $table->string('title');
        $table->text('body');

        $table->foreignId('posted_by')->nullable()
              ->constrained('employees', 'employee_id')->nullOnDelete();

        $table->string('audience')->default('ALL'); // ALL / WORKERS / EMPLOYEES / ADMINS / CUSTOM
        $table->timestamp('created_at')->useCurrent();
        $table->timestamp('expires_at')->nullable();
    });
}

public function down()
{
    Schema::dropIfExists('announcements');
}

};
