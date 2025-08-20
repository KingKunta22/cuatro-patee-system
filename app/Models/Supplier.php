<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //
    protected $fillable = [
        'supplierName',
        'supplierAddress',
        'supplierContactNumber',
        'supplierEmailAddress',
        'supplierStatus',
    ];

    public function purchaseOrder(){
        return $this->hasMany(PurchaseOrder::class, 'supplierId');
    }

    public function delivery(){
        return $this->belongsTo(Delivery::class);
    }
}
