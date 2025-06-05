<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use HasFactory;    protected $fillable = [
        'supplier_id',
        'period_from',
        'period_to',
        'total_quantity',
        'average_cost',
        'total_amount',
        'paid_amount',
        'payment_date',
        'notes',
        'loan_deduction',
        'amount_paid',
        'payment_adjustment'
    ];    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'payment_date' => 'date',
        'total_quantity' => 'decimal:2',
        'average_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'payment_adjustment' => 'decimal:2'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function staffDiscrepancy()
    {
        return $this->hasOne(StaffDiscrepancy::class);
    }
}
