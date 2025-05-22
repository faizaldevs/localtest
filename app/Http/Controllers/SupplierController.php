<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('staff')->paginate(10);
        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers
        ]);
    }

    public function create()
    {
        $staff = Staff::all();
        return Inertia::render('Suppliers/Create', [
            'staff' => $staff
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:255',
            'staff_id' => 'required|exists:staff,id'
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('message', 'Supplier created successfully');
    }

    public function edit(Supplier $supplier)
    {
        $staff = Staff::all();
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
            'staff' => $staff
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:255',
            'staff_id' => 'required|exists:staff,id'
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('message', 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        
        return redirect()->route('suppliers.index')
            ->with('message', 'Supplier deleted successfully');
    }
}
