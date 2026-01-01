<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::all();
        return response()->json($sites);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $site = Site::create($validated);
        return response()->json($site, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        return response()->json($site);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $site->update($validated);
        return response()->json($site);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        $site->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
