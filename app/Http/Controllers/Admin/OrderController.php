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
    /**
     * @OA\Get(
     *     path="/api/admin/orders",
     *     summary="Admin: Get all orders",
     *     tags={"Admin Orders"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="room_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="user_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=20)),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/admin/orders/{order}",
     *     summary="Admin: Get order details by ID",
     *     tags={"Admin Orders"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(Order $order)
    {
        return new OrderResource($order->load([
            'room.amenities',
            'room.images' => fn ($query) => $query->orderBy('sort_order'),
            'user',
            'payments',
        ]));
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/orders/{order}",
     *     summary="Admin: Update order status / payment details",
     *     tags={"Admin Orders"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending","paid","cancelled","refunded"}),
     *             @OA\Property(property="payment_method", type="string"),
     *             @OA\Property(property="payment_ref", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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
