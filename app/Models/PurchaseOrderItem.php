<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'productName',
        'quantity',
        'unitPrice',
        'itemMeasurement',
        'totalAmount',
        'purchase_order_id',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function badItems()
    {
        return $this->hasMany(BadItem::class, 'purchase_order_item_id');
    }

    public function productBatches()
    {
        return $this->hasMany(ProductBatch::class, 'purchase_order_item_id');
    }
    
}