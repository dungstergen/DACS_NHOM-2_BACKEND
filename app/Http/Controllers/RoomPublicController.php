<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomPublicController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::query()
            ->with([
                'amenities',
                'images' => fn ($query) => $query->orderBy('sort_order'),
            ]);

        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        if ($request->filled('district')) {
            $query->where('district', $request->input('district'));
        }

        if ($request->filled('price_min')) {
            $query->where('price_monthly', '>=', $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('price_monthly', '<=', $request->input('price_max'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', 'available');
        }

        if ($request->filled('area_min')) {
            $query->where('area_sqm', '>=', $request->input('area_min'));
        }

        if ($request->filled('area_max')) {
            $query->where('area_sqm', '<=', $request->input('area_max'));
        }

        if ($request->filled('max_occupants')) {
            $query->where('max_occupants', '>=', $request->input('max_occupants'));
        }

        if ($request->filled('amenities')) {
            $amenityIds = array_filter((array) $request->input('amenities'));
            $query->whereHas('amenities', function ($builder) use ($amenityIds) {
                $builder->whereIn('amenities.id', $amenityIds);
            });
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

        $rooms = $query->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return RoomResource::collection($rooms);
    }

    public function show(Room $room)
    {
        if ($room->status !== 'available') {
            abort(404);
        }

        return new RoomResource($room->load([
            'amenities',
            'images' => fn ($query) => $query->orderBy('sort_order'),
        ]));
    }
}
