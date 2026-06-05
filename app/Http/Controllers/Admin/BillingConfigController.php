<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillingConfig;

class BillingConfigController extends Controller
{
    /**
     * Display the billing config.
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
     * Update the billing config.
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
