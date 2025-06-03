<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Location;
use App\Models\StaffCashTransfer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffCashTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transfers = StaffCashTransfer::with(['staff', 'location'])
            ->latest('date')
            ->paginate(15);

        return Inertia::render('StaffCashTransfers/Index', [
            'transfers' => $transfers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $staff = Staff::all(['id', 'name']);
        $locations = Location::all(['id', 'name']);

        return Inertia::render('StaffCashTransfers/Create', [
            'staff' => $staff,
            'locations' => $locations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        StaffCashTransfer::create($request->all());

        return redirect()->route('staff-cash-transfers.index')
            ->with('success', 'Cash transfer recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StaffCashTransfer $staffCashTransfer)
    {
        $staffCashTransfer->load(['staff', 'location']);

        return Inertia::render('StaffCashTransfers/Show', [
            'transfer' => $staffCashTransfer
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StaffCashTransfer $staffCashTransfer)
    {
        $staff = Staff::all(['id', 'name']);
        $locations = Location::all(['id', 'name']);

        return Inertia::render('StaffCashTransfers/Edit', [
            'transfer' => $staffCashTransfer,
            'staff' => $staff,
            'locations' => $locations
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StaffCashTransfer $staffCashTransfer)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        $staffCashTransfer->update($request->all());

        return redirect()->route('staff-cash-transfers.index')
            ->with('success', 'Cash transfer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StaffCashTransfer $staffCashTransfer)
    {
        $staffCashTransfer->delete();

        return redirect()->route('staff-cash-transfers.index')
            ->with('success', 'Cash transfer deleted successfully.');
    }
}
