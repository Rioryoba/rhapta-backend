<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createInvoiceWithItems(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Handle customer - create if customerName provided, otherwise use customerId
            $customerId = $data['customerId'] ?? null;
            if (!$customerId && isset($data['customerName'])) {
                $customerName = trim($data['customerName']);
                if (!empty($customerName)) {
                    $customer = Customer::firstOrCreate(
                        ['name' => $customerName],
                        ['email' => $data['customerEmail'] ?? null, 'phone' => $data['customerPhone'] ?? null]
                    );
                    $customerId = $customer->id;
                }
            }
            
            // Ensure we have a customer ID
            if (!$customerId) {
                throw new \InvalidArgumentException('Either customerId or customerName must be provided');
            }

            // Map payment status from frontend format to backend format
            $status = $data['status'] ?? 'unpaid';
            if (isset($data['paymentStatus'])) {
                $statusMap = [
                    'Paid' => 'paid',
                    'Pending' => 'unpaid',
                    'Overdue' => 'overdue',
                ];
                $status = $statusMap[$data['paymentStatus']] ?? $data['paymentStatus'];
            }

            // Map camelCase to snake_case for invoice
            $invoiceData = [
                'invoice_no' => $data['invoiceNo'] ?? 'TEMP',
                'customer_id' => $customerId,
                'invoice_date' => $data['invoiceDate'] ?? $data['dateIssued'] ?? null,
                'due_date' => $data['dueDate'] ?? null,
                'status' => $status,
                'tax' => isset($data['tax']) ? (float)$data['tax'] : 0,
                'discount' => isset($data['discount']) ? (float)$data['discount'] : 0,
                'created_by' => $data['created_by'] ?? null,
            ];
            $items = $data['items'] ?? [];
            $subtotal = 0;
            foreach ($items as &$item) {
                // Map camelCase to snake_case for item
                $item['unit_price'] = $item['unitPrice'] ?? 0;
                $item['total'] = $item['quantity'] * $item['unit_price'];
                $subtotal += $item['total'];
            }
            // Use provided subtotal/total if items array is empty
            if (empty($items) && isset($data['subtotal'])) {
                $subtotal = (float)$data['subtotal'];
            } elseif (empty($items) && isset($data['total'])) {
                $subtotal = (float)$data['total'];
            }
            // Ensure correct calculation
            $invoiceData['subtotal'] = $subtotal;
            $invoiceData['total'] = isset($data['total']) ? (float)$data['total'] : ($subtotal + ($invoiceData['tax'] ?? 0) - ($invoiceData['discount'] ?? 0));
            // Create invoice
            $invoice = Invoice::create($invoiceData);
            // Set default invoice_no if not provided or if 'TEMP' was used
            if (empty($data['invoiceNo'])) {
                $paddedId = str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
                $invoice->invoice_no = 'INV-' . $paddedId;
                $invoice->save();
            }
            // Create invoice items
            foreach ($items as $item) {
                $item['invoice_id'] = $invoice->id;
                InvoiceItem::create([
                    'invoice_id' => $item['invoice_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                ]);
            }
            return $invoice->fresh(['customer', 'invoiceItems']);
        });
    }
}
