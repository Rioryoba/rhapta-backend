<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Set a new password for the authenticated user (first login).
     */
    public function setPassword(\App\Http\Requests\SetPasswordRequest $request)
    {
        // Debug: Always log the received Authorization header and bearer token
        $authHeader = $request->header('Authorization');
        $bearerToken = $request->bearerToken();
    Log::info('setPassword called', [
            'Authorization' => $authHeader,
            'bearerToken' => $bearerToken,
        ]);
        // Force the use of the 'api' guard and refetch the user by ID to guarantee an Eloquent model
        $authUser = auth('api')->user();
        $user = null;
        if ($authUser) {
            $user = \App\Models\User::find($authUser->id);
        }
        if (!$user) {
            Log::error('No authenticated user found in setPassword. Token: ' . $request->bearerToken());
            return response()->json([
                'message' => 'No authenticated user found.',
                'token' => $request->bearerToken(),
            ], 401);
        }
        Log::info('Authenticated user in setPassword', [
            'user_id' => $user->id,
            'token' => $request->bearerToken(),
            'user_class' => is_object($user) ? get_class($user) : 'not an object',
        ]);
        $user->password = Hash::make($request->input('password'));
        if (!method_exists($user, 'save')) {
            Log::error('User object does not have save() method', ['user_class' => is_object($user) ? get_class($user) : 'not an object']);
            return response()->json([
                'message' => 'Internal error: User object is not a model.',
                'user_class' => is_object($user) ? get_class($user) : 'not an object',
            ], 500);
        }
        $user->save();
        // Invalidate the current token (force logout)
        auth()->logout();
        return response()->json([
            'message' => 'Password updated successfully. Please log in again with your new password.'
        ], 200);
    }

    /**
     * Change password for the authenticated user (requires current password).
     */
    public function changePassword(\App\Http\Requests\ChangePasswordRequest $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json([
                'message' => 'No authenticated user found.'
            ], 401);
        }

        $user = User::find($authUser->id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.'
        ], 200);
    }

    /**
     * Update the authenticated user's profile (name, email, and phone if employee exists).
     */
    public function updateProfile(\App\Http\Requests\UpdateProfileRequest $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json([
                'message' => 'No authenticated user found.'
            ], 401);
        }

        $user = User::find($authUser->id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        // Update user fields
        if ($request->has('user_name')) {
            $user->user_name = $request->input('user_name');
        }
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }

        $user->save();
        $user->load('role');

        // Update employee phone if user has an employee_id and phone is provided
        $phoneUpdated = false;
        if ($user->employee_id && $request->has('phone')) {
            try {
                $employee = \App\Models\Employee::find($user->employee_id);
                if ($employee) {
                    $employee->phone = $request->input('phone');
                    $employee->save();
                    $phoneUpdated = true;
                }
            } catch (\Exception $e) {
                \Log::error('Error updating employee phone: ' . $e->getMessage());
                // Continue even if employee phone update fails
            }
        }

        $response = [
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->user_name,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : null,
            ]
        ];

        if ($phoneUpdated) {
            $response['phone'] = $request->input('phone');
        }

        return response()->json($response, 200);
    }

     /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = new \App\Filters\UserFilter();
        $query = User::query();
        $filter->setModelQuery($query);
        $filteredQuery = $filter->transform($request);
        $users = $filteredQuery->with(['employee', 'role'])->paginate();
        return UserResource::collection($users->appends(request()->query()));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, \App\Services\UserService $service)
    {
        $user = $service->create($request->validated());
        return new UserResource($user->load(['employee', 'role']));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user->load(['employee', 'role']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user, \App\Services\UserService $service)
    {
        $user = $service->update($user, $request->validated());
        return new UserResource($user->load(['employee', 'role']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, \App\Services\UserService $service)
    {
        $service->delete($user);
        return response()->json(['message' => 'User deleted successfully']);
    }
}
