<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'address',
        'district',
        'city',
        'price_monthly',
        'deposit_amount',
        'area_sqm',
        'max_occupants',
        'status',
        'created_by',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'area_sqm' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities');
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
