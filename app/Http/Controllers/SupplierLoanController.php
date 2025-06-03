<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierLoan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierLoanController extends Controller
{
    public function index()
    {
        $loans = SupplierLoan::with('supplier')
            ->latest()
            ->paginate(10);

        return Inertia::render('SupplierLoans/Index', [
            'loans' => $loans
        ]);
    }

    public function create()
    {
        $suppliers = Supplier::all(['id', 'name']);

        return Inertia::render('SupplierLoans/Create', [
            'suppliers' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        SupplierLoan::create($validated);

        return redirect()->route('supplier-loans.index')
            ->with('success', 'Supplier loan created successfully.');
    }

    public function edit(SupplierLoan $supplierLoan)
    {
        $suppliers = Supplier::all(['id', 'name']);

        return Inertia::render('SupplierLoans/Edit', [
            'loan' => $supplierLoan,
            'suppliers' => $suppliers
        ]);
    }

    public function update(Request $request, SupplierLoan $supplierLoan)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $supplierLoan->update($validated);

        return redirect()->route('supplier-loans.index')
            ->with('success', 'Supplier loan updated successfully.');
    }

    public function destroy(SupplierLoan $supplierLoan)
    {
        $supplierLoan->delete();

        return redirect()->route('supplier-loans.index')
            ->with('success', 'Supplier loan deleted successfully.');
    }
}
