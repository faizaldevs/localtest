<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'amount',
        'date',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
