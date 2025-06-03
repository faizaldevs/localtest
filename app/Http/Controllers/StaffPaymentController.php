<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffPayment;
use App\Models\StaffLoan;
use App\Models\StaffDiscrepancy;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class StaffPaymentController extends Controller
{
    public function index()
    {
        $payments = StaffPayment::with('staff')
            ->latest('payment_date')
            ->paginate(15);

        return Inertia::render('StaffPayments/Index', [
            'payments' => $payments
        ]);
    }

    public function create()
    {
        $staff = Staff::all(['id', 'name', 'salary']);

        return Inertia::render('StaffPayments/Create', [
            'staff' => $staff
        ]);
    }

    public function getStaffPaymentData(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        $staff = Staff::findOrFail($request->staff_id);
          // Get staff loan balance
        $totalLoansGiven = StaffLoan::where('staff_id', $staff->id)->sum('amount');
        $totalLoanRepayments = StaffPayment::where('staff_id', $staff->id)
            ->sum('loan_deduction');
        $loanBalance = $totalLoansGiven - $totalLoanRepayments;

        // Get pending discrepancies
        $pendingDiscrepancies = StaffDiscrepancy::where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->sum('discrepancy_amount');

        // Check for existing payment in this period
        $existingPayment = StaffPayment::where('staff_id', $staff->id)
            ->where('period_from', $request->from_date)
            ->where('period_to', $request->to_date)
            ->first();

        $data = [
            'staff' => $staff,
            'loan_balance' => $loanBalance,
            'pending_discrepancies' => $pendingDiscrepancies,
            'base_salary' => $staff->salary ?? 0,
            'existing_payment' => $existingPayment
        ];

        return response()->json($data);
    }

    public function store(Request $request)
    {        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'payment_date' => 'required|date',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'base_amount' => 'required|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'discrepancy_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'payment_id' => 'nullable|exists:staff_payments,id'
        ]);        // Set defaults for nullable fields
        $validated['bonus_amount'] = $validated['bonus_amount'] ?? 0;
        $validated['loan_deduction'] = $validated['loan_deduction'] ?? 0;
        $validated['discrepancy_deduction'] = $validated['discrepancy_deduction'] ?? 0;
        $validated['other_deductions'] = $validated['other_deductions'] ?? 0;        if (isset($validated['payment_id']) && $validated['payment_id']) {
            // Update existing payment
            $payment = StaffPayment::findOrFail($validated['payment_id']);
            // Remove payment_id from data before updating
            $updateData = collect($validated)->except('payment_id')->toArray();
            $payment->update($updateData);
        } else {
            // Create new payment
            // Remove payment_id from data before creating (if present)
            $createData = collect($validated)->except('payment_id')->toArray();
            $payment = StaffPayment::create($createData);
        }// Update discrepancy status if there's a discrepancy deduction
        if ($validated['discrepancy_deduction'] > 0) {
            $this->updateDiscrepancyStatus($validated['staff_id'], $validated['discrepancy_deduction']);
        }

        return redirect()->route('staff-payments.index')
            ->with('success', 'Staff payment saved successfully.');
    }

    public function show(StaffPayment $staffPayment)
    {
        $staffPayment->load('staff');

        return Inertia::render('StaffPayments/Show', [
            'payment' => $staffPayment
        ]);
    }

    public function edit(StaffPayment $staffPayment)
    {
        $staff = Staff::all(['id', 'name', 'salary']);
        $staffPayment->load('staff');

        return Inertia::render('StaffPayments/Edit', [
            'payment' => $staffPayment,
            'staff' => $staff
        ]);
    }

    public function update(Request $request, StaffPayment $staffPayment)
    {        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'payment_date' => 'required|date',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'base_amount' => 'required|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'discrepancy_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        // Set defaults for nullable fields
        $validated['bonus_amount'] = $validated['bonus_amount'] ?? 0;
        $validated['loan_deduction'] = $validated['loan_deduction'] ?? 0;
        $validated['discrepancy_deduction'] = $validated['discrepancy_deduction'] ?? 0;
        $validated['other_deductions'] = $validated['other_deductions'] ?? 0;        $staffPayment->update($validated);

        // Update discrepancy status if there's a discrepancy deduction
        if ($validated['discrepancy_deduction'] > 0) {
            $this->updateDiscrepancyStatus($validated['staff_id'], $validated['discrepancy_deduction']);
        }

        return redirect()->route('staff-payments.index')
            ->with('success', 'Staff payment updated successfully.');
    }

    public function destroy(StaffPayment $staffPayment)
    {
        $staffPayment->delete();

        return redirect()->route('staff-payments.index')
            ->with('success', 'Staff payment deleted successfully.');
    }    /**
     * Update discrepancy status based on deduction amount
     */
    private function updateDiscrepancyStatus($staffId, $deductionAmount)
    {
        // Get pending discrepancies ordered by oldest first (FIFO)
        $pendingDiscrepancies = StaffDiscrepancy::where('staff_id', $staffId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingDeduction = $deductionAmount;

        foreach ($pendingDiscrepancies as $discrepancy) {
            if ($remainingDeduction <= 0) {
                break;
            }

            $discrepancyAmount = $discrepancy->discrepancy_amount;

            if ($remainingDeduction >= $discrepancyAmount) {
                // Full discrepancy is covered - mark as deducted
                $discrepancy->update([
                    'status' => 'deducted'
                ]);
                $remainingDeduction -= $discrepancyAmount;
            } else {
                // Partial payment - keep as pending for now
                // You could create a new discrepancy record for the remaining amount
                // and mark this one as deducted, but for simplicity we'll keep it pending
                break;
            }
        }
    }
}
