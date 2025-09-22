<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        // 'productName',
        // 'productSKU',
        // 'productBatch',
        // 'productBrand', // Text field
        // 'productCategory', // Text field
        // 'productStock',
        // 'productSellingPrice',
        // 'productCostPrice',
        // 'productProfitMargin',
        // 'productItemMeasurement',
        // 'productExpirationDate',
        // 'productImage',
        // 'purchase_order_id',
        // 'purchase_order_item_id',
        // 'brand_id', // Add for relationship
        // 'category_id', // Add for relationship
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