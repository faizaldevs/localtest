<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'price', 'cost'];

    protected $appends = ['total_quantity_sold'];

    public function productSales()
    {
        return $this->hasMany(ProductSale::class);
    }

    public function getTotalQuantitySoldAttribute()
    {
        return $this->productSales()->sum('quantity');
    }
}
