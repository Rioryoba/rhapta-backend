<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\DailyActivity;
use App\Models\Activity;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffProjectController extends Controller
{
    /**
     * Get projects assigned to the authenticated staff member
     * Projects are considered assigned if the staff member has activities in them.
     * Only activities assigned to this staff member (assigned_to = employee_id) are returned.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        if (!$user->employee_id) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        // Get project IDs where the employee has activities assigned to them
        // This ensures we only return projects where HR has assigned activities to this staff member
        $projectIds = Activity::where('assigned_to', $user->employee_id)
            ->whereNotNull('project_id')
            ->distinct()
            ->pluck('project_id');

        // If no projects found, return empty collection
        if ($projectIds->isEmpty()) {
            return ProjectResource::collection(collect([]));
        }

        // Get projects with only the activities assigned to this staff member
        // The activities relationship is constrained to only show activities assigned to this employee
        $projects = Project::with([
            'manager', 
            'department', 
            'activities' => function($query) use ($user) {
                // Filter to only include activities assigned to this staff member
                $query->where('assigned_to', $user->employee_id)
                      ->orderBy('start_date', 'asc');
            }, 
            'activities.assignedTo'
        ])
        ->whereIn('id', $projectIds)
        ->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Get daily activities for a specific project
     */
    public function getProjectDailyActivities($projectId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        if (!$user->employee_id) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        // Verify the project exists and the employee has activities in it
        $project = Project::findOrFail($projectId);
        $hasActivity = Activity::where('project_id', $projectId)
            ->where('assigned_to', $user->employee_id)
            ->exists();

        if (!$hasActivity) {
            return response()->json(['message' => 'You are not assigned to this project'], 403);
        }

        $dailyActivities = DailyActivity::where('project_id', $projectId)
            ->where('employee_id', $user->employee_id)
            ->orderBy('submission_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $dailyActivities]);
    }

    /**
     * Submit a daily activity for a project
     */
    public function submitDailyActivity(Request $request, $projectId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        if (!$user->employee_id) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        // Verify the project exists and the employee has activities in it
        $project = Project::findOrFail($projectId);
        $hasActivity = Activity::where('project_id', $projectId)
            ->where('assigned_to', $user->employee_id)
            ->exists();

        if (!$hasActivity) {
            return response()->json(['message' => 'You are not assigned to this project'], 403);
        }

        $request->validate([
            'activity_description' => 'required|string',
            'materials_used' => 'nullable|string',
            'issues_challenges' => 'nullable|string',
            'submission_date' => 'required|date',
        ]);

        // Check if already submitted for this date
        $existing = DailyActivity::where('project_id', $projectId)
            ->where('employee_id', $user->employee_id)
            ->where('submission_date', $request->submission_date)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Daily activity already submitted for this date',
                'data' => $existing
            ], 422);
        }

        $dailyActivity = DailyActivity::create([
            'project_id' => $projectId,
            'employee_id' => $user->employee_id,
            'submission_date' => $request->submission_date,
            'activity_description' => $request->activity_description,
            'materials_used' => $request->materials_used,
            'issues_challenges' => $request->issues_challenges,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Daily activity submitted successfully',
            'data' => $dailyActivity
        ], 201);
    }

    /**
     * Get all daily activities for the authenticated staff member
     */
    public function getAllDailyActivities(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        if (!$user->employee_id) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $dailyActivities = DailyActivity::with(['project', 'employee'])
            ->where('employee_id', $user->employee_id)
            ->orderBy('submission_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $dailyActivities]);
    }
}

