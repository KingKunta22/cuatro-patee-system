<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
    protected $fillable = [
        'productName',
        'productSKU',
        'productBrand',
        'productCategory',
        'productStock',
        'productSellingPrice',
        'productCostPrice',
        'productProfitMargin',
        'productItemMeasurement',
        'productExpirationDate',
        'productImage',
        'purchase_order_id',
        'purchase_order_item_id',
    ];

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class); 
        // An inventory item belongs to ONE purchase order
    }

    public function purchaseOrderItem() {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
        // An inventory item belongs to ONE purchase order item
    }
}
