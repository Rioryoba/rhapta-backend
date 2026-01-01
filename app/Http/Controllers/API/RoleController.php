<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleResourceCollection;
use Illuminate\Http\Request;
use App\Filters\RoleFilter;
use Illuminate\Support\Facades\Log;



class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // If 'all' parameter is set, return all roles without pagination (useful for dropdowns)
        if ($request->has('all') && $request->input('all') == 'true') {
            $roles = Role::orderBy('name', 'asc')->get();
            // Return as a simple array for easier frontend consumption
            return response()->json([
                'data' => $roles->map(function($role) {
                    return [
                        'roleId' => $role->id,
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                })
            ]);
        }
        
        $roles = Role::orderBy('name', 'asc')->paginate();
        return new RoleResourceCollection($roles->appends(request()->query()));
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
    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->validated());
        \App\Models\AuditLog::create([
            'action' => 'create_role',
            'description' => 'Role ' . $role->id . ' created by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }
        return new RoleResource($role);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }
        try {
            $role->update($request->validated());
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
        \App\Models\AuditLog::create([
            'action' => 'update_role',
            'description' => 'Role ' . $role->id . ' updated by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return response()->json(['message' => 'Updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }
        try {
            $role->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
        \App\Models\AuditLog::create([
            'action' => 'delete_role',
            'description' => 'Role ' . $role->id . ' deleted by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
