<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'location_id',
        'salary'
    ];

    protected $appends = ['total_products'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function productCollections(): HasMany
    {
        return $this->hasMany(ProductCollection::class);
    }

    public function productsReceived(): HasMany
    {
        return $this->hasMany(ProductTransfer::class, 'to_staff_id');
    }

    public function productsSent(): HasMany
    {
        return $this->hasMany(ProductTransfer::class, 'from_staff_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function getTotalProductsAttribute()
    {
        $collectionsTotal = $this->productCollections()->sum('quantity');
        $receivedTotal = $this->productsReceived()->sum('quantity');
        $sentTotal = $this->productsSent()->sum('quantity');
        
        return $collectionsTotal + $receivedTotal - $sentTotal;
    }
}
