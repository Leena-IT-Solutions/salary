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
     * Handle forgot password request via Email or Mobile.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
        ]);

        $loginInput = trim($request->login);

        $user = User::where('email', $loginInput)
            ->orWhere('mobile', $loginInput)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user account found with that Email or Mobile number.',
            ], 44);
        }

        // Return a mock/verification token response
        return response()->json([
            'status' => 'success',
            'message' => 'Password reset request processed. If this account is registered, instructions have been dispatched.',
            'email' => $user->email,
            'mobile' => $user->mobile,
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
