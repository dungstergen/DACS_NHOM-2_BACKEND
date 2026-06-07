<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\User;
use App\Models\MonthlyBill;
use App\Models\Order;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/dashboard/summary",
     *     summary="Admin: Get dashboard summary statistics",
     *     tags={"Admin Dashboard"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="month", in="query", required=false, description="Format: YYYY-MM", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="operations", type="object",
     *                 @OA\Property(property="total_rooms", type="integer"),
     *                 @OA\Property(property="available_rooms", type="integer"),
     *                 @OA\Property(property="occupied_rooms", type="integer"),
     *                 @OA\Property(property="total_users", type="integer")
     *             ),
     *             @OA\Property(property="finance", type="object",
     *                 @OA\Property(property="period", type="string"),
     *                 @OA\Property(property="total_revenue", type="integer"),
     *                 @OA\Property(property="pending_bills", type="integer"),
     *                 @OA\Property(property="total_deposits", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function summary(Request $request)
    {
        // 1. Chỉ số Vận hành (Operations)
        $total_rooms = Room::count();
        $available_rooms = Room::where('status', 'available')->count();
        $occupied_rooms = Room::where('status', 'occupied')->count();
        $total_users = User::where('role', 'user')->count();

        // 2. Chỉ số Tài chính (Finance)
        
        // Thống kê theo tháng hoặc toàn thời gian
        $month = $request->query('month'); // Format: YYYY-MM
        
        $billsQuery = MonthlyBill::query();
        $ordersQuery = Order::query()->where('status', 'paid');
        
        if ($month) {
            $billsQuery->where('billing_month', $month);
            $ordersQuery->whereMonth('created_at', substr($month, 5, 2))
                        ->whereYear('created_at', substr($month, 0, 4));
        }

        $total_revenue = (clone $billsQuery)->where('status', 'paid')->sum('total_amount');
        $pending_bills = (clone $billsQuery)->where('status', 'unpaid')->sum('total_amount');
        $total_deposits = $ordersQuery->sum('amount');

        return response()->json([
            'operations' => [
                'total_rooms' => $total_rooms,
                'available_rooms' => $available_rooms,
                'occupied_rooms' => $occupied_rooms,
                'total_users' => $total_users,
            ],
            'finance' => [
                'period' => $month ?? 'all_time',
                'total_revenue' => $total_revenue,
                'pending_bills' => $pending_bills,
                'total_deposits' => $total_deposits,
            ]
        ]);
    }
}
