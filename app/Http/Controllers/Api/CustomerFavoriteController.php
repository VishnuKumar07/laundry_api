<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Support\Facades\Validator;

class CustomerFavoriteController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'vendor_id' => 'required|exists:vendors,id',
                ],
                [
                    'vendor_id.required' => 'Vendor Id is required',
                    'vendor_id.exists'   => 'Selected vendor does not exist',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $customer = Customer::where('user_id', $request->user()->id)
                ->firstOrFail();

            $alreadyFavorite = $customer->favoriteVendors()
                ->where('vendor_id', $request->vendor_id)
                ->exists();

            if ($alreadyFavorite) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Vendor already added to favorites'
                ], 409);
            }

            $customer->favoriteVendors()
                ->attach($request->vendor_id);

            return response()->json([
                'status'  => true,
                'message' => 'Vendor added to favorites'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'vendor_id' => 'required|exists:vendors,id',
                ],
                [
                    'vendor_id.required' => 'Vendor Id is required',
                    'vendor_id.exists'   => 'Selected vendor does not exist',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $customer = Customer::where('user_id', $request->user()->id)
                ->firstOrFail();

            $isFavorite = $customer->favoriteVendors()
                ->where('vendor_id', $request->vendor_id)
                ->exists();

            if (!$isFavorite) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Vendor not found in favorites'
                ], 404);
            }

            $customer->favoriteVendors()->detach($request->vendor_id);

            return response()->json([
                'status'  => true,
                'message' => 'Vendor removed from favorites'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
