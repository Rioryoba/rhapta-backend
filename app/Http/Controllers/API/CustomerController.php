<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $this->authorize('viewAny', Customer::class);
    $filter = new \App\Filters\CustomerFilter();
    $customers = $filter->transform(request())->paginate();
    return \App\Http\Resources\CustomerResource::collection($customers);
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
    public function store(StoreCustomerRequest $request)
    {
    $this->authorize('create', Customer::class);
    // ...create customer logic...
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
    $this->authorize('view', $customer);
    // ...show customer logic...
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
    $this->authorize('update', $customer);
    // ...update customer logic...
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
    $this->authorize('delete', $customer);
    // ...delete customer logic...
    }
}
