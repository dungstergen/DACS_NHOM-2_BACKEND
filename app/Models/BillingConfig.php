<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingConfig extends Model
{
    protected $fillable = [
        'electricity_price',
        'water_price',
        'internet_price',
        'trash_price',
        'parking_price',
    ];
}
