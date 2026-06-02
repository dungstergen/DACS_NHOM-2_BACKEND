<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointments\UpdateAppointmentStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::query()
            ->with([
                'room.amenities',
                'room.images' => fn ($query) => $query->orderBy('sort_order'),
                'user',
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

        if ($request->filled('scheduled_from')) {
            $query->where('scheduled_at', '>=', $request->input('scheduled_from'));
        }

        if ($request->filled('scheduled_to')) {
            $query->where('scheduled_at', '<=', $request->input('scheduled_to'));
        }

        $appointments = $query
            ->orderByDesc('scheduled_at')
            ->paginate($request->integer('per_page', 20));

        return AppointmentResource::collection($appointments);
    }

    public function update(UpdateAppointmentStatusRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());

        return new AppointmentResource($appointment->load([
            'room.amenities',
            'room.images' => fn ($query) => $query->orderBy('sort_order'),
            'user',
        ]));
    }
}
