<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        $suppliers = Supplier::where('staff_id', $request->staff_id)
            ->with(['productCollections' => function ($query) use ($request) {
                $query->whereBetween('date', [$request->from_date, $request->to_date]);
            }])
            ->get()
            ->map(function ($supplier) {
                $collections = $supplier->productCollections;
                
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'daily_quantities' => $collections->pluck('quantity', 'date'),
                    'total_quantity' => $collections->sum('quantity'),
                    'total_amount' => $collections->sum('total')
                ];
            });

        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'date_range' => 'required|array|size:2',
            'date_range.*' => 'required|date',
            'suppliers' => 'required|array',
            'suppliers.*.id' => 'required|exists:suppliers,id',
            'suppliers.*.payment_amount' => 'required|numeric|min:0',
            'suppliers.*.loan_deduction' => 'required|numeric|min:0',
            'suppliers.*.notes' => 'nullable|string'
        ]);

        foreach ($request->suppliers as $supplierData) {
            if ($supplierData['payment_amount'] > 0 || $supplierData['loan_deduction'] > 0) {
                $collections = \App\Models\ProductCollection::where('supplier_id', $supplierData['id'])
                    ->whereBetween('date', $request->date_range)
                    ->get();

                SupplierPayment::create([
                    'supplier_id' => $supplierData['id'],
                    'period_from' => $request->date_range[0],
                    'period_to' => $request->date_range[1],
                    'total_quantity' => $collections->sum('quantity'),
                    'average_cost' => $collections->avg('average_cost'),
                    'total_amount' => $collections->sum(function ($collection) {
                        return $collection->quantity * $collection->average_cost;
                    }),
                    'paid_amount' => $supplierData['payment_amount'],
                    'payment_date' => $request->payment_date,
                    'notes' => $supplierData['notes'],
                    'loan_deduction' => $supplierData['loan_deduction'],
                    'amount_paid' => $supplierData['payment_amount'] - $supplierData['loan_deduction']
                ]);
            }
        }

        return redirect()->back()->with('success', 'Payments saved successfully');
    }
}
