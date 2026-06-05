<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('rental_contracts')->onDelete('cascade');
            $table->string('billing_month', 7); // Format: YYYY-MM
            $table->decimal('room_rent', 12, 2);
            $table->integer('electricity_old');
            $table->integer('electricity_new');
            // Generated column for usage
            $table->integer('electricity_usage')->storedAs('electricity_new - electricity_old');
            $table->decimal('electricity_cost', 12, 2);
            $table->integer('water_old');
            $table->integer('water_new');
            // Generated column for usage
            $table->integer('water_usage')->storedAs('water_new - water_old');
            $table->decimal('water_cost', 12, 2);
            $table->decimal('internet_cost', 12, 2)->default(0);
            $table->decimal('trash_cost', 12, 2)->default(0);
            $table->decimal('parking_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_bills');
    }
};
