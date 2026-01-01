<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $this->authorize('viewAny', Invoice::class);
    $filter = new \App\Filters\InvoiceFilter();
    $query = Invoice::query();
    $filter->setModelQuery($query);
    $filteredQuery = $filter->transform(request());
    $invoices = $filteredQuery->with(['customer', 'invoiceItems'])->paginate();
    return \App\Http\Resources\InvoiceResource::collection($invoices->appends(request()->query()));
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
    public function store(StoreInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);
        $data = $request->validated();
        $data['items'] = $request->input('items', []);
        $data['created_by'] = auth()->id();
        $invoice = app(\App\Services\InvoiceService::class)->createInvoiceWithItems($data);
        $invoice->load('customer', 'invoiceItems');
        return new \App\Http\Resources\InvoiceResource($invoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        return new \App\Http\Resources\InvoiceResource($invoice);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $data = $request->validated();
        
        // Map frontend fields to database fields
        if (isset($data['invoiceNo'])) {
            $data['invoice_no'] = $data['invoiceNo'];
        }
        if (isset($data['customerId'])) {
            $data['customer_id'] = $data['customerId'];
        }
        if (isset($data['invoiceDate'])) {
            $data['invoice_date'] = $data['invoiceDate'];
        }
        if (isset($data['dueDate'])) {
            $data['due_date'] = $data['dueDate'];
        }
        if (isset($data['paymentStatus'])) {
            // Map frontend status to database status
            $statusMap = [
                'Paid' => 'paid',
                'Pending' => 'unpaid',
                'Overdue' => 'overdue',
            ];
            $data['status'] = $statusMap[$data['paymentStatus']] ?? $data['paymentStatus'];
        }
        if (isset($data['amount'])) {
            $data['total'] = $data['amount'];
        }

        $invoice->update($data);
        $invoice->load('customer', 'invoiceItems');
        return new \App\Http\Resources\InvoiceResource($invoice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted successfully'], 200);
    }

    /**
     * Record payment for an invoice
     */
    public function payInvoice(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        // Create payment record
        $payment = \App\Models\Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $validated['amount'],
            'payment_date' => now(),
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'status' => 'completed',
        ]);

        // Update invoice status if fully paid
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        $invoice->load('customer', 'invoiceItems', 'payments');
        return new \App\Http\Resources\InvoiceResource($invoice);
    }
}
