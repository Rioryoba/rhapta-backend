<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Performance;
use App\Http\Requests\StorePerformanceRequest;
use App\Http\Requests\UpdatePerformanceRequest;
use App\Http\Resources\PerformanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PerformanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Performance::query()->with('employee');
        
        // Apply filters if needed
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('period')) {
            $query->where('period', $request->period);
        }
        
        $performances = $query->orderBy('created_at', 'desc')->paginate();
        
        return PerformanceResource::collection($performances);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePerformanceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $performance = Performance::create($validated);
        $performance->load('employee');
        
        return response()->json(new PerformanceResource($performance), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Performance $performance)
    {
        $performance->load('employee');
        return new PerformanceResource($performance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePerformanceRequest $request, Performance $performance): JsonResponse
    {
        $validated = $request->validated();
        
        $performance->update($validated);
        $performance->load('employee');
        
        return response()->json(new PerformanceResource($performance));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Performance $performance): JsonResponse
    {
        $performance->delete();
        
        return response()->json(['message' => 'Performance record deleted successfully']);
    }
}
