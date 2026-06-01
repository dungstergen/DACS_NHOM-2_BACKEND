<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('room_amenities')) {
            return;
        }

        Schema::create('room_amenities', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('amenity_id');
            $table->primary(['room_id', 'amenity_id']);

            $table->foreign('room_id')
                ->references('id')
                ->on('rooms')
                ->onDelete('cascade');
            $table->foreign('amenity_id')
                ->references('id')
                ->on('amenities')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_amenities');
    }
};
