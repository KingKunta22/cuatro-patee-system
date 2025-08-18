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

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'purchase_order_item_id');
    }
    
}