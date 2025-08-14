@extends('components.dashboard.layout')

@section('title', 'Quản lý sản phẩm - Admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/products.css') }}">
@endpush

@section('content')
<div class="product-management-content">
    <div class="product-management-header">
        <div class="product-management-title">
            <h2>Quản lý sản phẩm</h2>
            <p>Quản lý tất cả sản phẩm trong hệ thống</p>
        </div>
        <button class="product-add-btn">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </button>
    </div>
    
    <div class="product-management-card">
        <div class="product-management-card-header">
            <h5 class="product-card-title">Danh sách sản phẩm</h5>
            <div class="product-search-box">
                <input type="text" class="product-search-input" placeholder="Tìm kiếm sản phẩm...">
                <button class="product-search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <div class="product-management-card-body">
            <table class="product-management-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Hoa hồng</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" 
                                 class="product-table-image">
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>
                            <span class="product-badge product-badge-info">{{ $product->category }}</span>
                        </td>
                        <td>{{ number_format($product->price) }}₫</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->commission_rate }}%</td>
                        <td>
                            @if($product->is_active)
                                <span class="product-badge product-badge-success">Hoạt động</span>
                            @else
                                <span class="product-badge product-badge-secondary">Không hoạt động</span>
                            @endif
                        </td>
                        <td>
                            <div class="product-action-buttons">
                                <button class="product-btn-edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="product-btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection