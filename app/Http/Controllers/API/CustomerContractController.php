<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\CustomerContract;
use App\Http\Requests\StoreCustomerContractRequest;
use App\Http\Requests\UpdateCustomerContractRequest;

class CustomerContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreCustomerContractRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerContract $customerContract)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerContract $customerContract)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerContractRequest $request, CustomerContract $customerContract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerContract $customerContract)
    {
        //
    }
}
