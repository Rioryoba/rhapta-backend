<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Services\ExpenseService;
use App\Http\Resources\ExpenseResource;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Expense::class);

        $filter = new \App\Filters\ExpenseFilter();
        $query = Expense::query();
        $filter->setModelQuery($query);
        $filteredQuery = $filter->transform(request());

        $expenses = $filteredQuery->with(['items', 'account', 'requester', 'receiver', 'site', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return ExpenseResource::collection($expenses->appends(request()->query()));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used in API context
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $this->authorize('create', Expense::class);
        $service = app(ExpenseService::class);
        try {
            $result = $service->createExpense($request->validated(), $request->user());
            return response()->json(new ExpenseResource($result['expense']), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);
        
        $expense->load(['items', 'account', 'requester', 'receiver']);
        return new ExpenseResource($expense);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        // Not used in API context
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $this->authorize('update', $expense);
        
        $service = app(ExpenseService::class);
        try {
            $result = $service->updateExpense($expense, $request->validated());
            return new ExpenseResource($result['expense']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);
        
        $service = app(ExpenseService::class);
        try {
            // If expense is paid, create a reversal transaction
            if ($expense->status === 'paid') {
                $result = $service->reverseExpense($expense);
                return response()->json(['message' => 'Expense reversed successfully']);
            }
            
            // If pending, just delete
            $expense->delete();
            return response()->json(['message' => 'Expense deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
