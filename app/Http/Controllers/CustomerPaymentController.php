<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Customer;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CustomerPaymentController extends Controller
{
    public function create()
    {
        return Inertia::render('CustomerPayments/Create', [
            'staff' => Staff::with('customers')->get()
        ]);
    }

    public function createPrepaid()
    {
        return Inertia::render('CustomerPrepaidPayments/Create', [
            'staff' => Staff::all()
        ]);
    }

    public function getCustomers(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        // Convert dates to Carbon instances with specific time boundaries
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        $customers = Customer::where('staff_id', $request->staff_id)
            ->with(['productSales' => function ($query) use ($fromDate, $toDate) {
                $query->whereDate('date', '>=', $fromDate->format('Y-m-d'))
                      ->whereDate('date', '<=', $toDate->format('Y-m-d'));
            }])
            ->get()            ->map(function ($customer) use ($fromDate, $toDate) {
                $sales = $customer->productSales;
                
                // Calculate payments for this period
                $periodPayments = CustomerPayment::where('customer_id', $customer->id)
                    ->whereDate('period_from', '>=', $fromDate->format('Y-m-d'))
                    ->whereDate('period_to', '<=', $toDate->format('Y-m-d'))
                    ->sum('paid_amount');
                
                // Calculate lifetime totals
                $lifetimeQuantity = \App\Models\ProductSale::where('customer_id', $customer->id)
                    ->sum('quantity');
                $lifetimeAmount = \App\Models\ProductSale::where('customer_id', $customer->id)
                    ->sum('total');
                $lifetimePayments = CustomerPayment::where('customer_id', $customer->id)
                    ->sum('paid_amount');
                
                // Calculate net due (lifetime amount minus lifetime payments)
                $netDue = $lifetimeAmount - $lifetimePayments;
                
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'daily_quantities' => $sales->pluck('quantity', 'date'),
                    'period_quantity' => $sales->sum('quantity'),
                    'period_amount' => $sales->sum('total'),
                    'period_payments' => $periodPayments,
                    'lifetime_quantity' => $lifetimeQuantity,
                    'lifetime_amount' => $lifetimeAmount,
                    'lifetime_payments' => $lifetimePayments,
                    'net_due' => $netDue,
                    // Keep for backward compatibility
                    'total_quantity' => $sales->sum('quantity'),
                    'total_amount' => $sales->sum('total'),
                    'total_previous_payments' => $periodPayments
                ];
            });

        return response()->json($customers);
    }

    public function getExistingPayments(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        $payments = CustomerPayment::whereHas('customer', function($query) use ($request) {
                $query->where('staff_id', $request->staff_id);
            })
            ->whereBetween('period_from', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
            ->get();

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        \Log::info('Customer Payment Request:', $request->all());

        $request->validate([
            'payment_date' => 'required|date',
            'date_range' => 'required|array|size:2',
            'date_range.*' => 'required|date',
            'customers' => 'required|array',
            'customers.*.id' => 'required|exists:customers,id',
            'customers.*.staff_id' => 'required|exists:staff,id',
            'customers.*.payment_amount' => 'required|numeric|min:0',
            'customers.*.payment_method' => 'required|in:prepaid,postpaid,cash',
            'customers.*.notes' => 'nullable|string',
            'customers.*.payment_id' => 'present|nullable'
        ]);

        foreach ($request->customers as $customerData) {
            if ($customerData['payment_amount'] > 0) {
                // Convert date range to Carbon instances with time boundaries
                $periodFrom = Carbon::parse($request->date_range[0])->startOfDay();
                $periodTo = Carbon::parse($request->date_range[1])->endOfDay();

                $sales = \App\Models\ProductSale::where('customer_id', $customerData['id'])
                    ->whereDate('date', '>=', $periodFrom->format('Y-m-d'))
                    ->whereDate('date', '<=', $periodTo->format('Y-m-d'))
                    ->get();

                // Calculate weighted average price
                $totalQuantity = $sales->sum('quantity');
                $totalAmount = $sales->sum('total');
                $weightedAveragePrice = $totalQuantity > 0 ? $totalAmount / $totalQuantity : 0;

                $paymentData = [
                    'customer_id' => $customerData['id'],
                    'period_from' => $periodFrom,
                    'period_to' => $periodTo,
                    'total_quantity' => $totalQuantity,
                    'average_price' => $weightedAveragePrice,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $customerData['payment_amount'],
                    'payment_date' => $request->payment_date,
                    'payment_method' => $customerData['payment_method'],
                    'notes' => $customerData['notes'],
                ];

                CustomerPayment::create($paymentData);
            }
        }

        return redirect()->back()->with('success', 'Payments saved successfully');
    }    public function storePrepaid(Request $request)
    {
        \Log::info('Customer Prepaid Payment Request:', $request->all());

        $request->validate([
            'payment_date' => 'required|date',
            'customers' => 'required|array',
            'customers.*.id' => 'required|exists:customers,id',
            'customers.*.staff_id' => 'required|exists:staff,id',
            'customers.*.payment_amount' => 'required|numeric|min:0',
            'customers.*.payment_method' => 'required|in:prepaid'
        ]);

        foreach ($request->customers as $customerData) {
            if ($customerData['payment_amount'] > 0) {
                // Try to find existing payment record for this customer on this date
                $existingPayment = CustomerPayment::where('customer_id', $customerData['id'])
                    ->where('payment_method', 'prepaid')
                    ->whereDate('payment_date', $request->payment_date)
                    ->first();

                $paymentData = [
                    'customer_id' => $customerData['id'],
                    'period_from' => Carbon::parse($request->payment_date)->startOfDay(),
                    'period_to' => Carbon::parse($request->payment_date)->endOfDay(),
                    'total_quantity' => 0, // Will be updated when used
                    'average_price' => 0, // Will be updated when used
                    'total_amount' => 0, // Will be updated when used
                    'paid_amount' => $customerData['payment_amount'],
                    'payment_date' => $request->payment_date,
                    'payment_method' => $customerData['payment_method'],
                    'notes' => '',
                    'staff_id' => $customerData['staff_id']
                ];

                if ($existingPayment) {
                    // Update existing payment
                    $existingPayment->update($paymentData);
                } else {
                    // Create new payment
                    CustomerPayment::create($paymentData);
                }
            }
        }

        return redirect()->back()->with('success', 'Prepaid payment recorded successfully');
    }    public function getCustomersWithPrepaid($staffId, Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = Carbon::parse($request->date)->format('Y-m-d');

        $customers = Customer::where('staff_id', $staffId)
            ->get()
            ->map(function ($customer) use ($date) {
                // Get the prepaid amount for this customer on the selected date
                $prepaidAmount = CustomerPayment::where('customer_id', $customer->id)
                    ->where('payment_method', 'prepaid')
                    ->whereDate('payment_date', $date)
                    ->sum('paid_amount');

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'prepaid_amount' => $prepaidAmount
                ];
            });

        return response()->json($customers);
    }
}
