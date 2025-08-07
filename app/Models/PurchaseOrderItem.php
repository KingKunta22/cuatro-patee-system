<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'productName',
        'quantity',
        'unitPrice',
        'totalAmount',
        'purchase_order_id',
    ];

    public function order() {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}
