<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Room;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/appointments",
     *     summary="Get all appointments of authenticated user",
     *     tags={"Appointments"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="List of appointments",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Appointment"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'room.amenities',
                'room.images' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->orderByDesc('scheduled_at')
            ->paginate($request->integer('per_page', 15));

        return AppointmentResource::collection($appointments);
    }

    /**
     * @OA\Post(
     *     path="/api/appointments",
     *     summary="Create a new appointment",
     *     tags={"Appointments"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_id","scheduled_at"},
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="scheduled_at", type="string", format="date-time"),
     *             @OA\Property(property="note", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Appointment created",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation or conflict error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();

        $room = Room::findOrFail($data['room_id']);

        if ($room->status !== 'available') {
            return response()->json([
                'message' => 'Room is not available for viewing.',
            ], 422);
        }

        $conflict = Appointment::query()
            ->where('room_id', $room->id)
            ->where('scheduled_at', $data['scheduled_at'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'This time slot is already booked.',
            ], 422);
        }

        $appointment = Appointment::create([
            'room_id' => $room->id,
            'user_id' => $request->user()->id,
            'scheduled_at' => $data['scheduled_at'],
            'note' => $data['note'] ?? null,
            'status' => 'pending',
        ]);

        return (new AppointmentResource($appointment->load([
            'room.amenities',
            'room.images' => fn ($query) => $query->orderBy('sort_order'),
        ])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Patch(
     *     path="/api/appointments/{appointment}/cancel",
     *     summary="Cancel an appointment",
     *     tags={"Appointments"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="appointment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment cancelled",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=409, description="Already cancelled"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($appointment->status === 'cancelled') {
            return response()->json(['message' => 'Appointment already cancelled.'], 409);
        }

        $appointment->update(['status' => 'cancelled']);

        return new AppointmentResource($appointment->load([
            'room.amenities',
            'room.images' => fn ($query) => $query->orderBy('sort_order'),
        ]));
    }
}
