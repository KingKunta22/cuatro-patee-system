<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name']; // Changed from 'productCategory'

    // Optional: If you want to use a different display name
    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? $this->attributes['productCategory'] ?? 'N/A';
    }
}