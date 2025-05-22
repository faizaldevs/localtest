<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
