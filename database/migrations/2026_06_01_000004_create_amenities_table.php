<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('amenities')) {
            return;
        }

        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
