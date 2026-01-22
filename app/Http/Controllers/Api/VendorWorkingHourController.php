<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorWorkingHour;
use Illuminate\Support\Facades\Validator;

class VendorWorkingHourController extends Controller
{

    public function index(Request $request)
    {
        try {
            $vendor = Vendor::where('user_id', $request->user()->id)->first();

            if (!$vendor) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor profile not found'
                ], 404);
            }

            $daysOfWeek = [
                'monday', 'tuesday', 'wednesday',
                'thursday', 'friday', 'saturday', 'sunday'
            ];

            $workingHours = VendorWorkingHour::where('vendor_id', $vendor->id)
                ->get()
                ->keyBy('day');

            $result = [];

            foreach ($daysOfWeek as $day) {
                $dayData = $workingHours->get($day);

                $result[] = [
                    'day'        => $day,
                    'is_open'    => $dayData->is_open ?? true,
                    'open_time'  => $dayData->open_time ?? null,
                    'close_time' => $dayData->close_time ?? null,
                ];
            }

            return response()->json([
                'status' => true,
                'data'   => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'days' => 'required|array|min:1',
                    'days.*.day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                    'days.*.is_open' => 'required|boolean',
                    'days.*.open_time' => 'nullable|required_if:days.*.is_open,true|date_format:H:i',
                    'days.*.close_time' => 'nullable|required_if:days.*.is_open,true|date_format:H:i',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $vendor = Vendor::where('user_id', $request->user()->id)->first();
            if (!$vendor) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor profile not found'
                ], 404);
            }

            foreach ($request->days as $dayData) {
                VendorWorkingHour::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'day' => $dayData['day'],
                    ],
                    [
                        'is_open' => $dayData['is_open'],
                        'open_time' => $dayData['is_open'] ? $dayData['open_time'] : null,
                        'close_time' => $dayData['is_open'] ? $dayData['close_time'] : null,
                    ]
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Working hours updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
