<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');

            // Foreign keys must reference 'id' (the default PK in users)
            $table->foreignId('sender_id')
                  ->nullable()
                  ->constrained('users')   // references 'id' on 'users'
                  ->nullOnDelete();

            $table->foreignId('receiver_id')
                  ->nullable()
                  ->constrained('users')   // references 'id' on 'users'
                  ->nullOnDelete();

            $table->text('content');
            $table->timestamp('timestamp')->useCurrent();
            $table->boolean('is_read')->default(false);

            // Optional indexes (Laravel auto-creates them for foreignId but okay to keep)
            $table->index('sender_id');
            $table->index('receiver_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
