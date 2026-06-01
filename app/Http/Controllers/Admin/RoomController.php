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

    public function show(Room $room)
    {
        return new RoomResource($room->load([
            'amenities',
            'images' => fn ($query) => $query->orderBy('sort_order'),
        ]));
    }

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

    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json(['message' => 'Room deleted.']);
    }

    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied'],
        ]);

        $room->update(['status' => $validated['status']]);

        return new RoomResource($room->load(['amenities', 'images']));
    }
}
