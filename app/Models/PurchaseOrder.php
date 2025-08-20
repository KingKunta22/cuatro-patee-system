<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    
    protected $fillable = [
        'orderNumber',
        'supplierId',
        'paymentTerms',
        'deliveryDate',
        'totalAmount',
        'orderStatus',
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class, 'supplierId');
    }

    public function items(){
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function inventory(){
        return $this->hasMany(Inventory::class, 'purchase_order_id');
    }

    public function delivery(){
        return $this->hasMany(Delivery::class);
    }
}
