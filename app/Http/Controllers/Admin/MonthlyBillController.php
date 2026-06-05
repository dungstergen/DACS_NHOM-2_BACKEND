<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyBill;
use App\Models\RentalContract;
use App\Models\BillingConfig;
use Illuminate\Support\Facades\DB;

class MonthlyBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MonthlyBill::with(['contract.room', 'contract.user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('billing_month')) {
            $query->where('billing_month', $request->billing_month);
        }
        if ($request->has('contract_id')) {
            $query->where('contract_id', $request->contract_id);
        }

        $bills = $query->latest()->paginate(10);
        return response()->json($bills);
    }

    /**
     * Store a newly created bill in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:rental_contracts,id',
            'billing_month' => 'required|date_format:Y-m',
            'electricity_old' => 'required|integer|min:0',
            'electricity_new' => 'required|integer|gte:electricity_old',
            'water_old' => 'required|integer|min:0',
            'water_new' => 'required|integer|gte:water_old',
        ]);

        $contract = RentalContract::findOrFail($request->contract_id);

        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Hợp đồng không trong trạng thái active'], 400);
        }

        // Check if bill already exists for this month
        $existingBill = MonthlyBill::where('contract_id', $contract->id)
            ->where('billing_month', $request->billing_month)
            ->first();

        if ($existingBill) {
            return response()->json(['message' => 'Hóa đơn tháng này đã được tạo trước đó'], 400);
        }

        $config = BillingConfig::first();
        if (!$config) {
            return response()->json(['message' => 'Chưa cấu hình bảng giá điện/nước/phí'], 400);
        }

        try {
            DB::beginTransaction();

            $electricity_usage = $request->electricity_new - $request->electricity_old;
            $electricity_cost = $electricity_usage * $config->electricity_price;

            $water_usage = $request->water_new - $request->water_old;
            $water_cost = $water_usage * $config->water_price;

            $room_rent = $contract->monthly_rent;
            $internet_cost = $config->internet_price;
            $trash_cost = $config->trash_price;
            $parking_cost = $config->parking_price;

            $total_amount = $room_rent + $electricity_cost + $water_cost + $internet_cost + $trash_cost + $parking_cost;

            $bill = MonthlyBill::create([
                'contract_id' => $contract->id,
                'billing_month' => $request->billing_month,
                'room_rent' => $room_rent,
                'electricity_old' => $request->electricity_old,
                'electricity_new' => $request->electricity_new,
                'electricity_cost' => $electricity_cost,
                'water_old' => $request->water_old,
                'water_new' => $request->water_new,
                'water_cost' => $water_cost,
                'internet_cost' => $internet_cost,
                'trash_cost' => $trash_cost,
                'parking_cost' => $parking_cost,
                'total_amount' => $total_amount,
                'status' => 'unpaid'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tạo hóa đơn thành công',
                'bill' => $bill
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi tạo hóa đơn: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bill = MonthlyBill::with(['contract.room', 'contract.user'])->findOrFail($id);
        return response()->json($bill);
    }

    /**
     * Update bill status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:unpaid,paid'
        ]);

        $bill = MonthlyBill::findOrFail($id);
        $bill->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Cập nhật trạng thái hóa đơn thành công',
            'bill' => $bill
        ]);
    }
}
