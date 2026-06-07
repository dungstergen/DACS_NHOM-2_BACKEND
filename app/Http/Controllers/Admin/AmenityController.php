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
    /**
     * @OA\Get(
     *     path="/api/admin/amenities",
     *     summary="Admin: Get all amenities",
     *     tags={"Admin Amenities"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=50)),
     *     @OA\Response(
     *         response=200,
     *         description="List of amenities",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Amenity"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $amenities = Amenity::query()
            ->orderBy('name')
            ->paginate($request->integer('per_page', 50));

        return AmenityResource::collection($amenities);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/amenities",
     *     summary="Admin: Create a new amenity",
     *     tags={"Admin Amenities"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Amenity created",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Amenity")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreAmenityRequest $request)
    {
        $amenity = Amenity::create($request->validated());

        return (new AmenityResource($amenity))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/amenities/{amenity}",
     *     summary="Admin: Update an amenity",
     *     tags={"Admin Amenities"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="amenity", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Amenity updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Amenity")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Amenity not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateAmenityRequest $request, Amenity $amenity)
    {
        $amenity->update($request->validated());

        return new AmenityResource($amenity);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/amenities/{amenity}",
     *     summary="Admin: Delete an amenity",
     *     tags={"Admin Amenities"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="amenity", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Amenity deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Amenity not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return response()->json(['message' => 'Amenity deleted.']);
    }
}
