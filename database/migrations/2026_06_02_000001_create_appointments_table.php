<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('appointments')) {
            return;
        }

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'scheduled_at'], 'idx_appointments_room_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
