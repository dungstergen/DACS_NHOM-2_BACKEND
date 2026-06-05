<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_configs', function (Blueprint $table) {
            $table->id();
            $table->decimal('electricity_price', 10, 2)->default(0);
            $table->decimal('water_price', 10, 2)->default(0);
            $table->decimal('internet_price', 10, 2)->default(0);
            $table->decimal('trash_price', 10, 2)->default(0);
            $table->decimal('parking_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_configs');
    }
};
