<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;

class AccountService
{
    /**
     * Debit an account (subtract amount) and create ledger entry.
     * Returns the created AccountTransaction
     */
    public function debitAccount(int $accountId, float $amount, string $referenceType = null, $referenceId = null, string $description = null, $userId = null, bool $allowNegative = false): AccountTransaction
    {
        return DB::transaction(function () use ($accountId, $amount, $referenceType, $referenceId, $description, $userId, $allowNegative) {
            $account = Account::lockForUpdate()->findOrFail($accountId);
            $current = (float) $account->balance;
            if (!$allowNegative && $current < $amount) {
                throw new \Exception("Insufficient funds: current balance {$current}");
            }
            $newBalance = $current - $amount;
            $account->balance = $newBalance;
            $account->save();

            $tx = AccountTransaction::create([
                'account_id' => $account->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'created_by' => $userId,
            ]);

            return $tx;
        });
    }

    public function creditAccount(int $accountId, float $amount, string $referenceType = null, $referenceId = null, string $description = null, $userId = null): AccountTransaction
    {
        return DB::transaction(function () use ($accountId, $amount, $referenceType, $referenceId, $description, $userId) {
            $account = Account::lockForUpdate()->findOrFail($accountId);
            $current = (float) $account->balance;
            $newBalance = $current + $amount;
            $account->balance = $newBalance;
            $account->save();

            return AccountTransaction::create([
                'account_id' => $account->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'created_by' => $userId,
            ]);
        });
    }
}
