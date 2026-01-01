<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use App\Http\Requests\StoreRegistrationRequest;

class AuthController extends Controller
{
    public function register(StoreRegistrationRequest $request)
    {
        $validated = $request->validated();
        // Check for duplicate email
        $exists = User::where('email', $validated['email'])->exists();
        if ($exists) {
            return response()->json([
                'error' => 'User with this email already exists.'
            ], 422);
        }
        $user = new User();
        $user->user_name = $validated['user_name'];
        $user->email = $validated['email'];
        $user->password = bcrypt($validated['password']);
        $user->role_id = $validated['role_id'];
        $user->employee_id = $validated['employee_id'] ?? null;
        $user->save();

        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        return response()->json(['token' => $token]);
    }

    public function me()
    {
        return response()->json(Auth::user());
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
