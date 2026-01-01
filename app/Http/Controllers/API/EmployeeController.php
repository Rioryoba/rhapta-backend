<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\EmployeeResourceCollection;
use Illuminate\Http\Request;
use App\Filters\EmployeeFilter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function __construct()
    {
        // Log::debug('EmployeeController: Constructor called');
        $this->authorizeResource(Employee::class, 'employee');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)

    {

        $filter = new EmployeeFilter();
        $query = Employee::query();
        $filter->setModelQuery($query);
        $filteredQuery = $filter->transform($request);
        
        // Add search functionality
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $filteredQuery->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhereHas('department', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('position', function($q) use ($searchTerm) {
                      $q->where('title', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        $employees = $filteredQuery->with(['position', 'department'])->paginate();
        return new EmployeeResourceCollection($employees->appends(request()->query()));
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
    public function store(StoreEmployeeRequest $request)
    {
        // Check for duplicate email or phone
        
        $exists = Employee::where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->exists();
        if ($exists) {
            return response()->json([
                'error' => 'Employee with this email or phone already exists.'
            ], 422);
        }
        
        // prepareForValidation() already converted camelCase to snake_case
        // Use the snake_case fields that the model expects
        $employeeData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'hire_date' => $request->hire_date,
            'salary' => $request->salary,
            'position_id' => $request->position_id,
            'department_id' => $request->department_id,
            'status' => $request->status,
        ];
        
        // Handle profile picture upload
        if ($request->hasFile('profilePicture')) {
            $file = $request->file('profilePicture');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('employee_profiles', $filename, 'public');
            $employeeData['profile_picture'] = $path;
        }
        
        $employee = Employee::create($employeeData);
        
        // Create user account if password and role are provided
        if ($request->has('password') && !empty($request->password) && $request->has('role_id') && !empty($request->role_id)) {
            // Check if user with this email already exists
            $existingUser = \App\Models\User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json([
                    'error' => 'User with this email already exists. Please use a different email.'
                ], 422);
            }
            
            // Create user account for the employee
            $user = \App\Models\User::create([
                'user_name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => $request->role_id,
                'employee_id' => $employee->id,
            ]);
        }
        
        // Audit log: who created the employee and when
        \App\Models\AuditLog::create([
            'action' => 'create_employee',
            'description' => 'Employee ' . $employee->id . ' created by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return new EmployeeResource($employee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
    return new EmployeeResource($employee->load(['position', 'department', 'user.role']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();
        
        // Separate employee data from user data
        $employeeData = array_filter($validated, function($key) {
            return !in_array($key, ['password', 'role_id', 'profilePicture']);
        }, ARRAY_FILTER_USE_KEY);
        
        // Handle profile picture upload
        if ($request->hasFile('profilePicture')) {
            // Delete old profile picture if exists
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }
            
            $file = $request->file('profilePicture');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('employee_profiles', $filename, 'public');
            $employeeData['profile_picture'] = $path;
        }
        
        $employee->update($employeeData);
        
        // Handle user account updates if password or role is provided
        if ($request->has('password') || $request->has('role_id')) {
            $user = \App\Models\User::where('employee_id', $employee->id)->first();
            
            if ($user) {
                // Update existing user
                if ($request->has('password') && !empty($request->password)) {
                    $user->password = bcrypt($request->password);
                }
                if ($request->has('role_id') && !empty($request->role_id)) {
                    $user->role_id = $request->role_id;
                }
                // Update email if it changed
                if ($request->has('email') && $request->email !== $user->email) {
                    // Check if new email is already taken by another user
                    $emailExists = \App\Models\User::where('email', $request->email)
                        ->where('id', '!=', $user->id)
                        ->exists();
                    if (!$emailExists) {
                        $user->email = $request->email;
                    }
                }
                $user->save();
            } else if ($request->has('password') && !empty($request->password) && $request->has('role_id') && !empty($request->role_id)) {
                // Create new user account if password and role are provided but user doesn't exist
                $email = $request->has('email') ? $request->email : $employee->email;
                $existingUser = \App\Models\User::where('email', $email)->first();
                if (!$existingUser) {
                    \App\Models\User::create([
                        'user_name' => $employee->first_name . ' ' . $employee->last_name,
                        'email' => $email,
                        'password' => bcrypt($request->password),
                        'role_id' => $request->role_id,
                        'employee_id' => $employee->id,
                    ]);
                }
            }
        }
        
        // Audit log: who updated the employee and when
        \App\Models\AuditLog::create([
            'action' => 'update_employee',
            'description' => 'Employee ' . $employee->id . ' updated by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return response()->json(['message' => 'Updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        // Log::info('Deleted employee:', ['id' => $employee->id, 'by_user' => auth()->id()]);
        \App\Models\AuditLog::create([
            'action' => 'delete_employee',
            'description' => 'Employee ' . $employee->id . ' deleted by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
