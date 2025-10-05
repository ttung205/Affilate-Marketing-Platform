<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Product([
            'user_id' => Auth::id(),
            'name' => $row['ten_san_pham'] ?? 'Sản phẩm mới',
            'description' => $row['mo_ta'] ?? null,
            'price' => $row['gia'] ?? 0,
            'stock' => $row['ton_kho'] ?? 0,
            'category_id' => $row['danh_muc_id'] ?? null,
            'affiliate_link' => $row['link_affiliate'] ?? null,
            'commission_rate' => $row['hoa_hong'] ?? 0,
            'is_active' => $row['trang_thai'] ?? 1,
        ]);
    }
}
