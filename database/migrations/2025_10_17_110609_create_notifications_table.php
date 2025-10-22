<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');

            // Correct foreign key — references 'id' on 'users'
            $table->foreignId('user_id')
                  ->constrained('users')     // references 'id' on 'users'
                  ->cascadeOnDelete();

            $table->string('title');
            $table->text('message')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            // Optional but fine
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
