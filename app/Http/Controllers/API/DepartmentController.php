<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Http\Request;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\DepartmentResourceCollection;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Department::query();
        
        // Add search functionality
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('manager', function($q) use ($searchTerm) {
                      $q->where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('last_name', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        $departments = $query->with(['manager', 'employees'])->paginate();
        return new DepartmentResourceCollection($departments->appends(request()->query()));
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
    public function store(StoreDepartmentRequest $request)
    {
        $department = Department::create($request->validated());
        \App\Models\AuditLog::create([
            'action' => 'create_department',
            'description' => 'Department ' . $department->id . ' created by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return new DepartmentResource($department->load(['manager', 'employees']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return new DepartmentResource($department->load(['manager', 'employees']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());
        \App\Models\AuditLog::create([
            'action' => 'update_department',
            'description' => 'Department ' . $department->id . ' updated by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return new DepartmentResource($department->load(['manager', 'employees']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();
        \App\Models\AuditLog::create([
            'action' => 'delete_department',
            'description' => 'Department ' . $department->id . ' deleted by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
