<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\ProductCollection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductCollectionController extends Controller
{
    public function index()
    {
        return Inertia::render('ProductCollections/Index', [
            'collections' => ProductCollection::with(['product', 'supplier', 'staff'])
                ->latest()
                ->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductCollections/Create', [
            'products' => Product::select('id', 'name', 'price', 'cost')->get(),
            'staff' => Staff::select('id', 'name')->get(),
        ]);
    }

    public function getSuppliersByStaff($staffId)
    {
        try {
            $suppliers = Supplier::where('staff_id', $staffId)
                ->select('id', 'name', 'staff_id')
                ->get();
            
            return response()->json($suppliers);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load suppliers'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'supplier_details' => 'required|array|min:1',
            'supplier_details.*.supplier_id' => 'required|exists:suppliers,id',
            'supplier_details.*.quantity' => 'required|numeric|min:0',
            'supplier_details.*.cost' => 'required|numeric|min:0',
        ]);

        foreach ($request->supplier_details as $detail) {
            ProductCollection::create([
                'product_id' => $request->product_id,
                'staff_id' => $request->staff_id,
                'date' => $request->date,
                'supplier_id' => $detail['supplier_id'],
                'quantity' => $detail['quantity'],
                'cost' => $detail['cost'],
                'total' => $detail['quantity'] * $detail['cost']
            ]);
        }

        return redirect()->route('product-collections.create')
            ->with('message', 'Product Collections created successfully');
    }

    public function checkExisting(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        $existingCollections = ProductCollection::with(['supplier'])
            ->where('date', $request->date)
            ->where('product_id', $request->product_id)
            ->where('staff_id', $request->staff_id)
            ->get();

        if ($existingCollections->isEmpty()) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            'data' => [
                'suppliers' => $existingCollections->map(function ($collection) {
                    return [
                        'supplier_id' => $collection->supplier_id,
                        'cost' => $collection->cost,
                        'quantity' => $collection->quantity
                    ];
                })
            ]
        ]);
    }
}
