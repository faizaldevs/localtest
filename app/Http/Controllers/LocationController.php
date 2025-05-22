<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Locations/Index', [
            'locations' => Location::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Locations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')->with('success', 'Location created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        return Inertia::render('Locations/Show', [
            'location' => $location
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        return Inertia::render('Locations/Edit', [
            'location' => $location
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')->with('success', 'Location updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Location deleted successfully');
    }
}
