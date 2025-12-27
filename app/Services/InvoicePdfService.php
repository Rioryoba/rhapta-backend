<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use App\Models\Setting;

class InvoicePdfService
{
    protected $cachePath = 'invoice_pdfs';

    public function generatePdf(Invoice $invoice): string
    {
        $settings = Setting::first();
        $cacheDir = storage_path('app/' . $this->cachePath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        $cacheFile = $cacheDir . "/invoice_{$invoice->id}.pdf";

        // Determine if cache is valid
        $invoiceUpdated = $invoice->updated_at?->timestamp ?? 0;
        $settingsUpdated = $settings?->updated_at?->timestamp ?? 0;
        $cacheValid = false;
        if (file_exists($cacheFile)) {
            $cacheMTime = filemtime($cacheFile);
            if ($cacheMTime > $invoiceUpdated && $cacheMTime > $settingsUpdated) {
                $cacheValid = true;
            }
        }

        if ($cacheValid) {
            return file_get_contents($cacheFile);
        }

        $data = [
            'invoice' => $invoice->load(['invoiceItems', 'customer']),
            'companyLogo' => $settings?->logo_path ? public_path('storage/' . $settings->logo_path) : null,
            'companyName' => $settings?->company_name,
            'companyAddress' => $settings?->company_address,
            'companyEmail' => $settings?->company_email,
            'companyPhone' => $settings?->company_phone,
            'companyWebsite' => $settings?->company_website,
        ];
        $pdf = Pdf::loadView('invoice.pdf', $data);
        $output = $pdf->output();
        file_put_contents($cacheFile, $output);
        return $output;
    }
}
