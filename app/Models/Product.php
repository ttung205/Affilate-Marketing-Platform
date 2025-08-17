<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category_id',
        'stock',
        'is_active',
        'affiliate_link',
        'commission_rate'
    ];

    protected $casts = [
        'price' => 'integer', // Lưu dưới dạng integer
        'is_active' => 'boolean',
        'commission_rate' => 'decimal:2'
    ];

    // Accessor để format giá tiền khi hiển thị
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.') . ' VND';
    }

    // Mutator để xử lý giá tiền khi lưu
    public function setPriceAttribute($value)
    {
        // Loại bỏ tất cả ký tự không phải số
        $this->attributes['price'] = (int) preg_replace('/[^0-9]/', '', $value);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}