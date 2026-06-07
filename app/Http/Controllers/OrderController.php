<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Room;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get all orders of authenticated user",
     *     tags={"Orders"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new deposit order",
     *     tags={"Orders"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_id"},
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="amount", type="integer", description="Leave empty to use default room deposit amount"),
     *             @OA\Property(property="payment_method", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Room is not available for deposit"),
     *     @OA\Response(response=409, description="Active order already exists for this room"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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
