<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User registered successfully', ['user' => $user, 'token' => $token]);
            return response()->json([
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::error('Invalid credentials', ['email' => $request->email]);
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('User logged in successfully', ['user' => $user, 'token' => $token]);

        return response()->json([
            'message' => 'Logged in successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        Log::info('User logged out successfully');

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
