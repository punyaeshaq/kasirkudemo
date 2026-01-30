<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.']
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif.']
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'permissions' => $user->getActivePermissions()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Login with QR code token
     */
    public function loginWithToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $user = User::where('login_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'QR Code tidak valid atau sudah tidak berlaku.'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif.'
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'permissions' => $user->getActivePermissions()
        ]);
    }

    /**
     * Generate QR login token for a user (Admin only)
     */
    public function generateLoginToken(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Generate a unique token
        $token = bin2hex(random_bytes(32)); // 64 character token

        $user->login_token = $token;
        $user->save();

        return response()->json([
            'message' => 'QR Login token berhasil dibuat',
            'login_token' => $token
        ]);
    }

    /**
     * Revoke QR login token for a user (Admin only)
     */
    public function revokeLoginToken(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $user->login_token = null;
        $user->save();

        return response()->json([
            'message' => 'QR Login token berhasil dicabut'
        ]);
    }

    /**
     * Logout via beacon (for auto-logout when browser/tab closes)
     * This accepts token via query parameter since beacon cannot send custom headers
     */
    public function logoutWithBeacon(Request $request)
    {
        $token = $request->query('_token');

        if (!$token) {
            return response()->json(['message' => 'Token tidak ditemukan'], 400);
        }

        // Find and delete the token
        // Token format is: {token_id}|{token_value}
        $tokenParts = explode('|', $token);
        if (count($tokenParts) === 2) {
            $tokenId = $tokenParts[0];

            // Delete the token by its ID
            \Laravel\Sanctum\PersonalAccessToken::where('id', $tokenId)->delete();
        } else {
            // Try to find token by plain text match
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $accessToken->delete();
            }
        }

        return response()->json(['message' => 'Logged out']);
    }
}
