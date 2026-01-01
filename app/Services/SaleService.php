<?php

namespace App\Services;

use App\Models\Sale;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;

class SaleService
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Create a sale, credit the account, and create ledger entry.
     */
    public function createSale(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // Ensure unit_price is set (handle both camelCase and snake_case)
            $unitPrice = $data['unit_price'] ?? $data['unitPrice'] ?? 0;
            $quantity = $data['quantity'] ?? 0;
            
            // Calculate total amount if not provided
            $totalAmount = $data['total_amount'] ?? ($quantity * $unitPrice);
            
            // Create sale record
            $sale = Sale::create([
                'sale_date' => $data['sale_date'] ?? $data['saleDate'] ?? now(),
                'mine_site' => $data['mine_site'] ?? $data['mineSite'] ?? null,
                'mineral_type' => $data['mineral_type'] ?? $data['mineralType'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'customer_name' => $data['customer_name'] ?? $data['customerName'] ?? '',
                'payment_status' => $data['payment_status'] ?? $data['paymentStatus'] ?? 'Pending',
                'region' => $data['region'] ?? null,
                'account_id' => $data['account_id'] ?? $data['accountId'] ?? null,
                'product_id' => $data['product_id'] ?? $data['productId'] ?? null,
                'description' => $data['description'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            // Credit account via service if account_id is provided
            $tx = null;
            if ($sale->account_id) {
                $tx = $this->accountService->creditAccount(
                    $sale->account_id,
                    $totalAmount,
                    'sale',
                    $sale->id,
                    $sale->description,
                    $user->id ?? null
                );
            }

            return ['sale' => $sale, 'transaction' => $tx];
        });
    }
}
