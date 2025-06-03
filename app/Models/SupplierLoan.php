<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'amount',
        'date',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
