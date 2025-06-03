<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSale;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CounterSaleController extends Controller
{    public function index()
    {
        $sales = ProductSale::with(['product', 'location'])
            ->where('sale_type', 'counter')
            ->latest()
            ->paginate(10);

        return Inertia::render('CounterSales/Index', [
            'sales' => $sales
        ]);
    }

    public function create()
    {
        return Inertia::render('CounterSales/Create', [
            'products' => Product::select('id', 'name', 'price')->get(),
            'locations' => Location::select('id', 'name')->get(),
        ]);
    }    public function store(Request $request)
    {
        \Log::info('Counter Sale Request:', $request->all());

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|numeric|min:0.001',
            'payment_mode' => 'required|in:cash,prepaid,postpaid',
        ]);

        $product = Product::findOrFail($request->product_id);
        $total = $product->price * $request->quantity;

        try {
            DB::beginTransaction();

            $saleData = [
                'product_id' => $request->product_id,
                'location_id' => $request->location_id,
                'date' => now(),
                'quantity' => $request->quantity,
                'price' => $product->price,
                'total' => $total,
                'payment_mode' => $request->payment_mode,
                'sale_type' => 'counter'
            ];
            
            \Log::info('Attempting to create sale with data:', $saleData);
            $sale = ProductSale::create($saleData);            \Log::info('Sale created successfully:', ['sale_id' => $sale->id]);
            DB::commit();

            // Check if this is a "Save & New" request
            if ($request->has('save_and_new')) {
                return back()->with([
                    'flash' => [
                        'message' => 'Sale completed successfully',
                        'type' => 'success'
                    ]
                ]);
            }

            return redirect()->route('counter-sales.index')
                ->with('message', 'Sale completed successfully')
                ->with('type', 'success');} catch (\Exception $e) {
            DB::rollback();
            \Log::error('Counter Sale Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Error processing sale: ' . $e->getMessage()]);
        } catch (\Throwable $t) {
            DB::rollback();
            \Log::error('Counter Sale Error (Throwable):', [
                'error' => $t->getMessage(),
                'trace' => $t->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Error processing sale: ' . $t->getMessage()]);
        }
    }

    public function show(ProductSale $sale)
    {
        $sale->load(['product', 'location']);
        
        return Inertia::render('CounterSales/Show', [
            'sale' => $sale
        ]);
    }
}
