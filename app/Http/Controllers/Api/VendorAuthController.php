<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorAddress;
use App\Models\VendorDocument;
use App\Models\OtpLog;

class VendorAuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [

                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'role' => 'required|in:vendor,branch,customer',
                'primary_mobile' => 'required|digits:10|unique:users,primary_mobile',
                'secondary_mobile' => 'nullable|digits:10',
                'primary_email' => 'nullable|email|unique:users,primary_email',
                'secondary_email' => 'nullable|email',
                'password' => 'required|string|min:6',
                'confirm_password' => 'required|same:password',
                'date_of_birth' => 'nullable|date',
                'date_of_incorporation' => 'required|date',
                'pincode' => 'nullable|digits:6',
                'address_type' => 'required|in:shop,office,factory,others',
                'company_image' => 'nullable|array|max:5',
                'company_image.*' => 'image|mimes:jpg,jpeg,png|max:2048',
                'supporting_document' => 'required|array|min:1|max:15',
                'supporting_document.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',

            ],
            [
                'first_name.required' => 'First name is required',
                'last_name.required' => 'Last name is required',
                'role.required' => 'Role is required',
                'role.in' => 'Role must be vendor, branch, or customer',
                'primary_mobile.required' => 'Mobile number is required',
                'primary_mobile.digits' => 'Mobile number must be exactly 10 digits',
                'primary_mobile.unique' => 'Mobile number already exists',
                'secondary_mobile.digits' => 'Secondary mobile number must be exactly 10 digits',
                'primary_email.unique' => 'Email address already exists',
                'secondary_email.email' => 'Please enter a valid secondary email address',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 6 characters',
                'confirm_password.required' => 'Confirm password is required',
                'confirm_password.same' => 'Password and confirm password do not match',
                'date_of_birth.date' => 'Please enter a valid date of birth',
                'date_of_incorporation.required' => 'Date of incorporation is required',
                'date_of_incorporation.date' => 'Please enter a valid date of incorporation',
                'pincode.digits' => 'Pincode must be exactly 6 digits',
                'address_type.required' => 'Address type is required',
                'address_type.in' => 'Address type must be shop, office, factory, or others',
                'company_image.array' => 'Company images must be an array',
                'company_image.max' => 'You can upload maximum 5 company images',
                'company_image.*.image' => 'Each company image must be an image file',
                'company_image.*.mimes' => 'Company images must be JPG or PNG',
                'company_image.*.max' => 'Each company image must not exceed 2MB',
                'supporting_document.required' => 'Supporting documents are required',
                'supporting_document.array' => 'Supporting documents must be an array',
                'supporting_document.min' => 'Please upload at least one supporting document',
                'supporting_document.max' => 'You can upload maximum 15 supporting documents',
                'supporting_document.*.file' => 'Each supporting document must be a valid file',
                'supporting_document.*.mimes' => 'Supporting documents must be JPG, JPEG, PNG, or PDF files',
                'supporting_document.*.max' => 'Each supporting document must not exceed 5MB',

            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $otp = 1234;

            $user = User::create([
                'role' => $request->role,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'primary_mobile' => $request->primary_mobile,
                'secondary_mobile' => $request->secondary_mobile,
                'primary_email' => $request->primary_email,
                'secondary_email' => $request->secondary_email,
                'password' => Hash::make($request->password),
                'sample_pass' => $request->password,
                'status' => 1,
                'otp_code'       => $otp,
                'otp_sent_at'    => now(),
                'otp_expires_at' => now()->addMinutes(5),
            ]);

            OtpLog::create([
                'user_id'        => $user->id,
                'identifier'     => $user->primary_mobile,
                'channel'        => 'sms',
                'otp_code'       => $otp,
                'purpose'        => 'signup',
                'status'         => 'sent',
                'ip_address'     => $request->ip(),
                'sent_at'        => now(),
            ]);

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name ?? null,
                'date_of_birth' => $request->date_of_birth,
                'date_of_incorporation' => $request->date_of_incorporation,
            ]);

            $companyImagePaths = [];

            if ($request->hasFile('company_image')) {
                $images = $request->file('company_image');
                $folder = 'uploads/vendor/company_image';

                if (!file_exists(public_path($folder))) {
                    mkdir(public_path($folder), 0755, true);
                }

                foreach ($images as $image) {
                    $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path($folder), $fileName);
                    $companyImagePaths[] = $folder . '/' . $fileName;
                }
            }

            $vendorAddress = VendorAddress::create([
                'vendor_id'     => $vendor->id,
                'door_no'       => $request->door_no ?? null,
                'street'        => $request->street ?? null,
                'landmark'      => $request->landmark ?? null,
                'pincode'       => $request->pincode ?? null,
                'city'          => $request->city ?? null,
                'state'         => $request->state ?? null,
                'country'       => $request->country ?? null,
                'latitude'      => $request->latitude ?? null,
                'longitude'     => $request->longitude ?? null,
                'address_type'  => $request->address_type,
                'company_image' => !empty($companyImagePaths) ? json_encode($companyImagePaths) : null

            ]);

            if ($request->hasFile('supporting_document')) {

                $documents = $request->file('supporting_document');
                $folder = 'uploads/vendor/documents';

                if (!file_exists(public_path($folder))) {
                    mkdir(public_path($folder), 0755, true);
                }

                foreach ($documents as $document) {

                    $fileName = time() . '_' . uniqid() . '.' . $document->getClientOriginalExtension();
                    $document->move(public_path($folder), $fileName);

                    VendorDocument::create([
                        'vendor_id'        => $vendor->id,
                        'document_path'    => $folder . '/' . $fileName,
                        'original_file_name' => $document->getClientOriginalName(),
                        'mime_type'        => $document->getClientMimeType(),
                        'status'           => 'pending',
                        'remarks'          => null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Signup successful. OTP sent to registered mobile number.',
                'data' => [
                    'user_id' => $user->id,
                    'primary_mobile' => $user->primary_mobile,
                    'otp_expires_in' => 300
                ]
            ], 201);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function signupSendOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'primary_mobile' => 'required|digits:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid mobile number',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $user = User::where('primary_mobile', $request->primary_mobile)->first();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Mobile number not registered'
                ], 404);
            }

            if ($user->primary_mobile_verified_at) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Mobile number already verified'
                ], 400);
            }

            $otp = 1234;

            $user->update([
                'otp_code'       => $otp,
                'otp_expires_at' => now()->addMinutes(5),
                'otp_sent_at'    => now(),
            ]);

            OtpLog::create([
                'user_id'        => $user->id,
                'identifier'     => $user->primary_mobile,
                'channel'        => 'sms',
                'otp_code'       => $otp,
                'purpose'        => 'signup',
                'status'         => 'sent',
                'ip_address'     => $request->ip(),
                'sent_at'        => now(),
            ]);

            return response()->json([
                'status'      => true,
                'message'     => 'OTP sent successfully',
                'expires_in'  => 300
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while sending OTP',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function signupResendOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'primary_mobile' => 'required|digits:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid mobile number',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('primary_mobile', $request->primary_mobile)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mobile number not found'
                ], 404);
            }

            if ($user->otp_sent_at && Carbon::parse($user->otp_sent_at)->addSeconds(60)->isFuture())
            {
                return response()->json([
                    'status' => false,
                    'message' => 'Please wait before requesting another OTP'
                ], 429);
            }

            $otp = 1234;

            $user->update([
                'otp_code'       => $otp,
                'otp_sent_at'    => now(),
                'otp_expires_at' => now()->addMinutes(5),
            ]);

            OtpLog::create([
                'user_id'        =>  $user->id,
                'identifier'     =>  $user->primary_mobile,
                'channel'        =>  'sms',
                'otp_code'       =>  $otp,
                'purpose'        =>  'signup_resend',
                'status'         =>  'sent',
                'ip_address'     =>  $request->ip(),
                'sent_at'        =>  now(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'OTP resent successfully',
                'expires_in' => 300
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function signupVerifyOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'primary_mobile' => 'required|digits:10',
                'otp'            => 'required|digits:4',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = User::where('primary_mobile', $request->primary_mobile)->first();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Mobile number not found',
                ], 404);
            }

            if ($user->primary_mobile_verified_at) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Mobile number already verified',
                ], 400);
            }

            if (!$user->otp_code || !$user->otp_expires_at) {
                return response()->json([
                    'status'  => false,
                    'message' => 'OTP not generated. Please request OTP again.',
                ], 400);
            }

            if (Carbon::now()->greaterThan(Carbon::parse($user->otp_expires_at))) {
                return response()->json([
                    'status'  => false,
                    'message' => 'OTP has expired. Please request a new OTP.',
                ], 410);
            }

            if ($user->otp_code !== $request->otp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid OTP',
                ], 401);
            }

            OtpLog::where('user_id', $user->id)->where('otp_code', $request->otp)
            ->whereIn('purpose', ['signup', 'signup_resend'])
            ->where('status', 'sent')
            ->orderByDesc('id')
            ->first()
            ?->update([
                'status'      => 'verified',
                'verified_at' => now(),
            ]);

            $user->update([
                'primary_mobile_verified_at' => Carbon::now(),
                'otp_code'        => null,
                'otp_expires_at'  => null,
                'otp_sent_at'     => null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Mobile number verified successfully',
                'data'    => [
                    'user_id' => $user->id,
                    'role'    => $user->role,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function forgotPasswordSendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid input',
                    'errors' => $validator->errors()
                ], 422);
            }

            $username = $request->username;

            $isMobile = preg_match('/^[0-9]{10}$/', $username);
            $isEmail  = filter_var($username, FILTER_VALIDATE_EMAIL);

            if (!$isMobile && !$isEmail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Enter valid mobile number or email address'
                ], 422);
            }

            $user = User::where(
                $isMobile ? 'primary_mobile' : 'primary_email',
                $username
            )->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Account not found'
                ], 404);
            }

            if ($user->otp_sent_at && Carbon::parse($user->otp_sent_at)->addSeconds(60)->isFuture()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please wait before requesting another OTP'
                ], 429);
            }

            $otp =  1234;

            $user->update([
                'otp_code'       => $otp,
                'otp_sent_at'    => now(),
                'otp_expires_at' => now()->addMinutes(5),
            ]);

            OtpLog::where('identifier', $username)
            ->whereIn('purpose', ['forgot_password', 'forgot_password_resend'])
            ->where('status', 'sent')
            ->update(['status' => 'expired']);

            OtpLog::create([
                'user_id'    => $user->id,
                'identifier' => $username,
                'channel'    => $isMobile ? 'sms' : 'email',
                'otp_code'   => $otp,
                'purpose'    => 'forgot_password',
                'status'     => 'sent',
                'ip_address' => $request->ip(),
                'sent_at'    => now(),
            ]);


            if ($isMobile) {

            } else {

            }

            return response()->json([
                'status' => true,
                'message' => $isMobile
                    ? 'OTP sent to registered mobile number'
                    : 'OTP sent to registered email address',
                'expires_in' => 300
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function forgotPasswordResendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid input',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $username = $request->username;

            $isMobile = preg_match('/^[0-9]{10}$/', $username);
            $isEmail  = filter_var($username, FILTER_VALIDATE_EMAIL);

            if (!$isMobile && !$isEmail) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Enter valid mobile number or email address',
                ], 422);
            }

            $user = User::where(
                $isMobile ? 'primary_mobile' : 'primary_email',
                $username
            )->first();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Account not found',
                ], 404);
            }

            if ($user->otp_sent_at && Carbon::parse($user->otp_sent_at)->addSeconds(60)->isFuture()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Please wait before requesting another OTP',
                ], 429);
            }

            $otp =  1234;

            $user->update([
                'otp_code'       => $otp,
                'otp_sent_at'    => now(),
                'otp_expires_at' => now()->addMinutes(5),
            ]);

            OtpLog::where('identifier', $username)
                ->whereIn('purpose', ['forgot_password', 'forgot_password_resend'])
                ->where('status', 'sent')
                ->update(['status' => 'expired']);

            OtpLog::create([
                'user_id'    => $user->id,
                'identifier' => $username,
                'channel'    => $isMobile ? 'sms' : 'email',
                'otp_code'   => $otp,
                'purpose'    => 'forgot_password_resend',
                'status'     => 'sent',
                'ip_address' => $request->ip(),
                'sent_at'    => now(),
            ]);


            if ($isMobile) {

            } else {

            }

            return response()->json([
                'status'  => true,
                'message' => $isMobile
                    ? 'OTP resent to registered mobile number'
                    : 'OTP resent to registered email address',
                'expires_in' => 300,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function forgotPasswordVerifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'otp'      => 'required|digits:4',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $username = $request->username;

            $isMobile = preg_match('/^[0-9]{10}$/', $username);
            $isEmail  = filter_var($username, FILTER_VALIDATE_EMAIL);

            if (!$isMobile && !$isEmail) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Enter valid mobile number or email address',
                ], 422);
            }

            $user = User::where(
                $isMobile ? 'primary_mobile' : 'primary_email',
                $username
            )->first();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Account not found',
                ], 404);
            }

            if (!$user->otp_code || !$user->otp_expires_at) {
                return response()->json([
                    'status'  => false,
                    'message' => 'OTP not generated. Please request OTP again.',
                ], 400);
            }

            if (Carbon::now()->greaterThan(Carbon::parse($user->otp_expires_at))) {
                return response()->json([
                    'status'  => false,
                    'message' => 'OTP has expired. Please request a new OTP.',
                ], 410);
            }

            if ($user->otp_code !== $request->otp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid OTP',
                ], 401);
            }

            OtpLog::where('user_id', $user->id)
                ->where('identifier', $username)
                ->where('otp_code', $request->otp)
                ->whereIn('purpose', ['forgot_password', 'forgot_password_resend'])
                ->where('status', 'sent')
                ->orderByDesc('id')
                ->first()
                ?->update([
                    'status'      => 'verified',
                    'verified_at' => now(),
                ]);

            $user->update([
                'otp_code'       => null,
                'otp_expires_at' => null,
                'otp_sent_at'    => null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'OTP verified successfully. You can now reset your password.',
                'data'    => [
                    'user_id' => $user->id,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function forgotPasswordReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username'         => 'required|string',
                'password'         => 'required|string|min:6',
                'confirm_password' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $username = $request->username;

            $isMobile = preg_match('/^[0-9]{10}$/', $username);
            $isEmail  = filter_var($username, FILTER_VALIDATE_EMAIL);

            if (!$isMobile && !$isEmail) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Enter valid mobile number or email address',
                ], 422);
            }

            $user = User::where(
                $isMobile ? 'primary_mobile' : 'primary_email',
                $username
            )->first();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User not found',
                ], 404);
            }

            $verifiedOtp = OtpLog::where('user_id', $user->id)
                ->where('identifier', $username)
                ->whereIn('purpose', ['forgot_password', 'forgot_password_resend'])
                ->where('status', 'verified')
                ->latest()
                ->first();

            if (!$verifiedOtp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'OTP verification required before resetting password',
                ], 403);
            }

            $user->update([
                'password'    => Hash::make($request->password),
                'sample_pass' => $request->password,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Password updated successfully. You can now login.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $username = $request->username;

            $isMobile = preg_match('/^[0-9]{10}$/', $username);
            $isEmail  = filter_var($username, FILTER_VALIDATE_EMAIL);

            if (!$isMobile && !$isEmail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Enter valid mobile number or email address',
                ], 422);
            }

            $user = User::where(
                $isMobile ? 'primary_mobile' : 'primary_email',
                $username
            )->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            if (!$user->primary_mobile_verified_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please verify your mobile number before login',
                ], 403);
            }

            $token = $user->createToken('vendor-login')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'role' => $user->role,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'primary_mobile' => $user->primary_mobile,
                        'primary_email' => $user->primary_email,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function sendLoginOtp(Request $request, string $purpose)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors(),
            ], 422);
        }

        $username = $request->username;

        $isMobile = preg_match('/^[0-9]{10}$/', $username);
        $isEmail  = filter_var($username, FILTER_VALIDATE_EMAIL);

        if (!$isMobile && !$isEmail) {
            return response()->json([
                'status' => false,
                'message' => 'Enter valid mobile number or email address',
            ], 422);
        }

        $user = User::where(
            $isMobile ? 'primary_mobile' : 'primary_email',
            $username
        )->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Account not found',
            ], 404);
        }

        if ($isMobile && !$user->primary_mobile_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Please verify your mobile number before login',
            ], 403);
        }

        if ($user->otp_sent_at && Carbon::parse($user->otp_sent_at)->addSeconds(60)->isFuture()) {
            return response()->json([
                'status' => false,
                'message' => 'Please wait before requesting another OTP',
            ], 429);
        }

        $otp =  1234;

        $user->update([
            'otp_code'       => $otp,
            'otp_sent_at'    => now(),
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        OtpLog::where('identifier', $username)
            ->whereIn('purpose', ['login', 'login_resend'])
            ->where('status', 'sent')
            ->update(['status' => 'expired']);

        OtpLog::create([
            'user_id'    => $user->id,
            'identifier' => $username,
            'channel'    => $isMobile ? 'sms' : 'email',
            'otp_code'   => $otp,
            'purpose'    => $purpose,
            'status'     => 'sent',
            'ip_address' => $request->ip(),
            'sent_at'    => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => $purpose == 'login'
                ? 'Login OTP sent'
                : 'Login OTP resent',
            'expires_in' => 300,
        ]);
    }

    public function loginSendOtp(Request $request)
    {
        return $this->sendLoginOtp($request, 'login');
    }

    public function loginResendOtp(Request $request)
    {
        return $this->sendLoginOtp($request, 'login_resend');
    }

    public function loginVerifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'otp'      => 'required|digits:4',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $username = $request->username;

            $isMobile = preg_match('/^[0-9]{10}$/', $username);
            $isEmail  = filter_var($username, FILTER_VALIDATE_EMAIL);

            if (!$isMobile && !$isEmail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Enter valid mobile number or email address',
                ], 422);
            }

            $user = User::where(
                $isMobile ? 'primary_mobile' : 'primary_email',
                $username
            )->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Account not found',
                ], 404);
            }

            if (!$user->otp_code || !$user->otp_expires_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'OTP not generated',
                ], 400);
            }

            if (now()->greaterThan($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'OTP expired',
                ], 410);
            }

            if ($user->otp_code !== $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP',
                ], 401);
            }


            OtpLog::where('user_id', $user->id)
                ->where('identifier', $username)
                ->where('otp_code', $request->otp)
                ->whereIn('purpose', ['login', 'login_resend'])
                ->where('status', 'sent')
                ->latest()
                ->first()
                ?->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);

            $user->update([
                'otp_code'       => null,
                'otp_sent_at'    => null,
                'otp_expires_at' => null,
            ]);

            $token = $user->createToken('vendor-login')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {

            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Logout successful',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
