<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['productBrand']; // Changed to match database column

    // Remove or update the accessor since you don't have a 'name' column
    public function getProductBrandAttribute($value)
    {
        return $value ?? 'N/A';
    }
}