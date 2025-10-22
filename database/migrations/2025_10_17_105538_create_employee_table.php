<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('employees', function (Blueprint $table) {
        $table->id('employee_id');

        // make sure this points to users.id
        $table->foreignId('user_id')
              ->constrained('users', 'id')   // or simply ->constrained()
              ->restrictOnDelete();

        $table->string('position')->nullable();
        $table->string('department')->nullable();
        $table->date('hire_date')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        $table->unique('user_id'); // if each user can be employee only once
    });
}

public function down(): void
{
    Schema::dropIfExists('employees');
}


};
