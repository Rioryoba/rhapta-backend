<?php

namespace App\Http\Controllers\API\auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use App\Http\Requests\StoreLoginRequest;

class AuthentificationController extends Controller
{
    public function login(StoreLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // Attempt to authenticate user and generate JWT token
            // This method handles both user lookup and password verification
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid email or password'
                ], 401);
            }
        } catch (JWTException $e) {
            \Log::error('JWT Token creation failed: ' . $e->getMessage());
            \Log::error('JWT Token creation stack trace: ' . $e->getTraceAsString());
            
            // Check if JWT_SECRET is set
            if (empty(config('jwt.secret'))) {
                return response()->json([
                    'error' => 'JWT secret is not configured. Please run: php artisan jwt:secret'
                ], 500);
            }
            
            return response()->json([
                'error' => 'Could not create token: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred during login: ' . $e->getMessage()
            ], 500);
        }

        // Get the authenticated user with role relationship
        $user = Auth::user();
        $user->load('role');

        // Return success response with user data and token
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : null,
                'name' => $user->user_name,
            ]
        ], 200);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        try {
            $newToken = Auth::refresh();
            return response()->json(['token' => $newToken]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 401);
        }
    }
}
