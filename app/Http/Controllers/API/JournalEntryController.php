<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\Account;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Resources\JournalEntryResource;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', JournalEntry::class);
        
        $perPage = request()->query('per_page', 15);
        $journalEntries = JournalEntry::with(['debitAccount', 'creditAccount'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return JournalEntryResource::collection($journalEntries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJournalEntryRequest $request)
    {
        $this->authorize('create', JournalEntry::class);
        
        return DB::transaction(function () use ($request) {
            $accountService = app(AccountService::class);
            
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'date' => $request->date,
                'description' => $request->description,
                'debit_account_id' => $request->debit_account_id,
                'credit_account_id' => $request->credit_account_id,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'created_by' => auth()->id(),
            ]);
            
            // Update account balances using AccountService
            // Debit increases asset/expense accounts, decreases liability/equity/income
            // Credit increases liability/equity/income accounts, decreases asset/expense
            
            $debitAccount = Account::findOrFail($request->debit_account_id);
            $creditAccount = Account::findOrFail($request->credit_account_id);
            
            // Determine if debit increases or decreases balance based on account type
            $debitIsIncrease = in_array($debitAccount->category ?? $debitAccount->account_type, ['Assets', 'Expenses', 'asset', 'expense']);
            $creditIsIncrease = in_array($creditAccount->category ?? $creditAccount->account_type, ['Liabilities', 'Equity', 'Income', 'liability', 'equity', 'revenue']);
            
            if ($debitIsIncrease) {
                // Debit increases asset/expense balance
                $accountService->creditAccount(
                    $request->debit_account_id,
                    $request->amount,
                    'journal_entry',
                    $journalEntry->id,
                    $request->description,
                    auth()->id()
                );
            } else {
                // Debit decreases liability/equity/income balance
                // Allow negative for journal entries (liabilities can be paid down)
                $accountService->debitAccount(
                    $request->debit_account_id,
                    $request->amount,
                    'journal_entry',
                    $journalEntry->id,
                    $request->description,
                    auth()->id(),
                    true // allowNegative for journal entries
                );
            }
            
            if ($creditIsIncrease) {
                // Credit increases liability/equity/income balance
                $accountService->creditAccount(
                    $request->credit_account_id,
                    $request->amount,
                    'journal_entry',
                    $journalEntry->id,
                    $request->description,
                    auth()->id()
                );
            } else {
                // Credit decreases asset/expense balance
                // Allow negative for journal entries (assets can be reduced below zero in some cases)
                $accountService->debitAccount(
                    $request->credit_account_id,
                    $request->amount,
                    'journal_entry',
                    $journalEntry->id,
                    $request->description,
                    auth()->id(),
                    true // allowNegative for journal entries
                );
            }
            
            \App\Models\AuditLog::create([
                'action' => 'create_journal_entry',
                'user_id' => auth()->id(),
                'details' => json_encode($journalEntry->toArray()),
                'ip_address' => request()->ip(),
            ]);
            
            return (new JournalEntryResource($journalEntry->load(['debitAccount', 'creditAccount'])))
                ->response()
                ->setStatusCode(201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(JournalEntry $journalEntry)
    {
        $this->authorize('view', $journalEntry);
        
        return new JournalEntryResource($journalEntry->load(['debitAccount', 'creditAccount']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JournalEntry $journalEntry)
    {
        $this->authorize('delete', $journalEntry);
        
        // Journal entries should typically not be deleted, but if needed, reverse the transactions
        return response()->json(['message' => 'Deletion of journal entries is not allowed. Create a reversing entry instead.'], 403);
    }
}
