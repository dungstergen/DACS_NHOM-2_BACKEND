<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Room;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
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
