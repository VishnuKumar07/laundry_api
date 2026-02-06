<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\VendorSetting;
use App\Models\Vendor;

class VendorSettingController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'service_radius'        => 'nullable|integer|min:1',
                'service_pincodes'      => 'nullable|array',
                'service_pincodes.*'    => 'digits:6',

                'status'                => 'required|in:active,inactive',
                'avg_delivery_time'     => 'nullable|integer|min:1',
                'years_in_business'     => 'nullable|integer|min:0',

                'online_payment'        => 'required|boolean',
                'cod'                   => 'required|boolean',

                'pre_booking'           => 'required|boolean',
                'pre_booking_days'      => 'nullable|integer|min:1|required_if:pre_booking,true',

                'free_delivery'         => 'required|boolean',
                'min_delivery_charge'   => 'nullable|numeric|min:0|required_if:free_delivery,false',
                'min_cart_value'        => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $vendor = Vendor::where('user_id', $request->user()->id)->first();

            if (!$vendor) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Vendor not found',
                ], 404);
            }

            $settings = VendorSetting::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'service_radius'      => $request->service_radius,
                    'service_pincodes'    => $request->service_pincodes,
                    'status'              => $request->status,
                    'avg_delivery_time'   => $request->avg_delivery_time,
                    'years_in_business'   => $request->years_in_business,

                    'online_payment'      => $request->online_payment,
                    'cod'                 => $request->cod,

                    'pre_booking'         => $request->pre_booking,
                    'pre_booking_days'    => $request->pre_booking_days,

                    'free_delivery'       => $request->free_delivery,
                    'min_delivery_charge' => $request->min_delivery_charge,
                    'min_cart_value'      => $request->min_cart_value,
                ]
            );

            return response()->json([
                'status'  => true,
                'message' => 'Vendor settings saved successfully',
                'data'    => $settings,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function get(Request $request)
    {
        $vendor = Vendor::where('user_id', $request->user()->id)->first();

        if (!$vendor) {
            return response()->json([
                'status'  => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $settings = VendorSetting::where('vendor_id', $vendor->id)->first();

        return response()->json([
            'status'  => true,
            'message' => 'Vendor settings fetched successfully',
            'data'    => $settings ?? (object) [],
        ], 200);
    }

}
