<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IncomeSalesSummaryController extends Controller
{
    /**
     * Get summary statistics for Income & Sales Management
     */
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Calculate total monthly revenue (from paid sales)
        $monthlySales = Sale::whereMonth('sale_date', $currentMonth)
            ->whereYear('sale_date', $currentYear)
            ->where('payment_status', 'Paid')
            ->get();

        $totalMonthlyRevenue = $monthlySales->sum('total_amount');

        // Total sales count
        $totalSalesCount = Sale::count();

        // Pending invoices
        $pendingInvoices = Invoice::whereIn('status', ['unpaid', 'overdue'])->get();
        $pendingInvoicesCount = $pendingInvoices->count();
        $pendingInvoicesAmount = $pendingInvoices->sum('total');

        // Average price (from all sales)
        $allSales = Sale::where('unit_price', '>', 0)->get();
        $averagePrice = $allSales->count() > 0 
            ? $allSales->avg('unit_price') 
            : 0;

        return response()->json([
            'totalMonthlyRevenue' => (float) $totalMonthlyRevenue,
            'totalSalesCount' => $totalSalesCount,
            'pendingInvoicesCount' => $pendingInvoicesCount,
            'pendingInvoicesAmount' => (float) $pendingInvoicesAmount,
            'averagePrice' => (float) $averagePrice,
        ]);
    }
}
