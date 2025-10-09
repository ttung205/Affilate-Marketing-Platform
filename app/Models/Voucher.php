<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id','publisher_id','code','type','value',
        'min_order','max_uses','used_count','is_active',
        'expires_at','is_global'
    ];

    protected $casts = [
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'is_global' => 'boolean'
    ];

    public function shop()
    {
        return $this->belongsTo(\App\Models\User::class, 'shop_id');
    }

    public function publisher()
    {
        return $this->belongsTo(\App\Models\User::class, 'publisher_id');
    }

    public function products()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'product_voucher');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)
                 ->where(function($s){ $s->whereNull('expires_at')->orWhere('expires_at','>=',now()); });
    }
}
