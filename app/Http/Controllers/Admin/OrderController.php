<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()
            ->with([
                'room.amenities',
                'room.images' => fn ($query) => $query->orderBy('sort_order'),
                'user',
                'payments',
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $orders = $query
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 20));

        return OrderResource::collection($orders);
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load([
            'room.amenities',
            'room.images' => fn ($query) => $query->orderBy('sort_order'),
            'user',
            'payments',
        ]));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $data = $request->validated();
        $previousStatus = $order->status;

        $order->update([
            'status' => $data['status'],
            'payment_method' => $data['payment_method'] ?? $order->payment_method,
            'payment_ref' => $data['payment_ref'] ?? $order->payment_ref,
        ]);

        if ($previousStatus !== 'paid' && $data['status'] === 'paid') {
            Payment::create([
                'order_id' => $order->id,
                'amount' => $order->amount,
                'status' => 'success',
                'provider' => $order->payment_method,
                'transaction_id' => $order->payment_ref,
                'paid_at' => now(),
            ]);
        }

        return new OrderResource($order->load([
            'room.amenities',
            'room.images' => fn ($query) => $query->orderBy('sort_order'),
            'user',
            'payments',
        ]));
    }
}
