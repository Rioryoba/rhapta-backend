<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Http\Requests\StoreTrainingRequest;
use App\Http\Requests\UpdateTrainingRequest;
use App\Http\Resources\TrainingResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Training::query()->with('employee');
        
        // Apply filters if needed
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $trainings = $query->orderBy('created_at', 'desc')->paginate();
        
        return TrainingResource::collection($trainings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $training = Training::create($validated);
        $training->load('employee');
        
        return response()->json(new TrainingResource($training), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {
        $training->load('employee');
        return new TrainingResource($training);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainingRequest $request, Training $training): JsonResponse
    {
        $validated = $request->validated();
        
        $training->update($validated);
        $training->load('employee');
        
        return response()->json(new TrainingResource($training));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training): JsonResponse
    {
        $training->delete();
        
        return response()->json(['message' => 'Training record deleted successfully']);
    }
}
