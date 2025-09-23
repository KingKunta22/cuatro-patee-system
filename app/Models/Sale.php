<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'sale_date',
        'total_amount',
        'cash_received',
        'change',
        'payment_method',
        'status',
        'user_id',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'date',
    ];

    // Relationship to User who processed the sale
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // Accessor for processed_by (fallback)
    public function getProcessedByAttribute()
    {
        return $this->user->name ?? 'System';
    }
}