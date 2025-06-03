<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffLoan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffLoanController extends Controller
{
    public function index()
    {
        $loans = StaffLoan::with('staff')
            ->latest()
            ->paginate(10);

        return Inertia::render('StaffLoans/Index', [
            'loans' => $loans
        ]);
    }

    public function create()
    {
        $staff = Staff::all(['id', 'name']);

        return Inertia::render('StaffLoans/Create', [
            'staff' => $staff
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        StaffLoan::create($validated);

        return redirect()->route('staff-loans.index')
            ->with('success', 'Staff loan created successfully.');
    }

    public function edit(StaffLoan $staffLoan)
    {
        $staff = Staff::all(['id', 'name']);

        return Inertia::render('StaffLoans/Edit', [
            'loan' => $staffLoan,
            'staff' => $staff
        ]);
    }

    public function update(Request $request, StaffLoan $staffLoan)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $staffLoan->update($validated);

        return redirect()->route('staff-loans.index')
            ->with('success', 'Staff loan updated successfully.');
    }

    public function destroy(StaffLoan $staffLoan)
    {
        $staffLoan->delete();

        return redirect()->route('staff-loans.index')
            ->with('success', 'Staff loan deleted successfully.');
    }
}
