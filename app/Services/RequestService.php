<?php

namespace App\Services;

use App\Models\Request;
use App\Models\Department;
use Carbon\Carbon;

class RequestService
{
	/**
	 * Submit a new request by employee.
	 */
	public function submit(array $data)
	{
		// Convert camelCase input to snake_case for DB
		$data['status'] = 'pending';
		$data['approved_at'] = null;
		$data['rejected_at'] = null;
		$data['next_approver_role'] = 'manager';
		$data['next_approver_type'] = 'employee'; // initial approver is always an employee (manager)
		$managerId = Department::find($data['department_id'])?->manager_id;
		$data['current_approver_id'] = $managerId;
		return Request::create($data);
	}

	/**
	 * Manager approves or forwards the request.
	 */
	public function managerApprove(Request $request, $nextApproverId = null)
	{
		   if ($nextApproverId) {
			   $request->current_approver_id = $nextApproverId;
			   // If forwarding, set next_approver_type to 'employee' for ceo/accountant, else null
			   $request->next_approver_type = in_array($request->next_approver_role, ['ceo', 'accountant']) ? 'employee' : null;
		   } else {
			   $request->next_approver_type = null;
		   }
		   $request->status = 'manager_approved';
		   $request->approved_at = Carbon::now();
		   $request->save();
		   return $request;
	}

	/**
	 * CEO approves and forwards to accountant.
	 */
	public function ceoApprove(Request $request, $accountantId)
	{
		$request->status = 'ceo_approved';
		$request->current_approver_id = $accountantId;
		$request->approved_at = Carbon::now();
		$request->save();
		return $request;
	}

	/**
	 * Accountant processes the request.
	 */
	public function accountantProcess(Request $request)
	{
		$request->status = 'accountant_processed';
		$request->current_approver_id = null;
		$request->approved_at = Carbon::now();
		$request->save();
		return $request;
	}

	/**
	 * Reject the request at any stage.
	 */
	public function reject(Request $request, $userId, $declineReason = null)
	{
		$request->status = 'rejected';
		$request->current_approver_id = $userId;
		$request->rejected_at = Carbon::now();
		if ($declineReason) {
			$request->decline_reason = $declineReason;
		}
		$request->save();
		return $request;
	}
}

