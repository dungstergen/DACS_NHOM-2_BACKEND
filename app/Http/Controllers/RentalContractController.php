<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RentalContract;
use Illuminate\Support\Facades\Auth;

class RentalContractController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/contracts",
     *     summary="Get all rental contracts of authenticated user",
     *     tags={"Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of contracts",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RentalContract"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
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
     * @OA\Get(
     *     path="/api/contracts/{contract}",
     *     summary="Get rental contract details by ID",
     *     tags={"Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="contract", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Rental contract details",
     *         @OA\JsonContent(ref="#/components/schemas/RentalContract")
     *     ),
     *     @OA\Response(response=404, description="Contract not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(string $id)
    {
        $contract = RentalContract::with(['room'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
            
        return response()->json($contract);
    }
}
