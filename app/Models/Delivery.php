<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Delivery extends Model
{
    protected $fillable = [
        'deliveryId',
        'orderStatus',
        'purchase_order_id',
        'last_updated_by',
        'status_updated_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->deliveryId)) {
                $model->deliveryId = self::generateDeliveryId();
            }
            
            // Set initial values
            if (empty($model->last_updated_by)) {
                $model->last_updated_by = 'System';
            }
            
            if (empty($model->status_updated_at)) {
                $model->status_updated_at = now();
            }
        });

        // Add this to track status changes
        static::updating(function ($model) {
            if ($model->isDirty('orderStatus')) {
                $model->status_updated_at = now();
                
                $model->last_updated_by = optional(auth()->user())->name ?? 'System';
            }
        });
    }

    public static function generateDeliveryId()
    {
        $latest = self::orderBy('id', 'desc')->first();
        $nextNumber = $latest ? ((int) str_replace('D-', '', $latest->deliveryId)) + 1 : 1;
        
        return 'D-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}