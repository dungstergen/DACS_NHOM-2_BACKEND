<?php

namespace App\Http\Controllers;

use App\Http\Resources\AmenityResource;
use App\Models\Amenity;

class AmenityPublicController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/amenities",
     *     summary="Get all public amenities",
     *     tags={"Amenities"},
     *     @OA\Response(
     *         response=200,
     *         description="List of amenities",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Amenity"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        return AmenityResource::collection(
            Amenity::query()->orderBy('name')->get()
        );
    }
}
