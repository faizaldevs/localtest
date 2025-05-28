<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSale;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductSaleController extends Controller
{
    public function index()
    {
        return Inertia::render('ProductSales/Index', [
            'sales' => ProductSale::with(['customer', 'staff', 'product'])
                ->latest()
                ->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductSales/Create', [
            'products' => Product::select('id', 'name', 'price')->get(),
            'staff' => Staff::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'staff_id' => 'required|exists:staff,id',
            'customer_details' => 'required|array|min:1',
            'customer_details.*.customer_id' => 'required|exists:customers,id',
            'customer_details.*.quantity' => 'required|numeric|min:0.001',
            'customer_details.*.price' => 'required|numeric|min:0',
            'customer_details.*.total' => 'required|numeric|min:0',
            'customer_details.*.payment_mode' => 'required|in:prepaid,cash,postpaid'
        ]);

        foreach ($request->customer_details as $detail) {
            ProductSale::create([
                'date' => $request->date,
                'customer_id' => $detail['customer_id'],
                'staff_id' => $request->staff_id,
                'product_id' => $request->product_id,
                'quantity' => $detail['quantity'],
                'price' => $detail['price'],
                'total' => $detail['total'],
                'payment_mode' => $detail['payment_mode'],
                'sale_type' => 'delivery' // Default to delivery since it's staff-customer based
            ]);
        }        return redirect()->route('product-sales.index')
            ->with('message', 'Product sales were successfully created')
            ->with('type', 'success');
    }

    public function checkExisting(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        $existingSales = ProductSale::where('date', $request->date)
            ->where('product_id', $request->product_id)
            ->where('staff_id', $request->staff_id)
            ->with('customer')
            ->get();

        if ($existingSales->isEmpty()) {
            return response()->json(['exists' => false]);
        }

        $salesData = [
            'exists' => true,
            'data' => [
                'customers' => $existingSales->map(function ($sale) {
                    return [
                        'customer_id' => $sale->customer_id,
                        'price' => $sale->price,
                        'quantity' => $sale->quantity,
                        'payment_mode' => $sale->payment_mode,
                    ];
                })
            ]
        ];

        return response()->json($salesData);
    }
}
