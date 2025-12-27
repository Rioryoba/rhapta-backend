<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Http\Resources\BudgetResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Budget::query();

        // Filter by type (Department or Project)
        if ($request->has('type') && $request->type !== 'All') {
            $query->where('type', $request->type);
        }

        // Filter by period (Month, Quarter, Year)
        if ($request->has('period') && $request->period !== 'All') {
            $query->where('period', $request->period);
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Search by name or period value
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('period_value', 'like', "%{$search}%");
            });
        }

        $budgets = $query->with(['department', 'project'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return BudgetResource::collection($budgets->appends($request->query()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBudgetRequest $request)
    {
        $data = $request->validated();
        $budget = Budget::create($data);
        $budget->load(['department', 'project']);
        
        return response()->json(new BudgetResource($budget), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        $budget->load(['department', 'project']);
        return new BudgetResource($budget);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        $data = $request->validated();
        $budget->update($data);
        $budget->load(['department', 'project']);
        
        return new BudgetResource($budget);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        $budget->delete();
        return response()->json(['message' => 'Budget deleted successfully'], Response::HTTP_OK);
    }

    /**
     * Get forecast data based on historical budgets
     */
    public function forecast(Request $request)
    {
        $budgets = Budget::orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Simple forecast calculation
        $historicalData = $budgets->map(function($budget) {
            return [
                'period' => $budget->period_value,
                'actual' => (float) $budget->actual_amount,
                'budget' => (float) $budget->budget_amount,
            ];
        });

        if ($historicalData->count() < 2) {
            return response()->json([
                'forecast' => [],
                'message' => 'Insufficient data for forecasting'
            ]);
        }

        $avgActual = $historicalData->avg('actual');
        $avgBudget = $historicalData->avg('budget');
        $trend = $avgBudget > 0 ? ($avgActual - $avgBudget) / $avgBudget : 0;

        // Generate forecast for next 3 periods
        $forecast = [];
        $periods = ['Next Month', 'Next Quarter', 'Next Year'];
        
        for ($i = 0; $i < 3; $i++) {
            $projectedBudget = $avgBudget * (1 + ($trend * ($i + 1) * 0.1));
            $projectedActual = $avgActual * (1 + ($trend * ($i + 1) * 0.1));
            
            $forecast[] = [
                'period' => $periods[$i],
                'projectedBudget' => max(0, $projectedBudget),
                'projectedActual' => max(0, $projectedActual),
                'projectedVariance' => max(0, $projectedActual) - max(0, $projectedBudget),
            ];
        }

        return response()->json([
            'forecast' => $forecast,
            'historical' => $historicalData->take(6)->values(),
        ]);
    }
}
