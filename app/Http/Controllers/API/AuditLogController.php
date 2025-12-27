<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Http\Requests\StoreAuditLogRequest;
use App\Http\Requests\UpdateAuditLogRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = AuditLog::with('user')->orderBy('created_at', 'desc');
            
            // Optional limit for recent activities
            $limit = $request->input('limit');
            if ($limit) {
                $query->limit((int)$limit);
            }
            
            $logs = $query->get()->map(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $this->formatActivityAction($log->action),
                    'actionKey' => $log->action, // Original action key for editing
                    'user' => $log->user ? ($log->user->user_name ?? $log->user->name ?? 'System') : 'System',
                    'time' => $log->created_at->diffForHumans(),
                    'type' => $this->getActivityType($log->action),
                    'description' => $log->description,
                    'details' => $log->details,
                    'created_at' => $log->created_at->toISOString(),
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching audit logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuditLogRequest $request)
    {
        try {
            $auditLog = AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $request->action,
                'description' => $request->description,
                'details' => $request->details,
                'ip_address' => $request->ip(),
            ]);
            
            $auditLog->load('user');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $auditLog->id,
                    'action' => $this->formatActivityAction($auditLog->action),
                    'actionKey' => $auditLog->action, // Original action key for editing
                    'user' => $auditLog->user ? ($auditLog->user->user_name ?? $auditLog->user->name ?? 'System') : 'System',
                    'time' => $auditLog->created_at->diffForHumans(),
                    'type' => $this->getActivityType($auditLog->action),
                    'description' => $auditLog->description,
                    'details' => $auditLog->details,
                    'created_at' => $auditLog->created_at->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating audit log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AuditLog $auditLog)
    {
        try {
            $auditLog->load('user');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $auditLog->id,
                    'action' => $this->formatActivityAction($auditLog->action),
                    'actionKey' => $auditLog->action, // Original action key for editing
                    'user' => $auditLog->user ? ($auditLog->user->user_name ?? $auditLog->user->name ?? 'System') : 'System',
                    'time' => $auditLog->created_at->diffForHumans(),
                    'type' => $this->getActivityType($auditLog->action),
                    'description' => $auditLog->description,
                    'details' => $auditLog->details,
                    'created_at' => $auditLog->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching audit log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuditLogRequest $request, AuditLog $auditLog)
    {
        try {
            $auditLog->update($request->only(['action', 'description', 'details']));
            $auditLog->load('user');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $auditLog->id,
                    'action' => $this->formatActivityAction($auditLog->action),
                    'actionKey' => $auditLog->action, // Original action key for editing
                    'user' => $auditLog->user ? ($auditLog->user->user_name ?? $auditLog->user->name ?? 'System') : 'System',
                    'time' => $auditLog->created_at->diffForHumans(),
                    'type' => $this->getActivityType($auditLog->action),
                    'description' => $auditLog->description,
                    'details' => $auditLog->details,
                    'created_at' => $auditLog->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating audit log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditLog $auditLog)
    {
        try {
            $auditLog->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Audit log deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting audit log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format activity action for display
     */
    private function formatActivityAction($action)
    {
        $actions = [
            'check_in' => 'Employee checked in',
            'check_out' => 'Employee checked out',
            'create_employee' => 'New employee onboarded',
            'update_employee' => 'Employee updated',
            'delete_employee' => 'Employee deleted',
            'create_leave' => 'Leave request submitted',
            'update_leave' => 'Leave request updated',
            'approve_leave' => 'Leave request approved',
            'create_payroll' => 'Payroll processed',
            'update_payroll' => 'Payroll updated',
            'create_performance' => 'Performance review completed',
            'create_training' => 'Training session completed',
        ];

        return $actions[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    /**
     * Get activity type for styling
     */
    private function getActivityType($action)
    {
        $successActions = ['check_in', 'create_employee', 'create_payroll', 'approve_leave', 'create_performance', 'create_training'];
        return in_array($action, $successActions) ? 'success' : 'info';
    }
}
