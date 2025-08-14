<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'stock',
        'is_active',
        'affiliate_link',
        'commission_rate'
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    // Scope để lấy sản phẩm active
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope để lấy sản phẩm theo category
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
