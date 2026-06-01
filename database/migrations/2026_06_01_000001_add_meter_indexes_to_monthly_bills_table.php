<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_bills', function (Blueprint $table) {
            $table->decimal('electricity_old_index', 10, 2)
                ->default(0)
                ->after('electricity_units');
            $table->decimal('electricity_new_index', 10, 2)
                ->default(0)
                ->after('electricity_old_index');
            $table->decimal('water_old_index', 10, 2)
                ->default(0)
                ->after('water_units');
            $table->decimal('water_new_index', 10, 2)
                ->default(0)
                ->after('water_old_index');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_bills', function (Blueprint $table) {
            $table->dropColumn([
                'electricity_old_index',
                'electricity_new_index',
                'water_old_index',
                'water_new_index',
            ]);
        });
    }
};
