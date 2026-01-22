<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Customer;
use Carbon\Carbon;

class CustomerHomeController extends Controller
{
    public function vendors(Request $request)
    {
        try {

            $customer = Customer::where('user_id', $request->user()->id)->first();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Customer profile not found'
                ], 404);
            }

            $favoriteVendorIds = $customer ? $customer->favoriteVendors()->pluck('vendors.id')->toArray() : [];

            $vendors = Vendor::with([
                    'user:id,first_name,last_name,role,primary_mobile,primary_email',
                    'address:id,vendor_id,door_no,street,landmark,pincode,city,state,country,address_type,company_image',
                    'workingHours:id,vendor_id,day,is_open,open_time,close_time',
                ])
                ->select(
                    'id',
                    'user_id',
                    'company_name',
                    'date_of_birth',
                    'date_of_incorporation',
                    'status',
                    'created_at'
                )
                ->get()
                ->map(function ($vendor) use ($favoriteVendorIds) {

                    $today = strtolower(Carbon::now()->format('l'));

                    $todayHours = $vendor->workingHours->firstWhere('day', $today);

                    $isOpenNow = null;
                    $openStatus = 'Not Updated';

                    if ($todayHours) {
                        if (!$todayHours->is_open) {
                            $isOpenNow = false;
                            $openStatus = 'Closed';
                        } elseif ($todayHours->open_time && $todayHours->close_time) {

                            $now = Carbon::now();
                            $openTime = Carbon::createFromTimeString($todayHours->open_time);
                            $closeTime = Carbon::createFromTimeString($todayHours->close_time);

                            if ($now->between($openTime, $closeTime)) {
                                $isOpenNow = true;

                                $minutesLeft = $now->diffInMinutes($closeTime, false);

                                $openStatus = ($minutesLeft <= 30)
                                    ? 'Closing Soon'
                                    : 'Open';
                            } else {
                                $isOpenNow = false;
                                $openStatus = 'Closed';
                            }
                        }
                    }


                    $years = $vendor->date_of_incorporation? (int) Carbon::parse($vendor->date_of_incorporation)->diffInYears(now()) : 0;

                    return [
                        'vendor_id'     => $vendor->id,
                        'company_name'  => $vendor->company_name ?? null,
                        'owner_name'    => trim(($vendor->user->first_name ?? '') . ' ' . ($vendor->user->last_name ?? '')),
                        'date_of_birth' => $vendor->date_of_birth ? Carbon::parse($vendor->date_of_birth)->format('d-m-Y') : null,
                        'date_of_incorporation' => $vendor->date_of_incorporation ? Carbon::parse($vendor->date_of_incorporation)->format('d-m-Y') : null,
                        'years_in_business' => $years > 0 ? $years . ($years == 1 ? ' Year in Business' : ' Years in Business') : 'New Business',
                        'company_status' => $vendor->status ?? null,
                        'door_no'       => $vendor->address->door_no ?? null,
                        'street'        => $vendor->address->street ?? null,
                        'landmark'      => $vendor->address->landmark ?? null,
                        'pincode'       => $vendor->address->pincode ?? null,
                        'city'          => $vendor->address->city ?? null,
                        'state'         => $vendor->address->state ?? null,
                        'country'       => $vendor->address->country ?? null,
                        'address_type'  => $vendor->address->address_type ?? null,
                        'is_favorite' => in_array($vendor->id, $favoriteVendorIds),
                        'is_open_now' => $isOpenNow,
                        'open_status' => $openStatus,
                        'company_images' => $vendor->address->company_image_urls ?? [],

                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Vendor list fetched successfully',
                'data'    => $vendors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
