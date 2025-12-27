<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InvoiceItem;
use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Http\Resources\InvoiceItemResource;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', InvoiceItem::class);
        $items = InvoiceItem::paginate();
        return InvoiceItemResource::collection($items);
    }

    public function store(StoreInvoiceItemRequest $request)
    {
        $this->authorize('create', InvoiceItem::class);
        $item = InvoiceItem::create($request->validated());
        return new InvoiceItemResource($item);
    }

    public function show(InvoiceItem $invoiceItem)
    {
        $this->authorize('view', $invoiceItem);
        return new InvoiceItemResource($invoiceItem);
    }

    public function update(UpdateInvoiceItemRequest $request, InvoiceItem $invoiceItem)
    {
        $this->authorize('update', $invoiceItem);
        $invoiceItem->update($request->validated());
        return new InvoiceItemResource($invoiceItem);
    }

    public function destroy(InvoiceItem $invoiceItem)
    {
        $this->authorize('delete', $invoiceItem);
        $invoiceItem->delete();
        return response()->json(['message' => 'Invoice item deleted successfully']);
    }
}
