<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CustomerReportController extends Controller
{
    public function show(Request $request)
    {
        // Get all customers for the dropdown
        $customers = Customer::select('id', 'name')->get();

        if (!$request->has('customer_id')) {
            return Inertia::render('CustomerReports/Show', [
                'customers' => $customers
            ]);
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        // Get payment details - with all-time totals
        $totalSales = \App\Models\ProductSale::where('customer_id', $customer->id)
            ->sum(DB::raw('CAST(total AS DECIMAL(10,2))'));

        $totalPayments = CustomerPayment::where('customer_id', $customer->id)
            ->sum(DB::raw('CAST(paid_amount AS DECIMAL(10,2))'));

        $remainingPaymentDue = $totalSales - $totalPayments;

        // Get last payment details
        $lastPayment = CustomerPayment::where('customer_id', $customer->id)
            ->latest('payment_date')
            ->first();

        // Get all sales and payments for the period
        $transactions = collect();

        // Add sales
        $sales = \App\Models\ProductSale::where('customer_id', $customer->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->map(function ($sale) {
                return [
                    'date' => $sale->date,
                    'sale_amount' => $sale->total,
                    'payment_amount' => null,
                    'quantity' => $sale->quantity,
                    'price' => $sale->price,
                    'type' => 'sale'
                ];
            });

        // Add payments
        $payments = CustomerPayment::where('customer_id', $customer->id)
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => $payment->payment_date,
                    'sale_amount' => null,
                    'payment_amount' => $payment->paid_amount,
                    'payment_method' => $payment->payment_method,
                    'quantity' => null,
                    'price' => null,
                    'type' => 'payment'
                ];
            });

        // Merge and sort all transactions
        $transactions = $sales->concat($payments)
            ->sortBy('date')
            ->values()
            ->all();

        return Inertia::render('CustomerReports/Show', [
            'customers' => $customers,
            'customer' => $customer,
            'fromDate' => $fromDate->format('Y-m-d'),
            'toDate' => $toDate->format('Y-m-d'),
            'summary' => [
                'remainingPaymentDue' => $remainingPaymentDue,
                'totalSales' => $totalSales,
                'totalPayments' => $totalPayments,
                'lastPayment' => $lastPayment ? [
                    'date' => $lastPayment->payment_date->format('Y-m-d'),
                    'amount' => $lastPayment->paid_amount,
                    'method' => $lastPayment->payment_method
                ] : null
            ],
            'transactions' => $transactions
        ]);
    }
}
