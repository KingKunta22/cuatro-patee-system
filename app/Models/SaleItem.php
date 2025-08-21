<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'inventory_id',
        'quantity',
        'price',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
