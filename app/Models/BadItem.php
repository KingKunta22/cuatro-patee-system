<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'purchase_order_item_id',
        'quality_status',
        'item_count',
        'notes',
        'status'
    ];

    protected $attributes = [
        'status' => 'Pending',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}