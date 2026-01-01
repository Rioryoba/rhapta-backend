<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\Carbon;

class ExpenseReportService
{
    /**
     * Generate expense report for a date range.
     */
    public function generateExpenseReport($fromDate, $toDate)
    {
        // Parse dates
        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->endOfDay();

        // Query expenses with items and relations.
        // Include expenses whose `expense_date` falls in the range,
        // or expenses where `expense_date` is null but `created_at` falls in the range.
        $expenses = Expense::with(['items', 'account'])
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('expense_date', [$from, $to])
                  ->orWhere(function ($q2) use ($from, $to) {
                      $q2->whereNull('expense_date')
                         ->whereBetween('created_at', [$from, $to]);
                  });
            })
            ->orderByRaw("COALESCE(expense_date, created_at) ASC")
            ->get();

        // Calculate summary
        $totalExpenses = $expenses->sum('total');
        $totalExpenseCount = $expenses->count();
        $totalItems = $expenses->sum(function ($expense) {
            return $expense->items->count();
        });

        // Group by account
        $byAccount = $expenses->groupBy('account_id')->map(function ($group) {
            return [
                'accountName' => optional($group->first()->account)->account_name,
                'total' => $group->sum('total'),
                'count' => $group->count(),
            ];
        })->values();

        // Group by status
        $byStatus = $expenses->groupBy('status')->map(function ($group) {
            return [
                'status' => $group->first()->status,
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ];
        })->values();

        return [
            'reportDate' => $from->format('Y-m-d'),
            'dateRange' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'summary' => [
                'totalExpenses' => $totalExpenses,
                'totalExpenseCount' => $totalExpenseCount,
                'totalItems' => $totalItems,
                'byAccount' => $byAccount,
                'byStatus' => $byStatus,
            ],
            'expenses' => $expenses,
        ];
    }
}
