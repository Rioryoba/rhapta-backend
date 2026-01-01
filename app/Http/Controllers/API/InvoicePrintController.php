<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;

class InvoicePrintController extends Controller
{
    public function show($id)
    {
        $invoice = Invoice::with(['invoiceItems', 'customer'])->findOrFail($id);
        $settings = Setting::first();
        return view('invoice.pdf', [
            'invoice' => $invoice,
            'companyLogo' => $settings?->logo_path ? asset('storage/' . $settings->logo_path) : null,
            'companyName' => $settings?->company_name,
            'companyAddress' => $settings?->company_address,
            'companyEmail' => $settings?->company_email,
            'companyPhone' => $settings?->company_phone,
        ]);
    }
}
