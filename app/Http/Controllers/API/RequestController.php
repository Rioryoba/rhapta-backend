<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Request;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestRequest;
use App\Http\Resources\RequestResource;
use App\Http\Resources\RequestResourceCollection;
use App\Services\RequestService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller
{
	public function __construct()
	{
		$this->authorizeResource(Request::class, 'request');
	}

	// List all requests visible to the user
	public function index()
	{
		$user = auth()->user();
		$role = $user->role?->name;
		$employee = $user->employee;
		$query = Request::query();
		if ($role === 'manager' && $employee) {
			$query->where('department_id', $employee->department_id)
				  ->where(function($q) use ($employee) {
					  $q->where('current_approver_id', $employee->id)
						->orWhere('next_approver_role', 'manager');
				  });
		} elseif ($role === 'ceo') {
			$query->where('next_approver_role', 'ceo');
		} elseif ($role === 'accountant') {
			$query->where('next_approver_role', 'accountant');
		} else {
			$query->where('employee_id', $employee?->id);
		}
		$requests = $query->with(['employee', 'department', 'currentApprover'])->paginate();
		return new RequestResourceCollection($requests);
	}

	// Store a new request
	public function store(StoreRequestRequest $request, RequestService $service)
	{
		$user = auth()->user();
		$employee = $user->employee;
		if (!$employee) {
			return response()->json(['error' => 'You are not registered as an employee.'], 403);
		}
		$data = $request->validated();
		$data['employee_id'] = $employee->id;
		$data['department_id'] = $employee->department_id;
		$created = $service->submit($data);
		\App\Models\AuditLog::create([
			'action' => 'submit_request',
			'user_id' => $user->id,
			'details' => json_encode($created->toArray()),
		]);
		return new RequestResource($created->load(['employee', 'department', 'currentApprover']));
	}

	// Show a single request
	public function show(Request $request)
	{
		$request->load(['employee', 'department', 'currentApprover']);
		return new RequestResource($request);
	}

	// Update a request
	public function update(UpdateRequestRequest $request, Request $requestModel)
	{
		$requestModel->update($request->validated());
		\App\Models\AuditLog::create([
			'action' => 'update_request',
			'user_id' => auth()->id(),
			'details' => json_encode($requestModel->toArray()),
		]);
		return new RequestResource($requestModel->load(['employee', 'department', 'currentApprover']));
	}

	// Delete a request
	public function destroy(Request $request)
	{
		$request->delete();
		\App\Models\AuditLog::create([
			'action' => 'delete_request',
			'user_id' => auth()->id(),
			'details' => json_encode($request->toArray()),
		]);
		return response()->json(['message' => 'Request deleted successfully']);
	}

	// Manager approves or forwards
	public function managerApprove(Request $request, RequestService $service, HttpRequest $httpRequest)
	{
		$user = auth()->user();
		$employee = $user->employee;
		$request->load('department');
		$department = $request->department;
	// Log::debug('managerApprove: checking manager', [
	// 		'user_id' => $user->id,
	// 		'employee_id' => $employee?->id,
	// 		'department_id' => $department?->id,
	// 		'department_manager_id' => $department?->manager_id,
	// 	]);

		   $user = auth()->user();
		   $employee = $user->employee;
		   $request->load('department');
		   $department = $request->department;
		   if (!$employee || !$department || $department->manager_id != $employee->id) {
			   return response()->json(['error' => 'Only the department manager can approve this request.'], 403);
		   }

		   $nextRole = $httpRequest->input('nextApproverRole'); // 'ceo' or 'accountant'
		   $managerResponse = $httpRequest->input('managerResponse');
		   if ($managerResponse) {
			   $request->update([
				   'status' => 'manager_approved',
				   'manager_response' => $managerResponse,
				   'approved_at' => now(),
				   'next_approver_type' => null,
			   ]);
			   $service->managerApprove($request, null);
		   } elseif (in_array($nextRole, ['ceo', 'accountant'])) {
			   $nextApproverId = null;
			   if ($nextRole === 'ceo') {
				   $nextApproverId = \App\Models\Employee::whereHas('user', function($q){ $q->whereHas('role', function($r){ $r->where('name', 'ceo'); }); })->first()?->id;
			   } elseif ($nextRole === 'accountant') {
				   $nextApproverId = \App\Models\Employee::whereHas('user', function($q){ $q->whereHas('role', function($r){ $r->where('name', 'accountant'); }); })->first()?->id;
			   }
			   $request->update([
				   'status' => 'manager_approved',
				   'current_approver_id' => $nextApproverId,
				   'next_approver_role' => $nextRole,
				   'next_approver_type' => 'employee',
				   'approved_at' => now(),
			   ]);
			   $service->managerApprove($request, $nextApproverId);
		   } else {
			   return response()->json(['error' => 'Invalid next approver role or missing manager response.'], 422);
		   }
		//    \App\Models\AuditLog::create([
		// 	   'action' => 'manager_approve',
		// 	   'user_id' => auth()->id(),
		// 	   'details' => json_encode($request->toArray()),
		//    ]);
		   return new RequestResource($request->load(['employee', 'department', 'currentApprover']));
	}

	// CEO approves or declines
	public function ceoApprove(Request $request, RequestService $service, HttpRequest $httpRequest)
	{
		$ceoResponse = $httpRequest->input('ceoResponse');
		$declineReason = $httpRequest->input('declineReason');
		if ($declineReason) {
			$request->update([
				'status' => 'rejected',
				'ceo_response' => $ceoResponse,
				'decline_reason' => $declineReason,
				'rejected_at' => now(),
			]);
		} else {
			$accountantId = \App\Models\Employee::whereHas('user', function($q){ $q->whereHas('role', function($r){ $r->where('name', 'accountant'); }); })->first()?->id;
			$request->update([
				'status' => 'ceo_approved',
				'ceo_response' => $ceoResponse,
				'current_approver_id' => $accountantId,
				'approved_at' => now(),
			]);
			$service->ceoApprove($request, $accountantId);
		}
		// \App\Models\AuditLog::create([
		// 	'action' => 'ceo_approve',
		// 	'user_id' => auth()->id(),
		// 	'details' => json_encode($request->toArray()),
		// ]);
		return new RequestResource($request->load(['employee', 'department', 'currentApprover']));
	}

	// Accountant processes the request
	public function accountantProcess(Request $request, RequestService $service)
	{
		$service->accountantProcess($request);
		// \App\Models\AuditLog::create([
		// 	'action' => 'accountant_process',
		// 	'user_id' => auth()->id(),
		// 	'details' => json_encode($request->toArray()),
		// ]);
		return new RequestResource($request->load(['employee', 'department', 'currentApprover']));
	}

	// Reject at any stage
	public function reject(Request $request, RequestService $service, HttpRequest $httpRequest)
	{
		$declineReason = $httpRequest->input('declineReason');
		$service->reject($request, auth()->id(), $declineReason);
		// \App\Models\AuditLog::create([
		// 	'action' => 'reject_request',
		// 	'user_id' => auth()->id(),
		// 	'details' => json_encode($request->toArray()),
		// ]);
		return new RequestResource($request->load(['employee', 'department', 'currentApprover']));
	}
}

