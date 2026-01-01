<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendence;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Training;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get HR Dashboard statistics
     */
    public function hrStatistics(Request $request)
    {
        try {
            $today = Carbon::today();
            $lastMonth = Carbon::today()->subMonth();
            $thisMonthStart = Carbon::now()->startOfMonth();
            $lastMonthStart = $lastMonth->startOfMonth();
            $lastMonthEnd = $lastMonth->endOfMonth();

            // Total Employees
            $totalEmployees = Employee::count();
            $lastMonthEmployees = Employee::where('hire_date', '<=', $lastMonthEnd)->count();
            $employeeChange = $lastMonthEmployees > 0 
                ? round((($totalEmployees - $lastMonthEmployees) / $lastMonthEmployees) * 100, 1)
                : 0;

            // Total Departments
            $totalDepartments = Department::count();
            $lastMonthDepartments = Department::where('created_at', '<=', $lastMonthEnd)->count();
            $departmentChange = $lastMonthDepartments > 0
                ? ($totalDepartments - $lastMonthDepartments)
                : 0;

            // Attendance Today
            $totalEmployeesToday = Employee::where('status', 'active')->count();
            $attendedToday = Attendence::whereDate('date', $today)
                ->whereNotNull('check_in')
                ->distinct('employee_id')
                ->count('employee_id');
            $attendanceRate = $totalEmployeesToday > 0 
                ? round(($attendedToday / $totalEmployeesToday) * 100, 1)
                : 0;
            
            // Last month attendance for comparison (average daily attendance rate)
            $lastMonthTotalEmployees = Employee::where('status', 'active')
                ->where('hire_date', '<=', $lastMonthEnd)
                ->count();
            
            // Calculate average daily attendance for last month
            $lastMonthTotalAttendanceDays = Attendence::whereBetween('date', [$lastMonthStart, $lastMonthEnd])
                ->whereNotNull('check_in')
                ->count();
            $lastMonthTotalPossibleDays = $lastMonthTotalEmployees * $lastMonth->daysInMonth;
            $lastMonthRate = $lastMonthTotalPossibleDays > 0 
                ? round(($lastMonthTotalAttendanceDays / $lastMonthTotalPossibleDays) * 100, 1)
                : 0;
            $attendanceChange = round($attendanceRate - $lastMonthRate, 1);

            // Active Leaves
            $activeLeaves = Leave::where('status', 'approved')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->count();
            $lastMonthActiveLeaves = Leave::where('status', 'approved')
                ->where('start_date', '<=', $lastMonthEnd)
                ->where('end_date', '>=', $lastMonthStart)
                ->whereDate('start_date', '<=', $lastMonthEnd)
                ->count();
            $leaveChange = $lastMonthActiveLeaves - $activeLeaves;

            // Pending Payroll
            $currentMonth = Carbon::now()->format('Y-m');
            $pendingPayroll = Payroll::where('month', $currentMonth)
                ->where('status', 'pending')
                ->count();
            $lastMonthPending = Payroll::where('month', $lastMonth->format('Y-m'))
                ->where('status', 'pending')
                ->count();
            $payrollChange = $pendingPayroll - $lastMonthPending;

            // Training Progress (average progress of all active trainings)
            $activeTrainings = Training::where('status', 'in_progress')
                ->orWhere('status', 'ongoing')
                ->get();
            $avgProgress = $activeTrainings->count() > 0
                ? round($activeTrainings->avg('progress'), 1)
                : 0;
            
            // Last month training progress
            $lastMonthTrainings = Training::where(function($q) use ($lastMonthStart, $lastMonthEnd) {
                    $q->whereBetween('start_date', [$lastMonthStart, $lastMonthEnd])
                      ->orWhereBetween('end_date', [$lastMonthStart, $lastMonthEnd]);
                })
                ->where(function($q) {
                    $q->where('status', 'in_progress')->orWhere('status', 'ongoing');
                })
                ->get();
            $lastMonthAvgProgress = $lastMonthTrainings->count() > 0
                ? round($lastMonthTrainings->avg('progress'), 1)
                : 0;
            $trainingChange = round($avgProgress - $lastMonthAvgProgress, 1);

            // Recent Activities (from AuditLog)
            $recentActivities = \App\Models\AuditLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($log) {
                    return [
                        'id' => $log->id,
                        'action' => $this->formatActivityAction($log->action),
                        'actionKey' => $log->action, // Original action key for editing
                        'user' => $log->user ? ($log->user->user_name ?? $log->user->name ?? 'System') : 'System',
                        'time' => $log->created_at->diffForHumans(),
                        'type' => $this->getActivityType($log->action),
                        'description' => $log->description,
                        'details' => $log->details,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'totalEmployees' => [
                        'value' => number_format($totalEmployees),
                        'change' => $employeeChange >= 0 ? '+' . $employeeChange . '%' : $employeeChange . '%',
                        'changeType' => $employeeChange >= 0 ? 'positive' : 'negative',
                    ],
                    'totalDepartments' => [
                        'value' => $totalDepartments,
                        'change' => $departmentChange >= 0 ? '+' . $departmentChange : $departmentChange,
                        'changeType' => $departmentChange >= 0 ? 'positive' : 'negative',
                    ],
                    'attendanceToday' => [
                        'value' => $attendanceRate . '%',
                        'change' => $attendanceChange >= 0 ? '+' . $attendanceChange . '%' : $attendanceChange . '%',
                        'changeType' => $attendanceChange >= 0 ? 'positive' : 'negative',
                    ],
                    'activeLeaves' => [
                        'value' => $activeLeaves,
                        'change' => $leaveChange >= 0 ? '+' . $leaveChange : '-' . abs($leaveChange),
                        'changeType' => $leaveChange <= 0 ? 'positive' : 'negative',
                    ],
                    'pendingPayroll' => [
                        'value' => $pendingPayroll,
                        'change' => $payrollChange >= 0 ? '+' . $payrollChange : '-' . abs($payrollChange),
                        'changeType' => $payrollChange <= 0 ? 'positive' : 'negative',
                    ],
                    'trainingProgress' => [
                        'value' => $avgProgress . '%',
                        'change' => $trainingChange >= 0 ? '+' . $trainingChange . '%' : $trainingChange . '%',
                        'changeType' => $trainingChange >= 0 ? 'positive' : 'negative',
                    ],
                    'recentActivities' => $recentActivities,
                    'attendanceRate' => $attendanceRate,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching HR statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Accountant Dashboard statistics
     */
    public function accountantStatistics(Request $request)
    {
        try {
            $timeFilter = $request->input('timeFilter', 'month'); // month or year
            $now = Carbon::now();
            
            if ($timeFilter === 'year') {
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $lastPeriodStart = $now->copy()->subYear()->startOfYear();
                $lastPeriodEnd = $now->copy()->subYear()->endOfYear();
            } else {
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $lastPeriodStart = $now->copy()->subMonth()->startOfMonth();
                $lastPeriodEnd = $now->copy()->subMonth()->endOfMonth();
            }

            // Total Revenue (from Sales)
            $totalRevenue = Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->sum('amount');
            $lastPeriodRevenue = Sale::whereBetween('sale_date', [$lastPeriodStart, $lastPeriodEnd])
                ->sum('amount');
            $revenueChange = $lastPeriodRevenue > 0
                ? round((($totalRevenue - $lastPeriodRevenue) / $lastPeriodRevenue) * 100, 1)
                : 0;

            // Total Expenses
            $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->where('status', 'approved')
                ->sum('total');
            $lastPeriodExpenses = Expense::whereBetween('expense_date', [$lastPeriodStart, $lastPeriodEnd])
                ->where('status', 'approved')
                ->sum('total');
            $expensesChange = $lastPeriodExpenses > 0
                ? round((($totalExpenses - $lastPeriodExpenses) / $lastPeriodExpenses) * 100, 1)
                : 0;

            // Net Profit
            $netProfit = $totalRevenue - $totalExpenses;
            $lastPeriodProfit = $lastPeriodRevenue - $lastPeriodExpenses;
            $profitChange = $lastPeriodProfit != 0
                ? round((($netProfit - $lastPeriodProfit) / abs($lastPeriodProfit)) * 100, 1)
                : 0;

            // Outstanding Invoices (unpaid invoices)
            $outstandingInvoices = Invoice::where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->get();
            $outstandingAmount = $outstandingInvoices->sum('total');
            $outstandingCount = $outstandingInvoices->count();

            // Pending Payments (from Payment model - if it has status field)
            // If Payment model doesn't have status, we'll use invoices with pending status
            $pendingPayments = Invoice::where('status', 'pending')
                ->orWhere('status', 'sent')
                ->get();
            $pendingAmount = $pendingPayments->sum('total');
            $pendingCount = $pendingPayments->count();

            // Cash Flow Data (last 6 months)
            $cashFlowData = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();
                
                $inflow = Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->sum('amount');
                $outflow = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                    ->where('status', 'approved')
                    ->sum('total');
                
                $cashFlowData[] = [
                    'month' => $month->format('M'),
                    'inflow' => (float) $inflow,
                    'outflow' => (float) $outflow,
                ];
            }

            // Yearly totals for subValue
            $yearlyRevenue = null;
            $yearlyExpenses = null;
            $yearlyProfit = null;
            
            if ($timeFilter === 'month') {
                $yearlyRevenue = Sale::whereYear('sale_date', $now->year)->sum('amount');
                $yearlyExpenses = Expense::whereYear('expense_date', $now->year)
                    ->where('status', 'approved')
                    ->sum('total');
                $yearlyProfit = $yearlyRevenue - $yearlyExpenses;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'totalRevenue' => [
                        'value' => '$' . number_format($totalRevenue, 2),
                        'subValue' => $timeFilter === 'month' 
                            ? 'Yearly: $' . number_format($yearlyRevenue ?? 0, 2)
                            : 'Yearly',
                        'yearlyValue' => $timeFilter === 'month' ? '$' . number_format($yearlyRevenue ?? 0, 2) : null,
                        'change' => $revenueChange,
                    ],
                    'totalExpenses' => [
                        'value' => '$' . number_format($totalExpenses, 2),
                        'subValue' => $timeFilter === 'month' 
                            ? 'Yearly: $' . number_format($yearlyExpenses ?? 0, 2)
                            : 'Yearly',
                        'yearlyValue' => $timeFilter === 'month' ? '$' . number_format($yearlyExpenses ?? 0, 2) : null,
                        'change' => $expensesChange,
                    ],
                    'netProfit' => [
                        'value' => '$' . number_format($netProfit, 2),
                        'subValue' => $timeFilter === 'month' 
                            ? 'Yearly: $' . number_format($yearlyProfit ?? 0, 2)
                            : 'Yearly',
                        'yearlyValue' => $timeFilter === 'month' ? '$' . number_format($yearlyProfit ?? 0, 2) : null,
                        'change' => $profitChange,
                    ],
                    'outstandingInvoices' => [
                        'value' => '$' . number_format($outstandingAmount, 2),
                        'subValue' => $outstandingCount . ' invoices pending',
                    ],
                    'pendingPayments' => [
                        'value' => '$' . number_format($pendingAmount, 2),
                        'subValue' => $pendingCount . ' payments due',
                    ],
                    'cashFlowData' => $cashFlowData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching accountant statistics: ' . $e->getMessage()
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

