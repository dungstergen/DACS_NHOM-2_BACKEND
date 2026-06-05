<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyBill;
use Illuminate\Support\Facades\Auth;

class MonthlyBillController extends Controller
{
    /**
     * Display a listing of the user's bills.
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
     * Display the specified bill for the user.
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
