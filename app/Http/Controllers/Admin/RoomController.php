<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\StoreRoomRequest;
use App\Http\Requests\Rooms\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/rooms",
     *     summary="Admin: Get all rooms",
     *     tags={"Admin Rooms"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="List of rooms for admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Room"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $query = Room::query()
            ->with([
                'amenities',
                'images' => fn ($query) => $query->orderBy('sort_order'),
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($builder) use ($keyword) {
                $builder->where('title', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhere('district', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%");
            });
        }

        $rooms = $query
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return RoomResource::collection($rooms);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/rooms",
     *     summary="Admin: Create a new room",
     *     tags={"Admin Rooms"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","price_monthly"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="district", type="string"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="price_monthly", type="integer"),
     *             @OA\Property(property="deposit_amount", type="integer"),
     *             @OA\Property(property="area_sqm", type="number", format="float"),
     *             @OA\Property(property="max_occupants", type="integer"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="amenities", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="images", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="url", type="string"),
     *                 @OA\Property(property="sort_order", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Room created",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Room")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreRoomRequest $request)
    {
        $data = $request->validated();

        $room = DB::transaction(function () use ($data, $request) {
            $room = Room::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'district' => $data['district'] ?? null,
                'city' => $data['city'] ?? null,
                'price_monthly' => $data['price_monthly'],
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'area_sqm' => $data['area_sqm'] ?? null,
                'max_occupants' => $data['max_occupants'] ?? null,
                'status' => $data['status'] ?? 'available',
                'created_by' => $request->user()->id,
            ]);

            if (! empty($data['amenities'])) {
                $room->amenities()->sync($data['amenities']);
            }

            if (! empty($data['images'])) {
                $images = collect($data['images'])
                    ->map(fn ($image) => new RoomImage([
                        'url' => $image['url'],
                        'sort_order' => $image['sort_order'] ?? 0,
                    ]));

                $room->images()->saveMany($images);
            }

            return $room;
        });

        return (new RoomResource($room->load([
            'amenities',
            'images' => fn ($query) => $query->orderBy('sort_order'),
        ])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/rooms/{room}",
     *     summary="Admin: Get single room details",
     *     tags={"Admin Rooms"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="room", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Room details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Room")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Room not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(Room $room)
    {
        return new RoomResource($room->load([
            'amenities',
            'images' => fn ($query) => $query->orderBy('sort_order'),
        ]));
    }

    /**
     * @OA\Put(
     *     path="/api/admin/rooms/{room}",
     *     summary="Admin: Update a room",
     *     tags={"Admin Rooms"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="room", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price_monthly", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Room")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Room not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateRoomRequest $request, Room $room)
    {
        $data = $request->validated();

        $room = DB::transaction(function () use ($data, $room) {
            $room->fill($data);
            $room->save();

            if (array_key_exists('amenities', $data)) {
                $room->amenities()->sync($data['amenities'] ?? []);
            }

            if (array_key_exists('images', $data)) {
                $room->images()->delete();
                $images = collect($data['images'] ?? [])
                    ->map(fn ($image) => new RoomImage([
                        'url' => $image['url'],
                        'sort_order' => $image['sort_order'] ?? 0,
                    ]));
                $room->images()->saveMany($images);
            }

            return $room;
        });

        return new RoomResource($room->load([
            'amenities',
            'images' => fn ($query) => $query->orderBy('sort_order'),
        ]));
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/rooms/{room}",
     *     summary="Admin: Delete a room",
     *     tags={"Admin Rooms"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="room", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Room deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Room not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json(['message' => 'Room deleted.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/rooms/{room}/status",
     *     summary="Admin: Update room status",
     *     tags={"Admin Rooms"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="room", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"available", "occupied"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Room")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Room not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied'],
        ]);

        $room->update(['status' => $validated['status']]);

        return new RoomResource($room->load(['amenities', 'images']));
    }
}
