<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
            'orderNumber',
            'supplierId',
            'productName',
            'paymentTerms',
            'unitPrice',
            'quantity',
            'deliveryDate',
            'totalAmount',
            'orderStatus',
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class, 'supplierId');
    }
}
