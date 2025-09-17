<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_name',
        'sale_date',
        'total_amount',
        'cash_received',
        'change',
        'payment_method',
        'status',
        'employee_id',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // Add accessor for processed_by
    public function getProcessedByAttribute()
    {
        // If you have an employee relationship, use it
        if ($this->employee_id && method_exists($this, 'employee')) {
            return $this->employee->name ?? 'System';
        }
        
        // Fallback to a default value
        return 'System';
    }
}