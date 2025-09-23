<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'productName',
        'productSKU', 
        'brand_id',           // ← Changed from productBrand
        'category_id',        // ← Changed from productCategory
        'productItemMeasurement',
        'productSellingPrice',
        'productCostPrice',
        'productImage',
        'is_perishable',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get all batches for this product
     */
    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    /**
     * Get total stock quantity across all batches
     */
    public function getTotalStockAttribute(): int
    {
        return $this->batches->sum('quantity');
    }

    /**
     * Get the earliest expiration date among batches
     */
    public function getEarliestExpirationDateAttribute()
    {
        return $this->batches->min('expiration_date');
    }
}