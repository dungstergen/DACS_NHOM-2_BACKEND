<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rooms')) {
            return;
        }

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('price_monthly', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('area_sqm', 8, 2)->nullable();
            $table->integer('max_occupants')->nullable();
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['city', 'district'], 'idx_rooms_location');
            $table->index('price_monthly', 'idx_rooms_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
