<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'inventory_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price'
    ];

    // Relationship: An item belongs to a sale
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Relationship: An item belongs to an inventory product
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}