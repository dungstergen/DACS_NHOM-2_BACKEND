<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RentalContract;
use Illuminate\Support\Facades\Auth;

class RentalContractController extends Controller
{
    /**
     * Display a listing of the user's contracts.
     */
    public function index(Request $request)
    {
        $contracts = RentalContract::with(['room'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);
            
        return response()->json($contracts);
    }

    /**
     * Display the specified contract for the user.
     */
    public function show(string $id)
    {
        $contract = RentalContract::with(['room'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
            
        return response()->json($contract);
    }
}
