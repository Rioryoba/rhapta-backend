<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin:20px 3em; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .logo { width: 200px;  margin-right: 10px; }
        .company-block { display: flex; align-items: flex-start; flex-direction: column; }
        .company-info { font-size: 13px; line-height: 1.5; margin-left: 10px; }
        .quote-title { color: #3a5ca8; font-size: 32px; font-weight: bold; letter-spacing: 2px; text-align: right; padding-bottom: 10px; }
        .summary-table { border: 1px solid #3a5ca8; border-collapse: collapse; font-size: 13px; float: right; margin-top: 0; }
        .summary-table td, .summary-table th { border: 1px solid #3a5ca8; padding: 4px 8px; }
        .summary-table th { background: #e7ecf7; color: #3a5ca8; }
        .customer-block { margin: 20px 0 10px 0; }
        .customer-label { background: #3a5ca8; color: #fff; padding:4px 80px 4px 12px; font-weight: bold; font-size: 14px; letter-spacing: 1px; display :inline-block; }
        .customer-info { font-size: 13px; margin-left: 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
        .items-table th, .items-table td { border: 1px solid #3a5ca8; padding: 6px 8px; }
        .items-table th { background: #3a5ca8; color: #fff; font-weight: bold; }
        .items-table td { background: #f8faff; }
        .term-block-cover { display: block }
        .terms-block { margin-top: 24px; display: inline-block;  }
        .terms-label { background: #3a5ca8; color: #fff; padding: 4px 12px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        .terms-list { font-size: 12px; margin: 0; padding: 8px 0 0 0; }
        .totals-table { float: right; margin-top: 16px; border-collapse: collapse; font-size: 13px; }
        .totals-table td { padding: 4px 8px; border: none; }
        .totals-table tr:last-child td { font-weight: bold; color: #3a5ca8; font-size: 16px; }
        .footer { margin-top: 40px; text-align: center; font-size: 13px; color: #3a5ca8; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-block">
            @if($companyLogo)
                <img src="{{ $companyLogo }}" class="logo" alt="Company Logo">
            @endif
            <div class="company-info">
                <div style="font-size:18px; font-weight:bold; color:#3a5ca8;">{{ $companyName }}</div>
                <div>{{ $companyAddress }}</div>
                <div>Email: {{ $companyEmail }}</div>
                <div>Phone: {{ $companyPhone }}</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div class="quote-title">INVOICE</div>
            <table class="summary-table">
                <tr><th>DATE</th><td>{{ $invoice->invoice_date }}</td></tr>
                <tr><th>INVOICE #</th><td>{{ $invoice->invoice_no }}</td></tr>
                <tr><th>DUE DATE</th><td>{{ $invoice->due_date }}</td></tr>
            </table>
        </div>
    </div>

    <div class="customer-block">
        <div class="customer-label">BILL TO </div>
        <div class="customer-info">
            <div>{{ $invoice->customer->name ?? '' }}</div>
            <div>{{ $invoice->customer->company_name ?? '' }}</div>
            <div>{{ $invoice->customer->address ?? '' }}</div>
            <div>{{ $invoice->customer->city ?? '' }} {{ $invoice->customer->zip ?? '' }}</div>
            <div>{{ $invoice->customer->phone ?? '' }}</div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>DESCRIPTION</th>
                <th>UNIT PRICE</th>
                <th>QTY</th>
                <th>TAXED</th>
                <th>AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->invoiceItems as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>@if($item->taxed ?? false) X @endif</td>
                <td>{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

   
    <table class="totals-table">
        <tr><td>Subtotal</td><td style="text-align:right;">{{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>Taxable</td><td style="text-align:right;">{{ number_format($invoice->taxable ?? 0, 2) }}</td></tr>
        <tr><td>Tax rate</td><td style="text-align:right;">{{ number_format($invoice->tax_rate ?? 0, 2) }}%</td></tr>
        <tr><td>Tax due</td><td style="text-align:right;">{{ number_format($invoice->tax_due ?? 0, 2) }}</td></tr>
        <tr><td>Other</td><td style="text-align:right;">{{ number_format($invoice->other ?? 0, 2) }}</td></tr>
        <tr><td style="color:#3a5ca8;">TOTAL</td><td style="text-align:right; color:#3a5ca8;">{{ number_format($invoice->total, 2) }}</td></tr>
    </table>
    
    <div class="term-block-cover">
     <div class="terms-block">
        <div class="terms-label">TERMS AND CONDITIONS</div>
        <ol class="terms-list">
            <li>Customer will be billed after indicating acceptance of this quote.</li>
            <li>Payment will be due prior to delivery of service and goods.</li>
            <li>Please contact us for any questions regarding this invoice.</li>
        </ol>
     </div>
    </div>


    <div class="footer">
        Thank You For Your Business!
    </div>
</body>
</html>
