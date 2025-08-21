<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customerName', 
        'totalAmount',
        'saleDate',
    ];

    public function inventories()
    {
        return $this->belongsToMany(Inventory::class, 'sale_items')
                    ->withPivot('quantity', 'price') // sold qty + price at sale time
                    ->withTimestamps();
    }
}

