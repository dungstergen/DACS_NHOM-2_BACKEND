<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RentalContract;
use App\Models\Room;
use App\Http\Requests\Contracts\StoreContractRequest;
use Illuminate\Support\Facades\DB;

class RentalContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/admin/contracts",
     *     summary="Admin: Get all rental contracts",
     *     tags={"Admin Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="room_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="user_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of contracts",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RentalContract"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $query = RentalContract::with(['user', 'room']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $contracts = $query->latest()->paginate(10);
        return response()->json($contracts);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/contracts",
     *     summary="Admin: Create a new rental contract",
     *     tags={"Admin Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_id","user_id","start_date","end_date","monthly_rent","deposit_amount"},
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *             @OA\Property(property="monthly_rent", type="integer"),
     *             @OA\Property(property="deposit_amount", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"draft","active","expired","terminated"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contract created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="contract", ref="#/components/schemas/RentalContract")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Room not available"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreContractRequest $request)
    {
        try {
            DB::beginTransaction();

            $room = Room::findOrFail($request->room_id);
            if ($room->status !== 'available') {
                return response()->json(['message' => 'Phòng này đã được cho thuê hoặc không khả dụng'], 400);
            }

            $contract = RentalContract::create($request->validated());

            // Auto update room status to occupied when a contract is created
            $room->update(['status' => 'occupied']);

            DB::commit();

            return response()->json([
                'message' => 'Tạo hợp đồng thành công',
                'contract' => $contract
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi tạo hợp đồng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/contracts/{contract}",
     *     summary="Admin: Get contract details by ID",
     *     tags={"Admin Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="contract", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Rental contract details",
     *         @OA\JsonContent(ref="#/components/schemas/RentalContract")
     *     ),
     *     @OA\Response(response=404, description="Contract not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(string $id)
    {
        $contract = RentalContract::with(['user', 'room'])->findOrFail($id);
        return response()->json($contract);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/contracts/{contract}/status",
     *     summary="Admin: Update rental contract status",
     *     tags={"Admin Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="contract", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"draft","active","expired","terminated"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="contract", ref="#/components/schemas/RentalContract")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Contract not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:draft,active,expired,terminated'
        ]);

        $contract = RentalContract::findOrFail($id);
        $oldStatus = $contract->status;
        $newStatus = $request->status;
        
        $contract->update(['status' => $newStatus]);

        // If contract is expired or terminated, free up the room
        if (in_array($newStatus, ['expired', 'terminated']) && !in_array($oldStatus, ['expired', 'terminated'])) {
            $room = Room::find($contract->room_id);
            if ($room) {
                $room->update(['status' => 'available']);
            }
        }

        return response()->json([
            'message' => 'Cập nhật trạng thái hợp đồng thành công',
            'contract' => $contract
        ]);
    }
}
