<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    
    Schema::create('workers', function (Blueprint $table) {
        $table->id('worker_id');
        $table->foreignId('user_id')->unique()
              ->constrained('users', 'id')->restrictOnDelete();

        $table->enum('engagement_kind', ['VOLUNTEER','STIPENDED','PAID'])->default('VOLUNTEER');
        $table->boolean('is_volunteer')->default(true);
        $table->string('location')->nullable();
        $table->decimal('total_hours', 8, 2)->default(0);
        $table->enum('verification_status', ['UNVERIFIED','PENDING','VERIFIED'])->default('UNVERIFIED');
        $table->decimal('hourly_rate', 8, 2)->nullable();

        $table->enum('approval_status', ['PENDING','APPROVED','REJECTED','SUSPENDED'])->default('PENDING');
        $table->foreignId('approved_by')->nullable()
              ->constrained('employees', 'employee_id')->nullOnDelete();
        $table->timestamp('approved_at')->nullable();

        $table->date('joined_at')->nullable();
        $table->timestamps();

        $table->index('approval_status');
    });
}

public function down(): void
{
    Schema::dropIfExists('workers');
}


};
