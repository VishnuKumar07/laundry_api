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

            if ($request->has('prices') && is_string($request->prices)) {
                $decodedPrices = json_decode($request->prices, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Invalid prices JSON format',
                    ], 422);
                }

                $request->merge([
                    'prices' => $decodedPrices
                ]);
            }

            $validator = Validator::make($request->all(), [
                'category_id'               => 'required|exists:categories,id',
                'name'                      => 'required|string|max:255',
                'description'               => 'nullable|string',

                'coupon_available'          => 'nullable|boolean',
                'effective_date'            => 'nullable|date',
                'position'                  => 'nullable|integer|min:0',

                'image'                     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

                'prices'                    => 'required|array|min:1',
                'prices.*.service_id'       => 'required|exists:services,id',
                'prices.*.delivery_type_id' => 'required|exists:delivery_types,id',
                'prices.*.mrp'              => 'required|numeric|min:0',
                'prices.*.selling_price'    => 'required|numeric|min:0|lte:prices.*.mrp',
                'prices.*.cgst'             => 'nullable|numeric|min:0|max:100',
                'prices.*.sgst'             => 'nullable|numeric|min:0|max:100',
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

            if ($request->filled('position') && (int)$request->position > 0) {

                $positionExists = VendorProduct::where('vendor_id', $vendor->id)
                    ->where('category_id', $request->category_id)
                    ->where('position', $request->position)->exists();

                if ($positionExists) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Position already exists. Please choose another position.',
                    ], 422);
                }
            }

            $imageName = null;

            if ($request->hasFile('image')) {
                $imageName = time() . '_' . uniqid() . '.' .
                            $request->image->getClientOriginalExtension();

                $request->image->move(
                    public_path('uploads/vendor/products'),
                    $imageName
                );
            }

            $product = VendorProduct::create([
                'vendor_id'   => $vendor->id,
                'category_id' => $request->category_id,
                'name'             => $request->name,
                'description'      => $request->description,
                'image'            => $imageName,
                'coupon_available' => $request->coupon_available ?? false,
                'effective_date'   => $request->effective_date,
                'position'         => $request->position ?? 0,
                'status'           => 'active',
            ]);

            foreach ($request->prices as $price) {
                VendorProductPrice::create([
                    'vendor_product_id' => $product->id,
                    'service_id'        => $price['service_id'],
                    'delivery_type_id'  => $price['delivery_type_id'],

                    'mrp'               => $price['mrp'],
                    'selling_price'     => $price['selling_price'],
                    'cgst'              => $price['cgst'] ?? 0,
                    'sgst'              => $price['sgst'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Product added successfully',
                'data'    => [
                    'product_id' => $product->id,
                    'name'       => $product->name,
                    'image_url'  => $product->image_url,
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

    public function vendorProducts(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'vendor_id'        => 'required|exists:vendors,id',
                'category_id'      => 'required|exists:categories,id',
                'delivery_type_id' => 'required|exists:delivery_types,id',
                'service_id'       => 'required|exists:services,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $products = VendorProduct::with([
                    'prices' => function ($q) use ($request) {
                        $q->where('delivery_type_id', $request->delivery_type_id)
                        ->where('service_id', $request->service_id)
                        ->with([
                            'service:id,name',
                            'deliveryType:id,name'
                        ]);
                    }
                ])
                ->where('vendor_id', $request->vendor_id)
                ->where('category_id', $request->category_id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('effective_date')
                    ->orWhere('effective_date', '<=', now()->toDateString());
                })
                ->whereHas('prices', function ($q) use ($request) {
                    $q->where('delivery_type_id', $request->delivery_type_id)
                    ->where('service_id', $request->service_id);
                })
                ->orderBy('position')
                ->get()
                ->map(function ($product) {

                    return [
                        'product_id'        => $product->id,
                        'product_name'      => $product->name,
                        'description'       => $product->description,
                        'coupon_available'  => (bool) $product->coupon_available,
                        'effective_date'    => $product->effective_date,
                        'position'          => $product->position,
                        'image_url'         => $product->image
                            ? asset('uploads/vendor/products/' . $product->image)
                            : null,

                        'prices' => $product->prices->map(function ($price) {

                            return [
                                'service_id'         => $price->service_id,
                                'service_name'       => $price->service->name ?? null,

                                'delivery_type_id'   => $price->delivery_type_id,
                                'delivery_type_name' => $price->deliveryType->name ?? null,

                                'mrp'                => $price->mrp,
                                'selling_price'      => $price->selling_price,
                                'cgst'               => $price->cgst,
                                'sgst'               => $price->sgst,
                            ];
                        })->values()
                    ];
                });

            if ($products->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No products found for selected service and delivery type',
                    'data'    => []
                ], 404);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Vendor products fetched successfully',
                'data'    => $products
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
