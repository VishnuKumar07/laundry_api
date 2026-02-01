<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorTermsCondition;
use Illuminate\Support\Facades\Validator;

class VendorTermsConditionController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'content' => 'required|string',
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
                    'message' => 'Vendor profile not found',
                ], 404);
            }

            $terms = VendorTermsCondition::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'content' => $request->content,
                ]
            );

            return response()->json([
                'status'  => true,
                'message' => 'Terms & Conditions saved successfully',
                'data'    => [
                    'vendor_id' => $vendor->id,
                    'content'   => $terms->content,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getTermsConditions(Request $request)
    {
        try {

            $vendor = Vendor::where('user_id', $request->user()->id)->first();

            if (!$vendor) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Vendor profile not found',
                    'data'    => []
                ], 404);
            }

            $terms = VendorTermsCondition::where('vendor_id', $vendor->id)
                ->first();

            if (!$terms) {
                return response()->json([
                    'status'  => true,
                    'message' => 'No terms & conditions added yet',
                    'data'    => [
                        'content' => ''
                    ]
                ], 200);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Terms & Conditions fetched successfully',
                'data'    => [
                    'content' => $terms->content
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getVendorTermsForCustomer(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'vendor_id' => 'required|exists:vendors,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $terms = VendorTermsCondition::where('vendor_id', $request->vendor_id)->first();

            if (!$terms) {
                return response()->json([
                    'status'  => true,
                    'message' => 'No terms & conditions available',
                    'data'    => [
                        'content' => ''
                    ]
                ], 200);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Terms & Conditions fetched successfully',
                'data'    => [
                    'content' => $terms->content
                ]
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
