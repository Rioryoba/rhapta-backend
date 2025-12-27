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
        $query = Leave::query()->with('employee');
        
        // Apply filters if needed
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', strtolower($request->status));
        }
        
        $leaves = $query->orderBy('created_at', 'desc')->paginate();
        
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
    public function show(Leave $leave)
    {
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
    public function update(UpdateLeaveRequest $request, Leave $leave): JsonResponse
    {
        $validated = $request->validated();
        
        // Recalculate days if dates are being updated
        if (isset($validated['start_date']) || isset($validated['end_date'])) {
            $startDate = Carbon::parse($validated['start_date'] ?? $leave->start_date);
            $endDate = Carbon::parse($validated['end_date'] ?? $leave->end_date);
            $validated['days'] = $startDate->diffInDays($endDate) + 1;
        }
        
        $leave->update($validated);
        $leave->load('employee');
        
        return response()->json(new LeaveResource($leave));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave): JsonResponse
    {
        $leave->delete();
        
        return response()->json(['message' => 'Leave request deleted successfully']);
    }
}
