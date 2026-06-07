<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyBill;
use Illuminate\Support\Facades\Auth;

class MonthlyBillController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/bills",
     *     summary="Get all bills of authenticated user",
     *     tags={"Bills"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of bills",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MonthlyBill"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $bills = MonthlyBill::with(['contract.room'])
            ->whereHas('contract', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(10);
            
        return response()->json($bills);
    }

    /**
     * @OA\Get(
     *     path="/api/bills/{bill}",
     *     summary="Get monthly bill details by ID",
     *     tags={"Bills"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="bill", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Monthly bill details",
     *         @OA\JsonContent(ref="#/components/schemas/MonthlyBill")
     *     ),
     *     @OA\Response(response=404, description="Bill not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(string $id)
    {
        $bill = MonthlyBill::with(['contract.room'])
            ->whereHas('contract', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->findOrFail($id);
            
        return response()->json($bill);
    }
}
