<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Activity;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Activity::with(['project','assignedTo']);
        
        // Filter by assigned_to if provided
        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        // Filter by project_id if provided
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $activities = $query->orderBy('id', 'desc')->paginate(20);
        return ActivityResource::collection($activities);
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
    public function store(StoreActivityRequest $request)
    {
        try {
            \Log::info('StoreActivityRequest received:', $request->all());
            $data = $request->validated();
            \Log::info('Creating activity with validated data:', $data);
            
            // Ensure status has a default value
            if (empty($data['status'])) {
                $data['status'] = 'not_started';
            }
            
            $activity = Activity::create($data);
            $activity->load(['project','assignedTo']);
            return new ActivityResource($activity);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for activity:', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            throw $e; // Re-throw validation exceptions to return proper 422 response
        } catch (\Exception $e) {
            \Log::error('Failed to create activity:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
                'validated_data' => $request->validated() ?? 'validation_failed'
            ]);
            return response()->json([
                'message' => 'Failed to create activity',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        $activity->load(['project','assignedTo']);
        return new ActivityResource($activity);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $data = $request->validated();
        $activity->update($data);
        $activity->load(['project','assignedTo']);
        return new ActivityResource($activity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();
        return response()->noContent();
    }
}

