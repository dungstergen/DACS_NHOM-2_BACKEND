<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointments\UpdateAppointmentStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/appointments",
     *     summary="Admin: Get all appointments",
     *     tags={"Admin Appointments"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="room_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="user_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="scheduled_from", in="query", required=false, @OA\Schema(type="string", format="date-time")),
     *     @OA\Parameter(name="scheduled_to", in="query", required=false, @OA\Schema(type="string", format="date-time")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=20)),
     *     @OA\Response(
     *         response=200,
     *         description="List of appointments",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Appointment"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * @OA\Patch(
     *     path="/api/admin/appointments/{appointment}",
     *     summary="Admin: Update appointment status",
     *     tags={"Admin Appointments"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="appointment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "confirmed", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Appointment not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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
