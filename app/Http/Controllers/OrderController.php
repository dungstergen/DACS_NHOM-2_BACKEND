<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Room;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'room.amenities',
                'room.images' => fn ($query) => $query->orderBy('sort_order'),
                'payments',
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        $room = Room::findOrFail($data['room_id']);

        if ($room->status !== 'available') {
            return response()->json([
                'message' => 'Room is not available for deposit.',
            ], 422);
        }

        $exists = Order::query()
            ->where('room_id', $room->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'paid'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'You already have an active order for this room.',
            ], 409);
        }

        $amount = $data['amount'] ?? $room->deposit_amount;

        $order = Order::create([
            'room_id' => $room->id,
            'user_id' => $request->user()->id,
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => $data['payment_method'] ?? null,
        ]);

        return (new OrderResource($order->load([
            'room.amenities',
            'room.images' => fn ($query) => $query->orderBy('sort_order'),
            'payments',
        ])))
            ->response()
            ->setStatusCode(201);
    }
}
