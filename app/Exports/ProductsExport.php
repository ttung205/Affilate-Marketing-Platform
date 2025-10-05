<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Lấy sản phẩm của shop đang login
        return Product::where('user_id', Auth::id())
            ->select('name', 'description', 'price', 'stock', 'category_id', 'affiliate_link', 'commission_rate', 'is_active')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tên sản phẩm',
            'Mô tả',
            'Giá',
            'Tồn kho',
            'Danh mục ID',
            'Link affiliate',
            'Hoa hồng (%)',
            'Trạng thái (1=active, 0=inactive)'
        ];
    }
}
