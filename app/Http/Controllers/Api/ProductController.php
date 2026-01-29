<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Models\VendorProduct;
use App\Models\VendorProductPrice;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function addProduct(Request $request)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'category_id'                 => 'required|exists:categories,id',
                'name'                        => 'required|string|max:255',
                'prices'                      => 'required|array|min:1',
                'prices.*.service_id'         => 'required|exists:services,id',
                'prices.*.delivery_type_id'   => 'required|exists:delivery_types,id',
                'prices.*.mrp'                => 'required|numeric|min:0',
                'prices.*.discount_price'     => 'nullable|numeric|min:0|lte:prices.*.mrp',
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


            $exists = VendorProduct::where('vendor_id', $vendor->id)
                ->where('name', $request->name)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product already exists',
                ], 409);
            }


            $product = VendorProduct::create([
                'vendor_id'   => $vendor->id,
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'status'      => 'active',
            ]);


            foreach ($request->prices as $price) {
                VendorProductPrice::create([
                    'vendor_product_id' => $product->id,
                    'service_id'        => $price['service_id'],
                    'delivery_type_id'  => $price['delivery_type_id'],
                    'mrp'               => $price['mrp'],
                    'discount_price'    => $price['discount_price'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Product added successfully',
                'data'    => [
                    'product_id' => $product->id,
                    'name'       => $product->name
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
