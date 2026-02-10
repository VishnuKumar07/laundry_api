<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorCoupon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'title'                     => 'required|string|max:255',
                'code'                      => 'required|string|max:50|unique:vendor_coupons,code',

                'from_date'                 => 'required|date',
                'to_date'                   => 'required|date|after_or_equal:from_date',

                'offer_type'                => 'required|in:percentage,amount',
                'offer_value'               => 'required|numeric|min:1',

                'max_discount_amount'       => 'nullable|numeric|min:0',
                'min_item_value'            => 'nullable|numeric|min:0',

                'coupon_limit_per_user'     => 'nullable|integer|min:1',
                'coupon_limit_overall'      => 'nullable|integer|min:1',

                'description'               => 'nullable|string',

                'images'                    => 'nullable|array',
                'images.*'                  => 'image|mimes:jpg,jpeg,png,webp|max:2048',

                'status'                    => 'required|in:active,inactive',
            ]);

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
                    'message' => 'Vendor not found',
                ], 404);
            }

            $imageNames = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $name = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/vendor/coupons'), $name);
                    $imageNames[] = $name;
                }
            }

            $coupon = VendorCoupon::create([
                'vendor_id'                => $vendor->id,
                'title'                    => $request->title,
                'code'                     => strtoupper($request->code),

                'from_date'                => $request->from_date,
                'to_date'                  => $request->to_date,

                'offer_type'               => $request->offer_type,
                'offer_value'              => $request->offer_value,

                'max_discount_amount'      => $request->max_discount_amount,
                'min_item_value'           => $request->min_item_value,

                'coupon_limit_per_user'    => $request->coupon_limit_per_user,
                'coupon_limit_overall'     => $request->coupon_limit_overall,

                'description'              => $request->description,
                'images'                   => $imageNames,

                'status'                   => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Coupon created successfully',
                'data' => $coupon,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {

            $vendor = Vendor::where('user_id', $request->user()->id)->first();

            if (!$vendor) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Vendor not found',
                ], 404);
            }

            $coupons = VendorCoupon::where('vendor_id', $vendor->id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($coupon) {

                    return [
                        'coupon_id'               => $coupon->id,
                        'title'                   => $coupon->title,
                        'code'                    => $coupon->code,

                        'from_date'               => $coupon->from_date?->format('d-m-Y'),
                        'to_date'                 => $coupon->to_date?->format('d-m-Y'),

                        'offer_type'              => $coupon->offer_type,
                        'offer_value'             => $coupon->offer_value,

                        'max_discount_amount'     => $coupon->max_discount_amount,
                        'min_item_value'          => $coupon->min_item_value,

                        'coupon_limit_per_user'   => $coupon->coupon_limit_per_user,
                        'coupon_limit_overall'    => $coupon->coupon_limit_overall,

                        'description'             => $coupon->description,
                        'images'                  => $coupon->image_urls,

                        'status'                  => $coupon->status,
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Coupons fetched successfully',
                'data'    => $coupons,
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
