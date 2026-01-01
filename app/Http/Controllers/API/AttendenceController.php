<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Attendence;
use App\Http\Requests\StoreAttendenceRequest;
use App\Http\Requests\UpdateAttendenceRequest;
use App\Services\AttendenceService;
use Illuminate\Http\Request;

class AttendenceController extends Controller
{
    /**
     * Employee check-in endpoint.
     */
    public function checkIn(Request $request, AttendenceService $service)
    {
        $request->validate([
            'employeeId' => 'required|exists:employees,id',
            'checkIn' => 'nullable|date_format:H:i:s',
        ]);
        try {
            $attendence = $service->checkIn($request->input('employeeId'), $request->input('checkIn'));
            \App\Models\AuditLog::create([
                'action' => 'check_in',
                'user_id' => auth()->id(),
                'details' => json_encode($attendence->toArray()),
            ]);
            return new \App\Http\Resources\AttendenceResource($attendence->load('employee'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Employee check-out endpoint.
     */
    public function checkOut(Request $request, AttendenceService $service)
    {
        $request->validate([
            'employeeId' => 'required|exists:employees,id',
            'checkOut' => 'nullable|date_format:H:i:s',
        ]);
        try {
            $attendence = $service->checkOut($request->input('employeeId'), $request->input('checkOut'));
            \App\Models\AuditLog::create([
                'action' => 'check_out',
                'user_id' => auth()->id(),
                'details' => json_encode($attendence->toArray()),
            ]);
            return new \App\Http\Resources\AttendenceResource($attendence->load('employee'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attendences = Attendence::with('employee')->paginate();
        return \App\Http\Resources\AttendenceResource::collection($attendences);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendenceRequest $request)
    {
        $data = $request->validated();
        // Use provided employee_id or fall back to authenticated user's employee_id
        $data['employee_id'] = $request->input('employee_id') ?? auth()->user()->employee_id;
        $data['date'] = $request->input('date') ?? now()->toDateString();
        // Use provided status or default to 'present'
        $data['status'] = $request->input('status') ?? 'present';
        if ($request->has('biometric')) {
            $data['biometric'] = $request->input('biometric');
        }
        $attendence = Attendence::create($data);
        \App\Models\AuditLog::create([
            'action' => 'create_attendence',
            'user_id' => auth()->id(),
            'details' => json_encode($attendence->toArray()),
        ]);
        return new \App\Http\Resources\AttendenceResource($attendence->load('employee'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendence $attendence)
    {
        return new \App\Http\Resources\AttendenceResource($attendence->load('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendenceRequest $request, Attendence $attendence)
    {
        $data = $request->validated();
        // Use provided employee_id or keep existing, or fall back to authenticated user's employee_id
        if (isset($data['employee_id'])) {
            $data['employee_id'] = $data['employee_id'];
        } elseif (!isset($data['employee_id'])) {
            // Keep existing employee_id if not provided
            unset($data['employee_id']);
        }
        // Use provided date or keep existing
        if (!isset($data['date'])) {
            unset($data['date']);
        }
        // Allow status to be updated if provided
        if (!isset($data['status'])) {
            unset($data['status']);
        }
        if ($request->has('biometric')) {
            $data['biometric'] = $request->input('biometric');
        }
        $attendence->update($data);
        \App\Models\AuditLog::create([
            'action' => 'update_attendence',
            'user_id' => auth()->id(),
            'details' => json_encode($attendence->toArray()),
        ]);
        return new \App\Http\Resources\AttendenceResource($attendence->load('employee'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendence $attendence)
    {
        $attendence->delete();
        \App\Models\AuditLog::create([
            'action' => 'delete_attendence',
            'user_id' => auth()->id(),
            'details' => json_encode($attendence->toArray()),
        ]);
        return response()->json(['message' => 'Attendence deleted successfully']);
    }
}
