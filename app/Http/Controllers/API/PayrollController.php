<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendence;
use App\Http\Requests\StorePayrollRequest;
use App\Http\Requests\UpdatePayrollRequest;
use App\Http\Resources\PayrollResource;
use App\Http\Resources\PayrollResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Payroll::query()->with('employee');
            
            // Optional filtering by employee_id
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            
            // Optional filtering by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Optional filtering by month
            if ($request->has('month')) {
                $query->where('month', $request->month);
            }
            
            $payrolls = $query->orderBy('created_at', 'desc')->paginate();
            
            return new PayrollResourceCollection($payrolls->appends(request()->query()));
        } catch (\Exception $e) {
            \Log::error('Error fetching payrolls: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error fetching payrolls',
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function store(StorePayrollRequest $request)
    {
        // Calculate net salary
        $basicSalary = $request->basic_salary;
        $allowances = $request->allowances ?? 0;
        $deductions = $request->deductions ?? 0;
        $netSalary = $basicSalary + $allowances - $deductions;
        
        $payrollData = [
            'employee_id' => $request->employee_id,
            'month' => $request->month,
            'pay_date' => $request->pay_date,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'status' => $request->status,
        ];
        
        $payroll = Payroll::create($payrollData);
        
        // Audit log
        \App\Models\AuditLog::create([
            'action' => 'create_payroll',
            'description' => 'Payroll ' . $payroll->id . ' created for employee ' . $payroll->employee_id . ' by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        
        return new PayrollResource($payroll->load('employee'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Payroll $payroll)
    {
        return new PayrollResource($payroll->load('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payroll $payroll)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePayrollRequest $request, Payroll $payroll)
    {
        $validated = $request->validated();
        
        // Calculate net salary - always recalculate to ensure accuracy
        $basicSalary = $validated['basic_salary'] ?? $payroll->basic_salary;
        $allowances = $validated['allowances'] ?? $payroll->allowances ?? 0;
        $deductions = $validated['deductions'] ?? $payroll->deductions ?? 0;
        
        // Always recalculate net salary
        $validated['net_salary'] = $basicSalary + $allowances - $deductions;
        
        $payroll->update($validated);
        
        // Audit log
        \App\Models\AuditLog::create([
            'action' => 'update_payroll',
            'description' => 'Payroll ' . $payroll->id . ' updated by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        
        return new PayrollResource($payroll->fresh()->load('employee'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payroll $payroll)
    {
        $payrollId = $payroll->id;
        $payroll->delete();
        
        // Audit log
        \App\Models\AuditLog::create([
            'action' => 'delete_payroll',
            'description' => 'Payroll ' . $payrollId . ' deleted by user ' . auth()->id(),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);
        
        return response()->json(['message' => 'Payroll deleted successfully']);
    }

    /**
     * Get attendance summary for an employee for a specific period
     */
    public function getAttendanceSummary(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/', // Format: YYYY-MM
        ]);

        try {
            $employeeId = $request->employee_id;
            $period = $request->period;
            
            $attendanceData = $this->getAttendanceData($employeeId, $period);
            
            return response()->json([
                'data' => $attendanceData
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching attendance summary: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error fetching attendance summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate payroll with taxes and benefits
     */
    public function calculatePayroll(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
        ]);

        try {
            $employee = Employee::findOrFail($request->employee_id);
            $period = $request->period;
            
            // Get attendance data directly
            $attendanceData = $this->getAttendanceData($employee->id, $period);
            
            // Get base salary
            $baseSalary = (float) $employee->salary;
            $daysInMonth = 22; // Standard working days
            $dailyRate = $baseSalary / $daysInMonth;
            
            // Calculate earnings
            $regularPay = ($attendanceData['daysWorked'] / $daysInMonth) * $baseSalary;
            $overtimePay = $attendanceData['overtimeHours'] * ($dailyRate / 8) * 1.5; // 1.5x for overtime
            $shiftAllowance = $attendanceData['shiftAllowances'];
            $grossSalary = $regularPay + $overtimePay + $shiftAllowance;
            
            // Calculate deductions
            $paye = $this->calculatePAYE($grossSalary);
            $nssf = $this->calculateNSSF($grossSalary);
            $healthInsurance = $this->calculateHealthInsurance($grossSalary);
            $totalDeductions = $paye + $nssf + $healthInsurance;
            
            // Net salary
            $netSalary = $grossSalary - $totalDeductions;
            
            return response()->json([
                'data' => [
                    'baseSalary' => $baseSalary,
                    'regularPay' => round($regularPay, 2),
                    'overtimePay' => round($overtimePay, 2),
                    'shiftAllowance' => $shiftAllowance,
                    'grossSalary' => round($grossSalary, 2),
                    'paye' => round($paye, 2),
                    'nssf' => round($nssf, 2),
                    'healthInsurance' => round($healthInsurance, 2),
                    'totalDeductions' => round($totalDeductions, 2),
                    'netSalary' => round($netSalary, 2),
                    'attendance' => $attendanceData,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating payroll: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error calculating payroll',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance data (helper method)
     */
    private function getAttendanceData($employeeId, $period)
    {
        // Parse period to get start and end dates
        $date = Carbon::createFromFormat('Y-m', $period);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();
        
        // Get all attendance records for the period
        $attendances = Attendence::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // Calculate summary
        $daysWorked = $attendances->where('status', 'present')->count();
        $leaveDays = $attendances->where('status', 'leave')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        
        // Calculate overtime hours (assuming 8 hours standard, any hours beyond that)
        $overtimeHours = 0;
        $shiftAllowances = 0;
        $nightShiftHours = 0;
        $weekendHours = 0;
        
        foreach ($attendances as $attendance) {
            if ($attendance->check_in && $attendance->check_out) {
                $checkIn = Carbon::parse($attendance->date . ' ' . $attendance->check_in);
                $checkOut = Carbon::parse($attendance->date . ' ' . $attendance->check_out);
                $hoursWorked = $checkIn->diffInHours($checkOut);
                
                // Standard work day is 8 hours
                if ($hoursWorked > 8) {
                    $overtimeHours += ($hoursWorked - 8);
                }
                
                // Check for night shift (work after 6 PM or before 6 AM)
                $checkInHour = $checkIn->hour;
                $checkOutHour = $checkOut->hour;
                if ($checkOutHour >= 18 || $checkInHour < 6) {
                    $nightShiftHours += $hoursWorked;
                }
                
                // Check for weekend
                if ($checkIn->isWeekend()) {
                    $weekendHours += $hoursWorked;
                }
            }
            
            // Shift allowance (example: 1500 TZS per day for shift work)
            if ($attendance->status === 'present') {
                $shiftAllowances += 1500; // This can be made configurable
            }
        }
        
        return [
            'daysWorked' => $daysWorked,
            'overtimeHours' => round($overtimeHours, 2),
            'leaveDays' => $leaveDays,
            'absentDays' => $absentDays,
            'shiftAllowances' => $shiftAllowances,
            'nightShiftHours' => round($nightShiftHours, 2),
            'weekendHours' => round($weekendHours, 2),
        ];
    }

    /**
     * Calculate PAYE (Pay As You Earn) tax
     * Tanzania tax brackets - adjust as needed
     */
    private function calculatePAYE($grossSalary)
    {
        if ($grossSalary <= 270000) return 0;
        if ($grossSalary <= 520000) return ($grossSalary - 270000) * 0.08;
        if ($grossSalary <= 760000) return 20000 + ($grossSalary - 520000) * 0.20;
        return 68000 + ($grossSalary - 760000) * 0.25;
    }

    /**
     * Calculate NSSF (National Social Security Fund) contribution
     * Tanzania - 10% of gross, capped
     */
    private function calculateNSSF($grossSalary)
    {
        $nssfRate = 0.10;
        $maxContribution = 200000; // Monthly cap
        return min($grossSalary * $nssfRate, $maxContribution);
    }

    /**
     * Calculate Health Insurance contribution
     * Example: 2% of gross
     */
    private function calculateHealthInsurance($grossSalary)
    {
        return $grossSalary * 0.02;
    }
}
