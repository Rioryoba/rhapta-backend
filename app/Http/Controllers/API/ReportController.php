<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ExpenseReportService;
use App\Http\Resources\ExpenseReportResource;
use App\Http\Resources\ExpenseResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Generate expense report for a date range.
     */
    public function expenseReport(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Expense::class);

        $request->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date', 'after_or_equal:fromDate'],
        ]);

        $service = app(ExpenseReportService::class);
        $reportData = $service->generateExpenseReport(
            $request->fromDate,
            $request->toDate
        );

        return new ExpenseReportResource($reportData);
    }

    /**
     * Download expense report in PDF or CSV format.
     * Query params: fromDate, toDate, format (pdf|csv)
     */
    public function downloadExpenseReport(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Expense::class);

        $request->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date', 'after_or_equal:fromDate'],
            'format' => ['nullable', 'in:pdf,csv'],
        ]);

        $format = $request->get('format', 'pdf');

        $service = app(ExpenseReportService::class);
        $reportData = $service->generateExpenseReport(
            $request->fromDate,
            $request->toDate
        );

        // Prepare formatted expenses via Resource so fields match API output
        $formattedExpenses = ExpenseResource::collection($reportData['expenses'])->resolve();
        $reportData['expenses'] = $formattedExpenses;
        $reportData['generatedBy'] = auth()->user()->email ?? 'system';
        $reportData['generatedAt'] = now()->toIso8601String();

        if ($format === 'csv') {
            $filename = 'expenses-report-' . Str::slug($reportData['reportDate']) . '.csv';

            $callback = function () use ($reportData) {
                $out = fopen('php://output', 'w');

                // Write summary header
                fputcsv($out, ['Report Date', $reportData['reportDate']]);
                fputcsv($out, ['From', $reportData['dateRange']['from'], 'To', $reportData['dateRange']['to']]);
                fputcsv($out, []);

                // Summary totals
                fputcsv($out, ['Total Expenses', $reportData['summary']['totalExpenses']]);
                fputcsv($out, ['Total Expense Count', $reportData['summary']['totalExpenseCount']]);
                fputcsv($out, ['Total Items', $reportData['summary']['totalItems']]);
                fputcsv($out, []);

                // Expense rows header
                fputcsv($out, [
                    'Expense ID', 'Account Name', 'Description', 'Expense Date', 'Reference',
                    'Subtotal', 'Tax', 'Discount', 'Total', 'Status',
                    'Item Description', 'Item Quantity', 'Item Unit Price', 'Item Total', 'Item Taxed'
                ]);

                foreach ($reportData['expenses'] as $exp) {
                    $base = [
                        $exp['id'] ?? '',
                        $exp['accountName'] ?? '',
                        $exp['description'] ?? '',
                        $exp['expenseDate'] ?? '',
                        $exp['reference'] ?? '',
                        $exp['subtotal'] ?? '',
                        $exp['tax'] ?? '',
                        $exp['discount'] ?? '',
                        $exp['total'] ?? '',
                        $exp['status'] ?? '',
                    ];

                    $items = $exp['items'] ?? [];
                    if (count($items) === 0) {
                        fputcsv($out, array_merge($base, ['', '', '', '', '']));
                    } else {
                        foreach ($items as $it) {
                            $row = array_merge($base, [
                                $it['description'] ?? '',
                                $it['quantity'] ?? '',
                                $it['unitPrice'] ?? '',
                                $it['total'] ?? '',
                                isset($it['taxed']) ? ($it['taxed'] ? 'true' : 'false') : '',
                            ]);
                            fputcsv($out, $row);
                        }
                    }
                }

                fclose($out);
            };

            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/csv',
                'Cache-Control' => 'no-store, no-cache',
            ]);
        }

        // Default: PDF
        $pdf = Pdf::loadView('reports.expenses', ['report' => $reportData]);
        $filename = 'expenses-report-' . Str::slug($reportData['reportDate']) . '.pdf';

        return $pdf->download($filename);
    }
}
