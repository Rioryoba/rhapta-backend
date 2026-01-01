<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Tasks;
use App\Models\ProgressUpdate;
use App\Http\Requests\StoreTasksRequest;
use App\Http\Requests\UpdateTasksRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        try {
            $data = $request->validated();
            \Log::info('Creating task with data:', $data);
            $task = Tasks::create($data);
            $task->load(['project','assignedTo']);
            return new TaskResource($task);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to create task:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tasks $task)
    {
        $task->load(['project','assignedTo']);
        return new TaskResource($task);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tasks $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTasksRequest $request, Tasks $task)
    {
        $data = $request->validated();
        $task->update($data);
        $task->load(['project','assignedTo']);
        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tasks $task)
    {
        $task->delete();
        return response()->noContent();
    }

    /**
     * Store a progress update for a task.
     */
    public function storeProgressUpdate(Request $request, Tasks $task)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'You are not registered as an employee.'], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'progress_description' => 'required|string',
            'time_spent' => 'required|numeric|min:0|max:24',
            'remarks' => 'nullable|string',
            'update_date' => 'required|date',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if update already exists for this task and date
        $existingUpdate = ProgressUpdate::where('task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->where('update_date', $request->input('update_date'))
            ->first();

        if ($existingUpdate) {
            return response()->json([
                'error' => 'You have already submitted a daily progress update for this task today.'
            ], 422);
        }

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            $uploadedFiles = $request->file('attachments');
            foreach ($uploadedFiles as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('progress_updates', $filename, 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                ];
            }
        }

        // Create progress update
        $progressUpdate = ProgressUpdate::create([
            'task_id' => $task->id,
            'employee_id' => $employee->id,
            'progress_description' => $request->input('progress_description'),
            'time_spent' => $request->input('time_spent'),
            'remarks' => $request->input('remarks'),
            'update_date' => $request->input('update_date'),
            'attachments' => $attachments,
        ]);

        $progressUpdate->load(['task', 'employee']);

        return response()->json([
            'id' => $progressUpdate->id,
            'task_id' => $progressUpdate->task_id,
            'taskId' => $progressUpdate->task_id,
            'employee_id' => $progressUpdate->employee_id,
            'progress_description' => $progressUpdate->progress_description,
            'progressDescription' => $progressUpdate->progress_description,
            'time_spent' => $progressUpdate->time_spent,
            'timeSpent' => $progressUpdate->time_spent,
            'remarks' => $progressUpdate->remarks,
            'update_date' => $progressUpdate->update_date->format('Y-m-d'),
            'updateDate' => $progressUpdate->update_date->format('Y-m-d'),
            'attachments' => $progressUpdate->attachments,
            'created_at' => $progressUpdate->created_at,
        ], 201);
    }

    /**
     * Get all progress updates for the authenticated user's tasks.
     */
    public function getProgressUpdates(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'You are not registered as an employee.'], 403);
        }

        // Get all tasks assigned to this employee
        $taskIds = Tasks::where('assigned_to', $employee->id)->pluck('id');

        // Get all progress updates for these tasks
        $progressUpdates = ProgressUpdate::whereIn('task_id', $taskIds)
            ->with(['task', 'employee'])
            ->orderBy('update_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedUpdates = $progressUpdates->map(function ($update) {
            return [
                'id' => $update->id,
                'task_id' => $update->task_id,
                'taskId' => $update->task_id,
                'employee_id' => $update->employee_id,
                'progress_description' => $update->progress_description,
                'progressDescription' => $update->progress_description,
                'time_spent' => $update->time_spent,
                'timeSpent' => $update->time_spent,
                'remarks' => $update->remarks,
                'update_date' => $update->update_date->format('Y-m-d'),
                'updateDate' => $update->update_date->format('Y-m-d'),
                'attachments' => $update->attachments ?? [],
                'created_at' => $update->created_at,
            ];
        });

        return response()->json([
            'data' => $formattedUpdates
        ]);
    }
}
