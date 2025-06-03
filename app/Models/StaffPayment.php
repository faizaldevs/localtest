<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayment extends Model
{
    use HasFactory;    protected $fillable = [
        'staff_id',
        'payment_date',
        'period_from',
        'period_to',
        'base_amount',
        'bonus_amount',
        'total_gross_amount',
        'loan_deduction',
        'discrepancy_deduction',
        'other_deductions',
        'total_deductions',
        'net_amount_paid',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'base_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'total_gross_amount' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'discrepancy_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_amount_paid' => 'decimal:2'
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    // Calculate total gross amount automatically
    public function calculateGrossAmount()
    {
        return $this->base_amount + $this->bonus_amount;
    }

    // Calculate total deductions automatically
    public function calculateTotalDeductions()
    {
        return $this->loan_deduction + $this->discrepancy_deduction + $this->other_deductions;
    }

    // Calculate net amount automatically
    public function calculateNetAmount()
    {
        return $this->calculateGrossAmount() - $this->calculateTotalDeductions();
    }

    // Boot method to auto-calculate amounts
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($staffPayment) {
            $staffPayment->total_gross_amount = $staffPayment->calculateGrossAmount();
            $staffPayment->total_deductions = $staffPayment->calculateTotalDeductions();
            $staffPayment->net_amount_paid = $staffPayment->calculateNetAmount();
        });
    }
}
