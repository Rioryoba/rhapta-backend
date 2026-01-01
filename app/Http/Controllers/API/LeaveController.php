<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Http\Resources\LeaveResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userRole = $user->role?->name;
        
        $query = Leave::query()->with('employee');
        
        // If user is HR, admin, or manager, they can view all leaves (viewAny permission)
        if (in_array($userRole, ['hr', 'admin', 'manager'])) {
            $this->authorize('viewAny', Leave::class);
        } else {
            // For staff/employee users, only allow them to view their own leaves
            // Check if they have an employee_id
            if (!$user->employee_id) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You must be linked to an employee record to view leave requests.'
                ], 403);
            }
            
            // Filter to only show leaves for this employee
            $query->where('employee_id', $user->employee_id);
        }
        
        // Apply additional filters if needed
        if ($request->has('employee_id')) {
            // Only allow filtering by employee_id if user has viewAny permission
            if (in_array($userRole, ['hr', 'admin', 'manager'])) {
                $query->where('employee_id', $request->employee_id);
            }
        }
        
        if ($request->has('status')) {
            $query->where('status', strtolower($request->status));
        }
        
        $leaves = $query->orderBy('created_at', 'desc')->paginate();
        
        \Log::info('Leaves fetched', [
            'count' => $leaves->count(),
            'first_leave_reason' => $leaves->first()?->reason ?? 'N/A',
        ]);
        
        return LeaveResource::collection($leaves);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used in API context
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $this->authorize('create', Leave::class);
        
        $validated = $request->validated();
        
        // Calculate days if not provided
        if (!isset($validated['days']) || $validated['days'] == 0) {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $validated['days'] = $startDate->diffInDays($endDate) + 1;
        }
        
        $leave = Leave::create($validated);
        $leave->load('employee');
        
        return response()->json(new LeaveResource($leave), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $leave = Leave::find($id);
        
        if (!$leave) {
            return response()->json([
                'error' => 'Leave request not found.',
                'message' => "No leave request found with ID: {$id}"
            ], 404);
        }
        
        $this->authorize('view', $leave);
        $leave->load('employee');
        return new LeaveResource($leave);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leave $leave)
    {
        // Not used in API context
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeaveRequest $request, $id): JsonResponse
    {
        // Manually resolve the leave model if route model binding didn't work
        $leave = Leave::find($id);
        
        if (!$leave) {
            return response()->json([
                'error' => 'Leave request not found.',
                'message' => "No leave request found with ID: {$id}"
            ], 404);
        }
        
        $user = auth()->user();
        \Log::info('Leave update attempt', [
            'user_id' => $user->id,
            'user_role' => $user->role?->name,
            'leave_id' => $leave->id,
            'current_status' => $leave->status,
            'requested_status' => $request->input('status'),
        ]);
        
        // Load relationships needed for policy checks
        $leave->load('employee.department');
        
        try {
            // Check authorization using policy
            $this->authorize('update', $leave);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Log::warning('Leave update authorization denied', [
                'user_id' => $user->id,
                'user_role' => $user->role?->name,
                'leave_id' => $leave->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'You do not have permission to update this leave request.',
                'message' => $e->getMessage()
            ], 403);
        }
        
        $validated = $request->validated();
        
        \Log::info('Leave update validated data', [
            'validated' => $validated,
            'current_leave_status' => $leave->status,
        ]);
        
        // Recalculate days if dates are being updated
        if (isset($validated['start_date']) || isset($validated['end_date'])) {
            $startDate = Carbon::parse($validated['start_date'] ?? $leave->start_date);
            $endDate = Carbon::parse($validated['end_date'] ?? $leave->end_date);
            $validated['days'] = $startDate->diffInDays($endDate) + 1;
        }
        
        $leave->update($validated);
        
        // Refresh the model to get the updated status
        $leave->refresh();
        $leave->load('employee');
        
        \Log::info('Leave updated successfully', [
            'leave_id' => $leave->id,
            'new_status' => $leave->status,
            'status_in_db' => $leave->getOriginal('status'),
        ]);
        
        return response()->json(new LeaveResource($leave));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $leave = Leave::find($id);
        
        if (!$leave) {
            return response()->json([
                'error' => 'Leave request not found.',
                'message' => "No leave request found with ID: {$id}"
            ], 404);
        }
        
        $this->authorize('delete', $leave);
        
        $leave->delete();
        
        return response()->json(['message' => 'Leave request deleted successfully']);
    }
}
