<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyBill extends Model
{
    protected $fillable = [
        'contract_id',
        'billing_month',
        'room_rent',
        'electricity_old',
        'electricity_new',
        'electricity_cost',
        'water_old',
        'water_new',
        'water_cost',
        'internet_cost',
        'trash_cost',
        'parking_cost',
        'total_amount',
        'status',
    ];

    public function contract()
    {
        return $this->belongsTo(RentalContract::class, 'contract_id');
    }
}
