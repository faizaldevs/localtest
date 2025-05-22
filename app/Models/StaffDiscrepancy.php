<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDiscrepancy extends Model
{
    protected $fillable = [
        'staff_id',
        'supplier_payment_id',
        'discrepancy_amount',
        'notes',
        'status'
    ];

    protected $casts = [
        'discrepancy_amount' => 'decimal:2'
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function supplierPayment(): BelongsTo
    {
        return $this->belongsTo(SupplierPayment::class);
    }
}
