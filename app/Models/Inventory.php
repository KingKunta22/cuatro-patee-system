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
        'productExpirationDate',
        'productImage',
        'purchase_order_id',
        'purchase_order_item_id',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }   

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
