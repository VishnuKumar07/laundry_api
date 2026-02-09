<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Carbon\Carbon;

class MyProfileController extends Controller
{
    public function getMyProfile(Request $request)
    {
        try {

            $vendor = Vendor::with([
                    'user:id,first_name,last_name,primary_mobile,secondary_mobile,primary_email,secondary_email'
                ])
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$vendor) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Vendor profile not found',
                ], 404);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Profile fetched successfully',
                'data'    => [
                    'first_name'             => $vendor->user->first_name ?? null,
                    'last_name'              => $vendor->user->last_name ?? null,

                    'company_name'           => $vendor->company_name ?? null,

                    'primary_mobile_number'  => $vendor->user->primary_mobile ?? null,
                    'secondary_mobile_number'=> $vendor->user->secondary_mobile ?? null,

                    'primary_email'          => $vendor->user->primary_email ?? null,
                    'secondary_email'        => $vendor->user->secondary_email ?? null,

                    'date_of_birth'          => $vendor->date_of_birth
                        ? Carbon::parse($vendor->date_of_birth)->format('d M, Y')
                        : null,

                    'date_of_incorporation'  => $vendor->date_of_incorporation
                        ? Carbon::parse($vendor->date_of_incorporation)->format('d M, Y')
                        : null,
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
