<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login via Email or Mobile.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->login);

        // Allow login by email OR mobile number OR username
        $user = User::where('email', $loginInput)
            ->orWhere('mobile', $loginInput)
            ->orWhere('username', $loginInput)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The credentials provided are invalid.'],
            ]);
        }

        if ($user->status && strtolower($user->status) === 'inactive') {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        $token = $user->createToken('mobile_app_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Logged in successfully.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'username' => $user->username,
                'role' => $user->role ?? 'Employee',
                'status' => $user->status ?? 'Active',
            ]
        ]);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|string|max:20|unique:users',
            'username' => 'nullable|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $username = $validated['username'] ?? strtolower(explode('@', $validated['email'])[0]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'username' => $username,
            'password' => Hash::make($validated['password']),
            'role' => 'Employee',
            'status' => 'Active',
        ]);

        $token = $user->createToken('mobile_app_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Account registered successfully.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'username' => $user->username,
                'role' => $user->role,
                'status' => $user->status,
            ]
        ], 201);
    }

    /**
     * Send 6-digit OTP to user email for password reset.
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $email = trim($request->email);
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user account found with that email address.',
            ], 404);
        }

        // Generate 6-digit numeric OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));

        // Store OTP in Cache for 15 minutes
        \Illuminate\Support\Facades\Cache::put('password_otp_' . $email, [
            'otp' => (string)$otp,
            'user_id' => $user->id,
            'created_at' => now()->timestamp,
        ], now()->addMinutes(15));

        // Attempt to send email / log OTP
        try {
            \Illuminate\Support\Facades\Mail::raw("Your Salary Manager password reset OTP code is: {$otp}. It will expire in 15 minutes.", function ($message) use ($email) {
                $message->to($email)->subject('Password Reset OTP - Salary Manager');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info("Password Reset OTP for {$email}: {$otp}. Error sending mail: " . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'A 6-digit OTP code has been sent to your email address.',
            'email' => $email,
            // In local/development, send OTP in response for instant testing
            'debug_otp' => config('app.debug') ? (string)$otp : null,
        ]);
    }

    /**
     * Verify 6-digit OTP and reset user password.
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|string|digits:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = trim($request->email);
        $otp = trim($request->otp);

        $cachedData = \Illuminate\Support\Facades\Cache::get('password_otp_' . $email);

        if (!$cachedData || (string)$cachedData['otp'] !== (string)$otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired OTP code. Please request a new OTP.',
            ], 422);
        }

        $user = User::find($cachedData['user_id']) ?? User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User account not found.',
            ], 404);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear OTP from Cache
        \Illuminate\Support\Facades\Cache::forget('password_otp_' . $email);

        // Revoke tokens
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Your password has been reset successfully. You can now login with your new password.',
        ]);
    }

    /**
     * Handle user logout (revokes Sanctum tokens).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Return authenticated user profile.
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user(),
        ]);
    }
}
