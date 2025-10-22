<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('system_settings', function (Blueprint $table) {
        $table->id('setting_id');
        $table->string('key')->unique();
        $table->json('value')->nullable(); // or ->text('value')
        $table->timestamp('updated_at')->useCurrent();
    });
}

public function down()
{
    Schema::dropIfExists('system_settings');
}

};
