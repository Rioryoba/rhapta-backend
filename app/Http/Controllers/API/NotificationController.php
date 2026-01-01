<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Notification::where('user_id', $user->id);
        
        // Filter by archived status
        if ($request->has('archived')) {
            $archived = filter_var($request->archived, FILTER_VALIDATE_BOOLEAN);
            $query->where('archived', $archived);
        } else {
            // By default, show non-archived notifications
            $query->where(function($q) {
                $q->where('archived', false)->orWhereNull('archived');
            });
        }
        
        // Filter by read status
        if ($request->has('read')) {
            $read = filter_var($request->read, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_read', $read);
        }
        
        // Filter by module
        if ($request->has('module')) {
            $query->where('module', $request->module);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->get();
        
        // Transform to match frontend expectations
        $transformed = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title ?? $this->extractTitleFromMessage($notification->message),
                'message' => $notification->message,
                'module' => $notification->module ?? $this->extractModuleFromType($notification->type),
                'type' => $notification->type,
                'relatedId' => $notification->related_id,
                'dateTime' => $notification->created_at->toISOString(),
                'read' => $notification->is_read ?? false,
                'archived' => $notification->archived ?? false,
                'status' => $notification->status,
            ];
        });
        
        return response()->json($transformed);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
            'title' => 'nullable|string',
            'module' => 'nullable|string',
            'related_id' => 'nullable|integer',
            'status' => 'nullable|string',
        ]);
        
        $validated['user_id'] = Auth::id();
        $validated['is_read'] = false;
        $validated['archived'] = false;
        
        $notification = Notification::create($validated);
        
        return response()->json([
            'id' => $notification->id,
            'title' => $notification->title ?? $this->extractTitleFromMessage($notification->message),
            'message' => $notification->message,
            'module' => $notification->module ?? $this->extractModuleFromType($notification->type),
            'type' => $notification->type,
            'relatedId' => $notification->related_id,
            'dateTime' => $notification->created_at->toISOString(),
            'read' => $notification->is_read ?? false,
            'archived' => $notification->archived ?? false,
            'status' => $notification->status,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        // Ensure user can only access their own notifications
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'id' => $notification->id,
            'title' => $notification->title ?? $this->extractTitleFromMessage($notification->message),
            'message' => $notification->message,
            'module' => $notification->module ?? $this->extractModuleFromType($notification->type),
            'type' => $notification->type,
            'relatedId' => $notification->related_id,
            'dateTime' => $notification->created_at->toISOString(),
            'read' => $notification->is_read ?? false,
            'archived' => $notification->archived ?? false,
            'status' => $notification->status,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        // Ensure user can only update their own notifications
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'is_read' => 'sometimes|boolean',
            'read' => 'sometimes|boolean', // Support both naming conventions
            'archived' => 'sometimes|boolean',
            'title' => 'sometimes|string',
            'message' => 'sometimes|string',
            'module' => 'sometimes|string',
            'type' => 'sometimes|string',
            'related_id' => 'sometimes|integer',
            'status' => 'sometimes|string',
        ]);
        
        // Handle 'read' field mapping to 'is_read'
        if (isset($validated['read'])) {
            $validated['is_read'] = $validated['read'];
            unset($validated['read']);
        }
        
        $notification->update($validated);
        $notification->refresh();
        
        return response()->json([
            'id' => $notification->id,
            'title' => $notification->title ?? $this->extractTitleFromMessage($notification->message),
            'message' => $notification->message,
            'module' => $notification->module ?? $this->extractModuleFromType($notification->type),
            'type' => $notification->type,
            'relatedId' => $notification->related_id,
            'dateTime' => $notification->created_at->toISOString(),
            'read' => $notification->is_read ?? false,
            'archived' => $notification->archived ?? false,
            'status' => $notification->status,
        ]);
    }

    /**
     * Mark notification as read/unread
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->update(['is_read' => true]);
        
        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Archive a notification
     */
    public function archive(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->update(['archived' => true]);
        
        return response()->json(['message' => 'Notification archived']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        // Ensure user can only delete their own notifications
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->delete();
        
        return response()->noContent();
    }

    /**
     * Helper method to extract title from message if title is not set
     */
    private function extractTitleFromMessage($message)
    {
        // Try to extract first sentence or first 50 characters
        $sentences = explode('.', $message);
        $title = trim($sentences[0]);
        return strlen($title) > 60 ? substr($title, 0, 60) . '...' : $title;
    }

    /**
     * Helper method to extract module from type if module is not set
     */
    private function extractModuleFromType($type)
    {
        // Map common types to modules
        $typeToModule = [
            'project' => 'project',
            'task' => 'task',
            'leave' => 'leave',
            'finance' => 'finance',
        ];
        
        $typeLower = strtolower($type);
        foreach ($typeToModule as $key => $module) {
            if (strpos($typeLower, $key) !== false) {
                return $module;
            }
        }
        
        return 'project'; // Default
    }
}
