<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Carbon\Carbon;

class CustomerHomeController extends Controller
{
    public function vendors()
    {
        try
        {
            $vendors = Vendor::with([
                    'user:id,first_name,last_name,role,primary_mobile,primary_email',
                    'address:id,vendor_id,door_no,street,landmark,pincode,city,state,country,address_type,company_image',
                ])
                ->select('id', 'user_id', 'company_name','date_of_birth','date_of_incorporation','status','created_at')
                ->get()
                ->map(function ($vendor) {
                return [
                    'vendor_id'              => $vendor->id,
                    'company_name'           => $vendor->company_name ?? null,
                    'owner_name'             => trim(($vendor->user->first_name ?? '') . ' ' . ($vendor->user->last_name ?? '')),
                    'date_of_birth'          => $vendor->date_of_birth ? Carbon::parse($vendor->date_of_birth)->format('d-m-Y') : null,
                    'date_of_incorporation'  => $vendor->date_of_incorporation ? Carbon::parse($vendor->date_of_incorporation)->format('d-m-Y') : null,
                    'company_status'         => $vendor->status ?? null,
                    'door_no'                => $vendor->address->door_no ?? null,
                    'street'                 => $vendor->address->street ?? null,
                    'landmark'               => $vendor->address->landmark ?? null,
                    'pincode'                => $vendor->address->pincode ?? null,
                    'city'                   => $vendor->address->city ?? null,
                    'state'                  => $vendor->address->state ?? null,
                    'country'                => $vendor->address->country ?? null,
                    'address_type'           => $vendor->address->address_type ?? null,
                    'company_images'         => $vendor->address->company_image_urls ?? [],
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
