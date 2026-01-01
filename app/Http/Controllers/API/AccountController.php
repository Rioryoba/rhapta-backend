<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Account;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountResourceCollection;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Account::class);

        $perPage = request()->query('per_page', 100);
        $accounts = Account::with('parent', 'children')
            ->orderBy('code', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        return new AccountResourceCollection($accounts);
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
    public function store(StoreAccountRequest $request)
    {
        // Authorize using AccountPolicy::create (only accountant and ceo allowed)
        $this->authorize('create', Account::class);

        $data = $request->validated();

        // Auto-generate account_number from code if not provided (for backward compatibility)
        // Since account_number is now nullable, we can leave it null if not provided
        if (empty($data['account_number']) && !empty($data['code'])) {
            // Try to extract numeric value from code
            $codeValue = preg_replace('/[^0-9]/', '', $data['code']);
            if (!empty($codeValue)) {
                $proposedNumber = (int) $codeValue;
                // Only set if it doesn't conflict with existing accounts
                if (!Account::where('account_number', $proposedNumber)->exists()) {
                    $data['account_number'] = $proposedNumber;
                }
                // If it exists, leave as null (code is the primary identifier now)
            }
            // If code has no numbers, leave account_number as null
        }

        // Set account_type from category if not provided (for backward compatibility)
        if (empty($data['account_type']) && !empty($data['category'])) {
            $categoryMap = [
                'Assets' => 'asset',
                'Liabilities' => 'liability',
                'Equity' => 'equity',
                'Income' => 'revenue',
                'Expenses' => 'expense',
            ];
            $data['account_type'] = $categoryMap[$data['category']] ?? 'asset';
        }

        $account = Account::create($data);
        
        \App\Models\AuditLog::create([
            'action' => 'create_account',
            'user_id' => auth()->id(),
            'details' => json_encode($account->toArray()),
            'ip_address' => request()->ip(),
        ]);

        return (new AccountResource($account))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        $this->authorize('view', $account);

        return new AccountResource($account);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        // Ensure only authorized users (AccountPolicy::update) can update
        $this->authorize('update', $account);

        $data = $request->validated();

        $account->update($data);
        
        \App\Models\AuditLog::create([
            'action' => 'update_account',
            'user_id' => auth()->id(),
            'details' => json_encode($account->toArray()),
            'ip_address' => request()->ip(),
        ]);

        return new AccountResource($account);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        // Deletion is forbidden by policy; authorize will throw 403
        $this->authorize('delete', $account);

        // If policy ever allows, prevent accidental deletes by default.
        return response()->json(['message' => 'Deletion of accounts is forbidden'], 403);
    }
}
