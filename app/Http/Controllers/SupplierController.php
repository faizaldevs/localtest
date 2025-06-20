<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with('staff');
        
        // Filter by staff if provided
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Search by name if provided
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $suppliers = $query->paginate(10)->withQueryString();
        $staff = Staff::all();
        
        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'staff' => $staff,
            'filters' => $request->only(['staff_id', 'search'])
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
