<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SupplierPaymentController extends Controller
{
    public function create()
    {
        return Inertia::render('SupplierPayments/Create', [
            'staff' => Staff::with('suppliers')->get()
        ]);
    }

    public function getSuppliers(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        // Convert dates to Carbon instances with specific time boundaries
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        $suppliers = Supplier::where('staff_id', $request->staff_id)
            ->with(['productCollections' => function ($query) use ($fromDate, $toDate) {
                $query->whereDate('date', '>=', $fromDate->format('Y-m-d'))
                      ->whereDate('date', '<=', $toDate->format('Y-m-d'));
            }])
            ->get()
            ->map(function ($supplier) use ($fromDate, $toDate) {
                $collections = $supplier->productCollections;
                
                // Calculate total previous payments
                $totalPreviousPayments = SupplierPayment::where('supplier_id', $supplier->id)
                    ->whereDate('period_from', '>=', $fromDate->format('Y-m-d'))
                    ->whereDate('period_to', '<=', $toDate->format('Y-m-d'))
                    ->sum('amount_paid');
                
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'daily_quantities' => $collections->pluck('quantity', 'date'),
                    'total_quantity' => $collections->sum('quantity'),
                    'total_amount' => $collections->sum('total'),
                    'total_previous_payments' => $totalPreviousPayments
                ];
            });

        return response()->json($suppliers);
    }

    public function getExistingPayments(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();        $payments = SupplierPayment::whereHas('supplier', function($query) use ($request) {
                $query->where('staff_id', $request->staff_id);
            })
            ->with('staffDiscrepancy') // Include staff discrepancy relationship
            ->whereBetween('period_from', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
            ->get();

        return response()->json($payments);
    }    public function store(Request $request)
    {
        \Log::info('Supplier Payment Request:', $request->all());

        $request->validate([
            'payment_date' => 'required|date',
            'date_range' => 'required|array|size:2',
            'date_range.*' => 'required|date',
            'suppliers' => 'required|array',
            'suppliers.*.id' => 'required|exists:suppliers,id',
            'suppliers.*.staff_id' => 'required|exists:staff,id',
            'suppliers.*.payment_amount' => 'required|numeric|min:0',
            'suppliers.*.loan_deduction' => 'required|numeric|min:0',
            'suppliers.*.staff_deduction' => 'required|numeric|min:0',
            'suppliers.*.notes' => 'nullable|string',
            'suppliers.*.staff_notes' => 'nullable|string',
            'suppliers.*.payment_id' => 'present|nullable'
        ]);

        foreach ($request->suppliers as $supplierData) {
            if ($supplierData['payment_amount'] > 0 || $supplierData['loan_deduction'] > 0) {
                // Convert date range to Carbon instances with time boundaries
                $periodFrom = Carbon::parse($request->date_range[0])->startOfDay();
                $periodTo = Carbon::parse($request->date_range[1])->endOfDay();

                $collections = \App\Models\ProductCollection::where('supplier_id', $supplierData['id'])
                    ->whereDate('date', '>=', $periodFrom->format('Y-m-d'))
                    ->whereDate('date', '<=', $periodTo->format('Y-m-d'))
                    ->get();

                // Calculate weighted average cost
                $totalQuantity = $collections->sum('quantity');
                $totalAmount = $collections->sum('total');
                $weightedAverageCost = $totalQuantity > 0 ? $totalAmount / $totalQuantity : 0;

                $paymentData = [
                    'supplier_id' => $supplierData['id'],
                    'period_from' => $periodFrom,
                    'period_to' => $periodTo,
                    'total_quantity' => $totalQuantity,
                    'average_cost' => $weightedAverageCost,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $supplierData['payment_amount'],
                    'payment_date' => $request->payment_date,
                    'notes' => $supplierData['notes'],
                    'loan_deduction' => $supplierData['loan_deduction'],
                    'amount_paid' => $supplierData['payment_amount'] - $supplierData['loan_deduction']
                ];                // Always create new payment records
                $payment = SupplierPayment::create($paymentData);

                // Create staff discrepancy record if there's a staff deduction
                if ($supplierData['staff_deduction'] > 0) {
                    \App\Models\StaffDiscrepancy::create([
                        'staff_id' => $supplierData['staff_id'],
                        'supplier_payment_id' => $payment->id,
                        'discrepancy_amount' => $supplierData['staff_deduction'],
                        'notes' => $supplierData['staff_notes'],
                        'status' => 'pending'
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Payments saved successfully');
    }
}
