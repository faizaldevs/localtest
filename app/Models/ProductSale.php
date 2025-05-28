<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'staff_id',
        'product_id',
        'date',
        'quantity',
        'price',
        'total',
        'payment_mode',
        'sale_type'
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
