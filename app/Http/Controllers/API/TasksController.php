<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Tasks;
use App\Http\Requests\StoreTasksRequest;
use App\Http\Requests\UpdateTasksRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Tasks::with(['project','assignedTo'])->paginate(20);
        return TaskResource::collection($tasks);
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
    public function store(StoreTasksRequest $request)
    {
        $data = $request->validated();
        $task = Tasks::create($data);
        $task->load(['project','assignedTo']);
        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tasks $tasks)
    {
        $tasks->load(['project','assignedTo']);
        return new TaskResource($tasks);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tasks $tasks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTasksRequest $request, Tasks $tasks)
    {
        $data = $request->validated();
        $tasks->update($data);
        $tasks->load(['project','assignedTo']);
        return new TaskResource($tasks);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tasks $tasks)
    {
        $tasks->delete();
        return response()->noContent();
    }
}
