<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
    ];

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class); 
    }

    public function purchaseOrderItem() {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function brand() {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function badItems() {
        return $this->hasMany(BadItem::class);
    }
}