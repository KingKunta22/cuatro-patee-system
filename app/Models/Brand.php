<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name']; // Changed from 'productBrand'

    // Optional: If you want to use a different display name
    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? $this->attributes['productBrand'] ?? 'N/A';
    }
}