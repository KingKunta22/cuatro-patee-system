<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sale_date',
        'customer_name',
        'total_amount',
        'cash_received',
        'change'
    ];

    // Relationship: A sale has many items
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}