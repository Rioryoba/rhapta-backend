<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'site'])->get();
        return response()->json($purchaseOrders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'site_id' => 'nullable|exists:sites,id',
            'items' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'nullable|in:open,ordered,received,cancelled',
            'order_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
        ]);

        $purchaseOrder = PurchaseOrder::create($validated);
        $purchaseOrder->load(['supplier', 'site']);
        return response()->json($purchaseOrder, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'site']);
        return response()->json($purchaseOrder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'site_id' => 'nullable|exists:sites,id',
            'items' => 'nullable|string',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'status' => 'nullable|in:open,ordered,received,cancelled',
            'order_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
        ]);

        $purchaseOrder->update($validated);
        $purchaseOrder->load(['supplier', 'site']);
        return response()->json($purchaseOrder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
