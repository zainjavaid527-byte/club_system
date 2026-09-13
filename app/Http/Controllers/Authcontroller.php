<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login — validates credentials, issues a Sanctum API token.
     * POST /api/login
     * body: { email, password, device_name? }
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            // optional: lets you name/identify the token, e.g. "android-app", "postman"
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            // Deliberately generic message — don't reveal whether it was the
            // email or the password that was wrong.
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Revoke old tokens for this device name if you want single-session-per-device.
        // Comment out if you want to allow multiple concurrent tokens per device name.
        // $user->tokens()->where('name', $validated['device_name'] ?? 'api')->delete();

        $token = $user->createToken($validated['device_name'] ?? 'api')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout — revokes the token used to authenticate THIS request only.
     * Other devices/tokens stay logged in.
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Logout everywhere — revokes ALL tokens for this user (all devices).
     * POST /api/logout-all
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices.',
        ]);
    }

    /**
     * Return the currently authenticated user.
     * GET /api/me
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}