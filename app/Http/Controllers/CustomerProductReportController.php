<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductSale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerProductReportController extends Controller
{
    public function show()
    {
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Reports/CustomerProduct', [
            'customers' => $customers,
            'products' => $products
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $product = Product::findOrFail($request->product_id);
        
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();
        $fromDateStr = $fromDate->format('Y-m-d');
        $toDateStr = $toDate->format('Y-m-d');
        
        \Log::info('Report Parameters:', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'date_range' => [$fromDateStr, $toDateStr]
        ]);

        try {
            // First check if we have any sales at all for this customer and product
            $allSales = ProductSale::where('customer_id', $customer->id)
                ->where('product_id', $product->id)
                ->get();

            \Log::info('All sales for customer/product:', [
                'count' => $allSales->count(),
                'sales' => $allSales->toArray()
            ]);

            // Now get sales within date range
            $sales = ProductSale::where('customer_id', $customer->id)
                ->where('product_id', $product->id)
                ->whereDate('date', '>=', $fromDateStr)
                ->whereDate('date', '<=', $toDateStr)
                ->get();

            \Log::info('Sales within date range:', [
                'count' => $sales->count(),
                'sales' => $sales->toArray()
            ]);

            $reportData = [];
            $totals = [
                'counter_qty' => 0,
                'delivery_qty' => 0,
                'total_amount' => 0
            ];

            // Group sales by date
            $salesByDate = $sales->groupBy('date');
            foreach ($salesByDate as $date => $dateSales) {
                $counterSales = $dateSales->where('sale_type', 'counter');
                $deliverySales = $dateSales->where('sale_type', 'delivery');

                $counterQty = $counterSales->sum('quantity');
                $deliveryQty = $deliverySales->sum('quantity');
                $dayAmount = $dateSales->sum('total');

                \Log::info("Calculated quantities for date $date:", [
                    'counter_qty' => $counterQty,
                    'delivery_qty' => $deliveryQty,
                    'day_amount' => $dayAmount,
                    'counter_sales' => $counterSales->toArray(),
                    'delivery_sales' => $deliverySales->toArray()
                ]);

                if ($counterQty > 0 || $deliveryQty > 0) {
                    $reportData[] = [
                        'date' => Carbon::parse($date)->format('M d, Y'),
                        'counter_qty' => number_format($counterQty, 3, '.', ''),
                        'delivery_qty' => number_format($deliveryQty, 3, '.', ''),
                        'total_qty' => number_format($counterQty + $deliveryQty, 3, '.', ''),
                        'amount' => number_format($dayAmount, 2, '.', '')
                    ];

                    $totals['counter_qty'] += $counterQty;
                    $totals['delivery_qty'] += $deliveryQty;
                    $totals['total_amount'] += $dayAmount;
                }
            }

            \Log::info('Final calculations:', [
                'totals' => $totals,
                'report_data' => $reportData
            ]);

            // Format totals
            $formattedTotals = [
                'counter_qty' => number_format($totals['counter_qty'], 3, '.', ''),
                'delivery_qty' => number_format($totals['delivery_qty'], 3, '.', ''),
                'total_qty' => number_format($totals['counter_qty'] + $totals['delivery_qty'], 3, '.', ''),
                'total_amount' => number_format($totals['total_amount'], 2, '.', '')
            ];

            // Get sales summary by payment mode
            $salesSummary = $sales->groupBy('payment_mode')
                ->map(function ($items) {
                    return [
                        'payment_mode' => ucfirst($items->first()->payment_mode),
                        'quantity' => number_format($items->sum('quantity'), 3, '.', ''),
                        'amount' => number_format($items->sum('total'), 2, '.', '')
                    ];
                })->values();

            $summaryTotals = [
                'quantity' => number_format($sales->sum('quantity'), 3, '.', ''),
                'amount' => number_format($sales->sum('total'), 2, '.', '')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => [
                        'name' => $customer->name,
                        'address' => $customer->address,
                        'phone' => $customer->phone
                    ],
                    'product' => $product,
                    'from_date' => $fromDate->format('M d, Y'),
                    'to_date' => $toDate->format('M d, Y'),
                    'report_data' => $reportData,
                    'totals' => $formattedTotals,
                    'sales_summary' => [
                        'data' => $salesSummary,
                        'totals' => $summaryTotals
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error generating report:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }
}
