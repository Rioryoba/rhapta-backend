<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Create expense, debit account, create ledger and items in a single transaction
     */
    public function createExpense(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // compute totals from items
            $items = $data['items'] ?? [];
            $subtotal = 0;
            foreach ($items as $it) {
                $subtotal += (float) ($it['quantity'] * $it['unitPrice']);
            }
            $tax = isset($data['tax']) ? (float) $data['tax'] : 0.0;
            $discount = isset($data['discount']) ? (float) $data['discount'] : 0.0;
            $total = $subtotal + $tax - $discount;

            // create expense record
            $expense = Expense::create([
                'account_id' => $data['account_id'],
                'created_by' => $user->id ?? null,
                'requested_by' => $data['requested_by'] ?? null,
                'received_by' => $data['received_by'] ?? null,
                'description' => $data['description'] ?? null,
                'expense_date' => $data['expense_date'] ?? now(),
                'reference' => $data['reference'] ?? null,
                'amount' => $total,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'status' => 'paid',
                'currency' => $data['currency'] ?? 'USD',
            ]);

            // debit account via service (this uses its own transaction but lock is applied)
            $tx = $this->accountService->debitAccount($expense->account_id, $total, 'expense', $expense->id, $expense->description, $user->id ?? null);

            // create items
            foreach ($items as $it) {
                ExpenseItem::create([
                    'expense_id' => $expense->id,
                    'description' => $it['description'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unitPrice'],
                    'total' => $it['quantity'] * $it['unitPrice'],
                    'taxed' => $it['taxed'] ?? false,
                ]);
            }

            return ['expense' => $expense->load('items'), 'transaction' => $tx];
        });
    }

    /**
     * Reverse a paid expense by creating a credit transaction and updating status
     */
    public function reverseExpense(Expense $expense)
    {
        if ($expense->status !== 'paid') {
            throw new \Exception('Only paid expenses can be reversed');
        }

        return DB::transaction(function () use ($expense) {
            // Create credit transaction to reverse the debit
            $tx = $this->accountService->creditAccount(
                $expense->account_id,
                $expense->total,
                'expense_reversal',
                $expense->id,
                "Reversal for expense #{$expense->id}",
                $expense->created_by
            );

            // Update expense status to reversed
            $expense->update(['status' => 'reversed']);

            return ['expense' => $expense, 'transaction' => $tx];
        });
    }
}
