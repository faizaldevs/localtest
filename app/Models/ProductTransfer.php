<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_staff_id',
        'to_staff_id',
        'location_id',
        'product_id',
        'quantity',
        'notes'
    ];

    public function fromStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'from_staff_id');
    }

    public function toStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'to_staff_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
