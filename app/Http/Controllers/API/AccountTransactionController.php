<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccountTransaction;
use App\Http\Requests\StoreAccountTransactionRequest;
use App\Services\AccountService;
use App\Http\Resources\AccountTransactionResource;
use Illuminate\Http\Response;

class AccountTransactionController extends Controller
{
    /**
     * Display a listing of account transactions.
     */
    public function index()
    {
        $transactions = AccountTransaction::with(['account'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return AccountTransactionResource::collection($transactions);
    }

    /**
     * Store a manual account transaction (deposit/withdrawal).
     */
    public function store(StoreAccountTransactionRequest $request)
    {
        $accountService = app(AccountService::class);
        try {
            if ($request->type === 'credit') {
                $tx = $accountService->creditAccount(
                    $request->account_id,
                    $request->amount,
                    'manual_deposit',
                    null,
                    $request->description,
                    $request->user()->id
                );
            } else {
                $tx = $accountService->debitAccount(
                    $request->account_id,
                    $request->amount,
                    'manual_withdrawal',
                    null,
                    $request->description,
                    $request->user()->id
                );
            }

            return response()->json(new AccountTransactionResource($tx), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
