<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Http\Response;

class InvoicePdfController extends Controller
{
    public function download($id, InvoicePdfService $pdfService)
    {
        $invoice = Invoice::findOrFail($id);
        // Check if PDF is cached
        $cacheDir = storage_path('app/invoice_pdfs');
        $cacheFile = $cacheDir . "/invoice_{$invoice->id}.pdf";
        $settings = \App\Models\Setting::first();
        $invoiceUpdated = $invoice->updated_at?->timestamp ?? 0;
        $settingsUpdated = $settings?->updated_at?->timestamp ?? 0;
        $cacheValid = false;
        if (file_exists($cacheFile)) {
            $cacheMTime = filemtime($cacheFile);
            if ($cacheMTime > $invoiceUpdated && $cacheMTime > $settingsUpdated) {
                $cacheValid = true;
            }
        }

        if (!$cacheValid) {
            // Dispatch async job to generate PDF
            \App\Jobs\GenerateInvoicePdfJob::dispatch($invoice->id);
            return response()->json([
                'status' => 'processing',
                'message' => 'Invoice PDF is being generated. Please try again shortly.'
            ], 202);
        }

        $pdfContent = file_get_contents($cacheFile);
        $filename = 'invoice_' . $invoice->invoice_no . '.pdf';
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
