<?php

namespace App\Http\Controllers;

use App\Http\Resources\AmenityResource;
use App\Models\Amenity;

class AmenityPublicController extends Controller
{
    public function index()
    {
        return AmenityResource::collection(
            Amenity::query()->orderBy('name')->get()
        );
    }
}
