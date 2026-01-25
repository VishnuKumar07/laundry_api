<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorServiceType;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Customer;

class VendorServiceTypeController extends Controller
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

            $serviceTypes = VendorServiceType::with([
                    'service:id,name',
                    'deliveryType:id,name'
                ])
                ->where('vendor_id', $vendor->id)
                ->get()
                ->map(function ($row) {
                    return [
                        'id'                => $row->id,
                        'service_id'        => $row->service_id,
                        'service_name'      => $row->service->name ?? null,
                        'delivery_type_id'  => $row->delivery_type_id,
                        'delivery_type_name'=> $row->deliveryType->name ?? null,
                        'avg_delivery_days' => $row->avg_delivery_days,
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Service types fetched successfully',
                'data'    => $serviceTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function storeOrUpdate(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'service_type' => 'required|array|min:1',
                    'service_type.*.service_id' => 'required|exists:services,id',
                    'service_type.*.delivery_type_id' => 'required|exists:delivery_types,id',
                    'service_type.*.avg_delivery_days' => 'required|integer|min:1',
                ],
                [
                    'service_type.required' => 'Service list is required',
                    'service_type.*.service_id.required' => 'Service is required',
                    'service_type.*.delivery_type_id.required' => 'Delivery type is required',
                    'service_type.*.avg_delivery_days.required' => 'Average delivery time is required',
                ]
            );

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

            foreach ($request->service_type as $service) {
                VendorServiceType::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'service_id' => $service['service_id'],
                        'delivery_type_id' => $service['delivery_type_id'],
                    ],
                    [
                        'avg_delivery_days' => $service['avg_delivery_days'],
                    ]
                );
            }

            return response()->json([
                'status'  => true,
                'message' => 'Vendor services updated successfully'
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
