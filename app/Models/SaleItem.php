<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_batch_id', // Change from inventory_id to product_batch_id
        'product_id',       // Add product_id for direct product reference
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

    // Relationship: An item belongs to a product batch
    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    // Relationship: An item belongs to a product (direct reference)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Keep inventory relationship for backward compatibility if needed
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}