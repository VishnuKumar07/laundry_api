<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\VendorRating;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CustomerVendorRatingController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'vendor_id' => 'required|exists:vendors,id',
                    'rating'    => 'required|integer|min:1|max:5',
                    'review'    => 'nullable|string|max:500',
                ],
                [
                    'vendor_id.required' => 'Vendor Id is required',
                    'rating.required'    => 'Rating is required',
                    'rating.min'         => 'Rating must be at least 1',
                    'rating.max'         => 'Rating cannot exceed 5',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $customer = Customer::where('user_id', $request->user()->id)->first();
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Customer profile not found'
                ], 404);
            }

            VendorRating::updateOrCreate(
                [
                    'vendor_id'   => $request->vendor_id,
                    'customer_id' => $customer->id,
                ],
                [
                    'rating' => $request->rating,
                    'review' => $request->review,
                ]
            );

            return response()->json([
                'status'  => true,
                'message' => 'Thank you for rating the vendor'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function ratingList(Request $request)
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

            $ratings = VendorRating::with([
                    'customer.user:id,first_name,last_name'
                ])
                ->where('vendor_id', $request->vendor_id)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($rating) {
                    return [
                        'rating_id' => $rating->id,
                        'rating'    => $rating->rating,
                        'review'    => $rating->review,
                        'customer'  => trim(
                            ($rating->customer->user->first_name ?? '') . ' ' .
                            ($rating->customer->user->last_name ?? '')
                        ),
                        'rated_on'  => Carbon::parse($rating->created_at)->format('d M, Y'),
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Vendor ratings fetched successfully',
                'data'    => $ratings
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
