<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'quantity',
        'cost_price',
        'selling_price',
        'expiration_date',
        'purchase_order_id',
        'purchase_order_item_id',
    ];

    // Replace $dates with $casts
    protected $casts = [
        'expiration_date' => 'datetime',
    ];

    /**
     * Get the product that owns this batch
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the purchase order for this batch
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the purchase order item for this batch
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * Scope to get batches that are not expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expiration_date', '>', now());
    }

    /**
     * Scope to get batches ordered by expiration date (FIFO)
     */
    public function scopeByExpiration($query)
    {
        return $query->orderBy('expiration_date', 'asc');
    }
}