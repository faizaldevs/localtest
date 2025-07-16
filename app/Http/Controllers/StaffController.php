<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Product; // Added this import for Product model

class StaffController extends Controller
{
    public function index()
    {
        return Inertia::render('Staff/Index', [
            'staff' => Staff::with('location')
                ->latest()
                ->paginate(10)
                ->through(fn ($staff) => [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'phone' => $staff->phone,
                    'address' => $staff->address,
                    'salary' => $staff->salary,
                    'location' => $staff->location ? [
                        'id' => $staff->location->id,
                        'name' => $staff->location->name,
                    ] : null,
                ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/Create', [
            'locations' => Location::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'salary' => 'nullable|numeric|min:0'
        ]);

        Staff::create($validated);

        return redirect()->route('staff.index')
            ->with('message', 'Staff member created successfully.');
    }

    public function show(Staff $staff)
    {
        // Calculate product quantities in staff's hand
        $productQuantities = $this->calculateProductQuantities($staff);
        
        // Calculate cash from sales
        $cashFromSales = $this->calculateCashFromSales($staff);
        
        // Calculate pending amounts
        $pendingAmounts = $this->calculatePendingAmounts($staff);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($staff);

        return Inertia::render('Staff/Show', [
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'phone' => $staff->phone,
                'address' => $staff->address,
                'salary' => $staff->salary,
                'created_at' => $staff->created_at->format('F j, Y'),
                'location' => $staff->location ? [
                    'id' => $staff->location->id,
                    'name' => $staff->location->name,
                ] : null,
            ],
            'productQuantities' => $productQuantities,
            'cashFromSales' => $cashFromSales,
            'pendingAmounts' => $pendingAmounts,
            'recentActivities' => $recentActivities,
        ]);
    }

    public function edit(Staff $staff)
    {
        return Inertia::render('Staff/Edit', [
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'phone' => $staff->phone,
                'address' => $staff->address,
                'salary' => $staff->salary,
                'location_id' => $staff->location_id,
            ],
            'locations' => Location::select('id', 'name')->get()
        ]);
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'salary' => 'nullable|numeric|min:0'
        ]);

        $staff->update($validated);

        return redirect()->route('staff.index')
            ->with('message', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('message', 'Staff member deleted successfully.');
    }

    public function suppliers($staffId)
    {
        try {
            $staff = Staff::findOrFail($staffId);
            $suppliers = $staff->suppliers()
                ->select('id', 'name', 'staff_id')
                ->get();
            
            return response()->json($suppliers);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load suppliers'], 500);
        }
    }

    private function calculateProductQuantities(Staff $staff)
    {
        // Get all products collected by staff (from suppliers)
        $productsCollected = $staff->productCollections()
            ->with('product')
            ->select('product_id', \DB::raw('SUM(CAST(quantity AS DECIMAL(10,3))) as total_collected'))
            ->groupBy('product_id')
            ->get();

        // Get all products received via transfers
        $productsReceived = $staff->productsReceived()
            ->with('product')
            ->select('product_id', \DB::raw('SUM(CAST(quantity AS DECIMAL(10,3))) as total_received'))
            ->groupBy('product_id')
            ->get();

        // Get all products sold by staff
        $productsSold = $staff->productSales()
            ->with('product')
            ->select('product_id', \DB::raw('SUM(CAST(quantity AS DECIMAL(10,3))) as total_sold'))
            ->groupBy('product_id')
            ->get();

        // Get all products sent to others by staff
        $productsSent = $staff->productsSent()
            ->with('product')
            ->select('product_id', \DB::raw('SUM(CAST(quantity AS DECIMAL(10,3))) as total_sent'))
            ->groupBy('product_id')
            ->get();

        // Get all unique product IDs
        $allProductIds = collect()
            ->concat($productsCollected->pluck('product_id'))
            ->concat($productsReceived->pluck('product_id'))
            ->concat($productsSold->pluck('product_id'))
            ->concat($productsSent->pluck('product_id'))
            ->unique();

        // Initialize products array with all products
        $allProducts = [];
        foreach ($allProductIds as $productId) {
            $product = Product::find($productId);
            $allProducts[$productId] = [
                'product_id' => $productId,
                'product_name' => $product ? $product->name : 'Unknown Product',
                'total_collected' => 0,
                'total_received' => 0,
                'total_sold' => 0,
                'total_sent' => 0
            ];
        }

        // Update quantities for each transaction type
        foreach ($productsCollected as $collected) {
            $allProducts[$collected->product_id]['total_collected'] = (float)$collected->total_collected;
        }

        foreach ($productsReceived as $received) {
            $allProducts[$received->product_id]['total_received'] = (float)$received->total_received;
        }

        foreach ($productsSold as $sold) {
            $allProducts[$sold->product_id]['total_sold'] = (float)$sold->total_sold;
        }

        foreach ($productsSent as $sent) {
            $allProducts[$sent->product_id]['total_sent'] = (float)$sent->total_sent;
        }

        // Calculate current quantities and prepare data
        $currentQuantities = [];
        $totalQuantity = 0;
        $totalCollected = 0;
        $totalReceived = 0;
        $totalSold = 0;
        $totalSent = 0;

        foreach ($allProducts as $productData) {
            $currentQuantity = $productData['total_collected'] + $productData['total_received'] 
                             - $productData['total_sold'] - $productData['total_sent'];
            
            // Add to totals regardless of current quantity
            $totalCollected += $productData['total_collected'];
            $totalReceived += $productData['total_received'];
            $totalSold += $productData['total_sold'];
            $totalSent += $productData['total_sent'];
            
            // Only include in current quantities if there is stock
            if ($currentQuantity > 0) {
                $currentQuantities[] = [
                    'product_id' => $productData['product_id'],
                    'product_name' => $productData['product_name'],
                    'quantity' => $currentQuantity,
                    'total_collected' => $productData['total_collected'],
                    'total_received' => $productData['total_received'],
                    'total_sold' => $productData['total_sold'],
                    'total_sent' => $productData['total_sent'],
                ];
                $totalQuantity += $currentQuantity;
            }
        }

        return [
            'total_quantity' => $totalQuantity,
            'products' => $currentQuantities,
            'product_count' => count($currentQuantities),
            'summary' => [
                'total_collected' => $totalCollected,
                'total_received' => $totalReceived,
                'total_sold' => $totalSold,
                'total_sent' => $totalSent,
            ],
        ];
    }

    private function calculateCashFromSales(Staff $staff)
    {
        // Get cash sales (payment_mode = 'cash')
        $cashSales = $staff->productSales()
            ->where('payment_mode', 'cash')
            ->sum('total');

        // Get cash transfers to company
        $cashTransfers = $staff->cashTransfers()->sum('amount');

        // Calculate net cash in hand
        $cashInHand = $cashSales - $cashTransfers;

        return [
            'total_cash_sales' => $cashSales,
            'total_cash_transfers' => $cashTransfers,
            'cash_in_hand' => $cashInHand,
        ];
    }

    private function calculatePendingAmounts(Staff $staff)
    {
        // Get total loans given to staff
        $totalLoans = $staff->loans()->sum('amount');
        
        // Get total loan repayments
        $totalLoanRepayments = $staff->payments()->sum('loan_deduction');
        
        // Calculate loan balance
        $loanBalance = $totalLoans - $totalLoanRepayments;

        // Get pending discrepancies
        $pendingDiscrepancies = $staff->discrepancies()
            ->where('status', 'pending')
            ->sum('discrepancy_amount');

        // Get total discrepancies paid
        $paidDiscrepancies = $staff->payments()->sum('discrepancy_deduction');

        // Calculate total pending amount
        $totalPending = $loanBalance + $pendingDiscrepancies;

        return [
            'loan_balance' => $loanBalance,
            'pending_discrepancies' => $pendingDiscrepancies,
            'paid_discrepancies' => $paidDiscrepancies,
            'total_pending' => $totalPending,
            'total_loans_given' => $totalLoans,
            'total_loan_repayments' => $totalLoanRepayments,
        ];
    }

    private function getRecentActivities(Staff $staff)
    {
        $activities = collect();

        // Get recent sales
        $recentSales = $staff->productSales()
            ->with(['product', 'customer'])
            ->latest('date')
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'type' => 'sale',
                    'date' => $sale->date,
                    'description' => "Sold {$sale->quantity} {$sale->product->name} for " . number_format($sale->total, 2),
                    'amount' => $sale->total,
                    'payment_mode' => $sale->payment_mode,
                ];
            });

        // Get recent cash transfers
        $recentTransfers = $staff->cashTransfers()
            ->latest('date')
            ->limit(5)
            ->get()
            ->map(function ($transfer) {
                return [
                    'type' => 'cash_transfer',
                    'date' => $transfer->date,
                    'description' => "Transferred " . number_format($transfer->amount, 2) . " to company",
                    'amount' => $transfer->amount,
                ];
            });

        // Get recent product transfers
        $recentProductTransfers = $staff->productsReceived()
            ->with('product')
            ->latest('date')
            ->limit(5)
            ->get()
            ->map(function ($transfer) {
                return [
                    'type' => 'product_received',
                    'date' => $transfer->date,
                    'description' => "Received {$transfer->quantity} {$transfer->product->name}",
                    'quantity' => $transfer->quantity,
                ];
            });

        // Get recent product collections
        $recentCollections = $staff->productCollections()
            ->with('product')
            ->latest('date')
            ->limit(5)
            ->get()
            ->map(function ($collection) {
                return [
                    'type' => 'product_collected',
                    'date' => $collection->date,
                    'description' => "Collected {$collection->quantity} {$collection->product->name} from supplier",
                    'quantity' => $collection->quantity,
                ];
            });

        // Merge and sort by date
        $activities = $recentSales->concat($recentTransfers)->concat($recentProductTransfers)->concat($recentCollections)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return $activities;
    }
}
