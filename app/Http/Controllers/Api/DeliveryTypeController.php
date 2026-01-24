<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryType;

class DeliveryTypeController extends Controller
{
    public function getDeliveryTypeList(Request $request)
    {
        try {
            $deliveryTypes = DeliveryType::where('status', 'active')
                ->select('id', 'name')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Delivery types fetched successfully',
                'data'    => $deliveryTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
