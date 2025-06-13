<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\ProductSale;
use App\Models\Supplier;
use App\Models\ProductTransfer;
use App\Models\StaffLoan;
use App\Models\StaffPayment;
use App\Models\StaffDiscrepancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{    public function supplierProduct()
    {
        $suppliers = Supplier::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        return Inertia::render('Reports/SupplierProduct', [
            'suppliers' => $suppliers,
            'products' => $products
        ]);
    }

    public function generateSupplierProductReport(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);
        $product = Product::findOrFail($request->product_id);
          $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();
        $fromDateStr = $fromDate->format('Y-m-d');
        $toDateStr = $toDate->format('Y-m-d');

        // Get collections for the supplier
        $collections = DB::table('product_collections')
            ->where('supplier_id', $supplier->id)
            ->where('product_id', $request->product_id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->select('date')
            ->selectRaw('SUM(CAST(quantity AS DECIMAL(10,3))) as quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $reportData = [];
        $totalQuantity = 0;

        // Prepare report data
        foreach ($collections as $collection) {
            $qty = (float)$collection->quantity;
            $reportData[] = [
                'date' => Carbon::parse($collection->date)->format('M d, Y'),
                'quantity' => number_format($qty, 3, '.', ''),
            ];
            $totalQuantity += $qty;
        }        return response()->json([
            'success' => true,
            'data' => [
                'supplier' => [
                    'name' => $supplier->name,
                    'address' => $supplier->address,
                    'phone' => $supplier->phone
                ],
                'product' => $product,
                'from_date' => $fromDate->format('M d, Y'),
                'to_date' => $toDate->format('M d, Y'),
                'report_data' => $reportData,
                'total_quantity' => number_format($totalQuantity, 3, '.', '')
            ]
        ]);
    }
    public function staffProduct()
    {
        $staff = Staff::select('id', 'name')->get();
        $products = Product::select('id', 'name')->get();

        return Inertia::render('Reports/StaffProduct', [
            'staff' => $staff,
            'products' => $products
        ]);
    }

    public function generateStaffProductReport(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'product_id' => 'required|exists:products,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        $staff = Staff::findOrFail($request->staff_id);
        $product = Product::findOrFail($request->product_id);
        
        // Format dates for database queries
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();
        $fromDateStr = $fromDate->format('Y-m-d');
        $toDateStr = $toDate->format('Y-m-d');

        // Get collected quantities - using raw query to ensure proper decimal handling
        $collected = DB::table('product_collections')
            ->where('staff_id', $request->staff_id)
            ->where('product_id', $request->product_id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->select('date')
            ->selectRaw('SUM(CAST(quantity AS DECIMAL(10,3))) as quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get sold quantities
        $sold = DB::table('product_sales')
            ->where('staff_id', $request->staff_id)
            ->where('product_id', $request->product_id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->select('date')
            ->selectRaw('SUM(CAST(quantity AS DECIMAL(10,3))) as quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get transferred quantities
        $transferred = DB::table('product_transfers')
            ->where('from_staff_id', $request->staff_id)
            ->where('product_id', $request->product_id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->select('date')
            ->selectRaw('SUM(CAST(quantity AS DECIMAL(10,3))) as quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get received quantities
        $received = DB::table('product_transfers')
            ->where('to_staff_id', $request->staff_id)
            ->where('product_id', $request->product_id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->select('date')
            ->selectRaw('SUM(CAST(quantity AS DECIMAL(10,3))) as quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Create a date range array
        $dateRange = [];
        $currentDate = $fromDate->copy();
        while ($currentDate <= $toDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Prepare report data
        $reportData = [];
        $totals = [
            'collected' => 0,
            'sold' => 0,
            'transferred' => 0,
            'received' => 0
        ];

        foreach ($dateRange as $date) {
            $collectedRecord = $collected->firstWhere('date', $date);
            $soldRecord = $sold->firstWhere('date', $date);
            $transferredRecord = $transferred->firstWhere('date', $date);
            $receivedRecord = $received->firstWhere('date', $date);

            // Convert string quantities to float with proper decimal handling
            $collectedQty = $collectedRecord ? (float)$collectedRecord->quantity : 0.000;
            $soldQty = $soldRecord ? (float)$soldRecord->quantity : 0.000;
            $transferredQty = $transferredRecord ? (float)$transferredRecord->quantity : 0.000;
            $receivedQty = $receivedRecord ? (float)$receivedRecord->quantity : 0.000;

            // Only add rows where there is at least one non-zero quantity
            if ($collectedQty > 0 || $soldQty > 0 || $transferredQty > 0 || $receivedQty > 0) {
                $reportData[] = [
                    'date' => $date,
                    'formatted_date' => Carbon::parse($date)->format('M d, Y'),
                    'collected' => number_format($collectedQty, 3, '.', ''),
                    'sold' => number_format($soldQty, 3, '.', ''),
                    'transferred' => number_format($transferredQty, 3, '.', ''),
                    'received' => number_format($receivedQty, 3, '.', '')
                ];
            }

            $totals['collected'] += $collectedQty;
            $totals['sold'] += $soldQty;
            $totals['transferred'] += $transferredQty;
            $totals['received'] += $receivedQty;
        }

        // Format totals with consistent decimal places
        $totals = array_map(function($value) {
            return number_format($value, 3, '.', '');
        }, $totals);

        // Get sales summary by payment mode
        $salesSummary = DB::table('product_sales')
            ->where('staff_id', $request->staff_id)
            ->where('product_id', $request->product_id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->select('payment_mode')
            ->selectRaw('SUM(CAST(quantity AS DECIMAL(10,3))) as total_quantity')
            ->selectRaw('SUM(CAST(total AS DECIMAL(10,2))) as total_amount')
            ->groupBy('payment_mode')
            ->orderBy('payment_mode')
            ->get()
            ->map(function($item) {
                return [
                    'payment_mode' => $item->payment_mode,
                    'quantity' => number_format((float)$item->total_quantity, 3, '.', ''),
                    'amount' => number_format((float)$item->total_amount, 2, '.', '')
                ];
            });

        // Calculate summary totals
        $summaryTotals = [
            'quantity' => number_format($salesSummary->sum(fn($item) => (float)$item['quantity']), 3, '.', ''),
            'amount' => number_format($salesSummary->sum(fn($item) => (float)$item['amount']), 2, '.', '')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'staff' => $staff,
                'product' => $product,
                'from_date' => $fromDate->format('M d, Y'),
                'to_date' => $toDate->format('M d, Y'),
                'report_data' => $reportData,
                'totals' => $totals,
                'sales_summary' => [
                    'data' => $salesSummary,
                    'totals' => $summaryTotals
                ]
            ]
        ]);
    }

    public function staffPayment()
    {
        return Inertia::render('Reports/StaffPayment', [
            'staff' => Staff::select('id', 'name')->get()
        ]);
    }

    public function generateStaffPaymentReport(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();
        $staff = Staff::findOrFail($request->staff_id);

        // Get loan dues
        $loanDues = StaffLoan::where('staff_id', $staff->id)
            ->sum('amount');
        $loanRepayments = StaffPayment::where('staff_id', $staff->id)
            ->sum('loan_deduction');
        $totalLoanDues = $loanDues - $loanRepayments;

        // Get undeducted discrepancies
        $undeductedDiscrepancies = StaffDiscrepancy::where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->sum('discrepancy_amount');

        // Get payment records for the period
        $payments = StaffPayment::where('staff_id', $staff->id)
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->orderBy('payment_date')
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => $payment->payment_date->format('M d, Y'),
                    'loan_amount' => $payment->loan_deduction,
                    'loan_deduction' => $payment->loan_deduction,
                    'undeducted_discrepancy' => 0, // This is shown in section 1
                    'deducted_discrepancy' => $payment->discrepancy_deduction,
                    'net_amount_paid' => $payment->net_amount_paid,
                    'base_amount' => $payment->base_amount,
                    'bonus_amount' => $payment->bonus_amount,
                    'note' => $payment->notes
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'staff' => $staff,
                'from_date' => $fromDate->format('M d, Y'),
                'to_date' => $toDate->format('M d, Y'),
                'section_one' => [
                    'loan_dues' => $totalLoanDues,
                    'undeducted_discrepancies' => $undeductedDiscrepancies
                ],
                'section_two' => [
                    'payments' => $payments
                ]
            ]
        ]);
    }
}
