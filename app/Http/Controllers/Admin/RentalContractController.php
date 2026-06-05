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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contract = RentalContract::with(['user', 'room'])->findOrFail($id);
        return response()->json($contract);
    }

    /**
     * Update status
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
