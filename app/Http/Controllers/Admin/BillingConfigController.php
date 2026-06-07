<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillingConfig;

class BillingConfigController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/settings/billing",
     *     summary="Admin: Get billing config",
     *     tags={"Admin Settings"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Billing configurations",
     *         @OA\JsonContent(ref="#/components/schemas/BillingConfig")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show()
    {
        $config = BillingConfig::first();
        
        if (!$config) {
            // Create default if not exists
            $config = BillingConfig::create([
                'electricity_price' => 3500,
                'water_price' => 25000,
                'internet_price' => 100000,
                'trash_price' => 50000,
                'parking_price' => 100000,
            ]);
        }

        return response()->json($config);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/settings/billing",
     *     summary="Admin: Update billing config",
     *     tags={"Admin Settings"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"electricity_price","water_price","internet_price","trash_price","parking_price"},
     *             @OA\Property(property="electricity_price", type="integer"),
     *             @OA\Property(property="water_price", type="integer"),
     *             @OA\Property(property="internet_price", type="integer"),
     *             @OA\Property(property="trash_price", type="integer"),
     *             @OA\Property(property="parking_price", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Billing config updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="config", ref="#/components/schemas/BillingConfig")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'electricity_price' => 'required|numeric|min:0',
            'water_price' => 'required|numeric|min:0',
            'internet_price' => 'required|numeric|min:0',
            'trash_price' => 'required|numeric|min:0',
            'parking_price' => 'required|numeric|min:0',
        ]);

        $config = BillingConfig::first();
        if (!$config) {
            $config = new BillingConfig();
        }

        $config->fill($request->all());
        $config->save();

        return response()->json([
            'message' => 'Cập nhật cấu hình chi phí thành công',
            'config' => $config
        ]);
    }
}
