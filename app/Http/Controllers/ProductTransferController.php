<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductTransfer;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductTransferController extends Controller
{
    public function index()
    {
        return Inertia::render('ProductTransfers/Index', [
            'transfers' => ProductTransfer::with(['fromStaff', 'toStaff', 'location', 'product'])
                ->latest()
                ->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductTransfers/Create', [
            'staff' => Staff::all(),
            'locations' => Location::all(),
            'products' => Product::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_staff_id' => 'nullable|exists:staff,id',
            'to_staff_id' => 'nullable|exists:staff,id|different:from_staff_id',
            'location_id' => 'nullable|exists:locations,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string',
        ]);

        // Additional validation: either to_staff_id or location_id must be present
        if (empty($validated['to_staff_id']) && empty($validated['location_id'])) {
            return back()->withErrors([
                'transfer_target' => 'You must select either a staff member or a location as the transfer destination.'
            ]);
        }

        ProductTransfer::create($validated);

        return redirect()->route('product-transfers.index')
            ->with('message', 'Product transfer created successfully.');
    }

    public function show(ProductTransfer $productTransfer)
    {
        return Inertia::render('ProductTransfers/Show', [
            'transfer' => $productTransfer->load(['fromStaff', 'toStaff', 'location', 'product'])
        ]);
    }
}
