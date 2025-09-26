<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['productCategory']; // Changed to match database column

    // Remove or update the accessor since you don't have a 'name' column
    public function getProductCategoryAttribute($value)
    {
        return $value ?? 'N/A';
    }
}