<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierLoan;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SupplierReportController extends Controller
{
    public function show(Request $request)
    {
        // Get all suppliers for the dropdown
        $suppliers = Supplier::select('id', 'name')->get();

        if (!$request->has('supplier_id')) {
            return Inertia::render('SupplierReports/Show', [
                'suppliers' => $suppliers
            ]);
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        // Get remaining loan due
        $totalLoansGiven = SupplierLoan::where('supplier_id', $supplier->id)
            ->sum('amount');
        $totalLoanRepayments = SupplierPayment::where('supplier_id', $supplier->id)
            ->sum('loan_deduction');
        $remainingLoanDue = $totalLoansGiven - $totalLoanRepayments;

        // Get payment details - with all-time totals
        $totalCollections = \App\Models\ProductCollection::where('supplier_id', $supplier->id)
            ->sum(DB::raw('CAST(total AS DECIMAL(10,2))'));
        \Log::info('Total Collections for supplier ' . $supplier->id, [
            'total_collections' => $totalCollections,
            'sql' => \App\Models\ProductCollection::where('supplier_id', $supplier->id)->toSql()
        ]);

        $totalPayments = SupplierPayment::where('supplier_id', $supplier->id)
            ->sum(DB::raw('CAST(amount_paid AS DECIMAL(10,2))'));
        \Log::info('Total Payments for supplier ' . $supplier->id, [
            'total_payments' => $totalPayments,
            'sql' => SupplierPayment::where('supplier_id', $supplier->id)->toSql()
        ]);

        $remainingPaymentDue = $totalCollections - $totalPayments;
        \Log::info('Payment calculations', [
            'total_collections' => $totalCollections,
            'total_payments' => $totalPayments,
            'remaining_due' => $remainingPaymentDue
        ]);

        // Get last payment details
        $lastPayment = SupplierPayment::where('supplier_id', $supplier->id)
            ->latest('payment_date')
            ->first();

        // Get all payments and loans for the period
        $transactions = collect();

        // Add loans
        $loans = SupplierLoan::where('supplier_id', $supplier->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->map(function ($loan) {
                return [
                    'date' => $loan->date,
                    'loan_amount' => $loan->amount,
                    'payment_amount' => null,
                    'loan_repayment' => null,
                    'notes' => $loan->notes,
                    'type' => 'loan'
                ];
            });

        // Add payments
        $payments = SupplierPayment::where('supplier_id', $supplier->id)
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => $payment->payment_date,
                    'loan_amount' => null,
                    'payment_amount' => $payment->amount_paid,
                    'loan_repayment' => $payment->loan_deduction,
                    'notes' => $payment->notes,
                    'type' => 'payment'
                ];
            });

        // Merge and sort all transactions
        $transactions = $loans->concat($payments)
            ->sortBy('date')
            ->values()
            ->all();

        return Inertia::render('SupplierReports/Show', [
            'suppliers' => $suppliers,
            'supplier' => $supplier,
            'fromDate' => $fromDate->format('Y-m-d'),
            'toDate' => $toDate->format('Y-m-d'),            'summary' => [
                'remainingLoanDue' => $remainingLoanDue,
                'remainingPaymentDue' => $remainingPaymentDue,
                'totalCollections' => $totalCollections,
                'totalPayments' => $totalPayments,
                'lastPayment' => $lastPayment ? [
                    'date' => $lastPayment->payment_date->format('Y-m-d'),
                    'amount' => $lastPayment->amount_paid
                ] : null
            ],
            'transactions' => $transactions
        ]);
    }
}
