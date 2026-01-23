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
                    'status'  => false,
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

            $data = [];

            foreach ($daysOfWeek as $day) {
                if (isset($workingHours[$day])) {
                    $record = $workingHours[$day];

                    $data[] = [
                        'day'        => $day,
                        'is_open'    => (bool) $record->is_open,
                        'open_time'  => $record->open_time,
                        'close_time' => $record->close_time,
                    ];
                } else {
                    $data[] = [
                        'day'        => $day,
                        'is_open'    => false,
                        'open_time'  => null,
                        'close_time' => null,
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'days'                => 'required|array|min:1',
                'days.*.day'          => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'days.*.is_open'      => 'required|boolean',
                'days.*.open_time'    => 'nullable|required_if:days.*.is_open,true|date_format:H:i',
                'days.*.close_time'   => 'nullable|required_if:days.*.is_open,true|date_format:H:i',
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
                    'message' => 'Vendor profile not found'
                ], 404);
            }

            foreach ($request->days as $dayData) {

                // Extra safety: close time must be after open time
                if (
                    $dayData['is_open'] &&
                    strtotime($dayData['close_time']) <= strtotime($dayData['open_time'])
                ) {
                    return response()->json([
                        'status'  => false,
                        'message' => ucfirst($dayData['day']) . ' close time must be after open time'
                    ], 422);
                }

                VendorWorkingHour::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'day'       => $dayData['day'],
                    ],
                    [
                        'is_open'    => $dayData['is_open'],
                        'open_time'  => $dayData['is_open'] ? $dayData['open_time'] : null,
                        'close_time' => $dayData['is_open'] ? $dayData['close_time'] : null,
                    ]
                );
            }

            return response()->json([
                'status'  => true,
                'message' => 'Working hours updated successfully'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
