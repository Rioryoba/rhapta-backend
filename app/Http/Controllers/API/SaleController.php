<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Services\SaleService;
use App\Http\Resources\SaleResource;
use Illuminate\Http\Response;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['account', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return SaleResource::collection($sales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        $service = app(SaleService::class);
        try {
            // Get validated data and merge with prepared snake_case keys
            $validated = $request->validated();
            $allData = $request->all();
            
            // Merge validated camelCase with prepared snake_case data
            $data = array_merge($validated, [
                'sale_date' => $allData['sale_date'] ?? null,
                'mine_site' => $allData['mine_site'] ?? null,
                'mineral_type' => $allData['mineral_type'] ?? null,
                'unit_price' => $allData['unit_price'] ?? null,
                'total_amount' => $allData['total_amount'] ?? null,
                'customer_name' => $allData['customer_name'] ?? null,
                'payment_status' => $allData['payment_status'] ?? null,
                'region' => $allData['region'] ?? null,
                'account_id' => $allData['account_id'] ?? null,
                'product_id' => $allData['product_id'] ?? null,
            ]);
            
            \Log::info('Sale creation data:', $data);
            $result = $service->createSale($data, $request->user());
            return response()->json(new SaleResource($result['sale']), Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Sale creation error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => $e->getMessage(),
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load(['account', 'product']);
        return new SaleResource($sale);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        try {
            // Get all data after validation (includes prepared snake_case keys)
            $data = $request->all();
            // Only update fields that are present in the request
            $updateData = array_filter($data, function($key) {
                return in_array($key, [
                    'sale_date', 'mine_site', 'mineral_type', 'quantity', 
                    'unit_price', 'total_amount', 'customer_name', 
                    'payment_status', 'region', 'account_id', 'product_id', 
                    'description', 'reference'
                ]);
            }, ARRAY_FILTER_USE_KEY);
            $sale->update($updateData);
            $sale->load(['account', 'product']);
            return new SaleResource($sale);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        try {
            $sale->delete();
            return response()->json(['message' => 'Sale deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
