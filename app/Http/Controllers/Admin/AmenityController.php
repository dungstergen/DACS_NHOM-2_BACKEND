<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Amenities\StoreAmenityRequest;
use App\Http\Requests\Amenities\UpdateAmenityRequest;
use App\Http\Resources\AmenityResource;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        $amenities = Amenity::query()
            ->orderBy('name')
            ->paginate($request->integer('per_page', 50));

        return AmenityResource::collection($amenities);
    }

    public function store(StoreAmenityRequest $request)
    {
        $amenity = Amenity::create($request->validated());

        return (new AmenityResource($amenity))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAmenityRequest $request, Amenity $amenity)
    {
        $amenity->update($request->validated());

        return new AmenityResource($amenity);
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return response()->json(['message' => 'Amenity deleted.']);
    }
}
