<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'orderNumber',
        'supplierId',
        'paymentTerms',
        'deliveryDate',
        'totalAmount',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplierId');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'purchase_order_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}