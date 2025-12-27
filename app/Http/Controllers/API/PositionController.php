<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Position;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Position::query();
        
        // Add search functionality
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('department', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        $positions = $query->with('department')->paginate();
        return \App\Http\Resources\PositionResource::collection($positions->appends(request()->query()));
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
    public function store(StorePositionRequest $request)
    {
        $position = Position::create($request->validated());
        \App\Models\AuditLog::create([
            'action' => 'create_position',
            'user_id' => auth()->id(),
            'details' => json_encode($position->toArray()),
        ]);
        return new \App\Http\Resources\PositionResource($position->load('department'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        return new \App\Http\Resources\PositionResource($position->load('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionRequest $request, Position $position)
    {
        $position->update($request->validated());
        \App\Models\AuditLog::create([
            'action' => 'update_position',
            'user_id' => auth()->id(),
            'details' => json_encode($position->toArray()),
        ]);
        return new \App\Http\Resources\PositionResource($position->load('department'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        $positionData = $position->toArray();
        $position->delete();
        \App\Models\AuditLog::create([
            'action' => 'delete_position',
            'user_id' => auth()->id(),
            'details' => json_encode($positionData),
        ]);
        return response()->json(['message' => 'Position deleted successfully']);
    }
}
