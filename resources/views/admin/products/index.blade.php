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
            <a href="{{ route('admin.products.create') }}" class="product-add-btn">
                <i class="fas fa-plus"></i> Thêm sản phẩm
            </a>
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
                            <th>Tỷ lệ hoa hồng</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                            class="product-table-image">
                                    @else
                                        <div class="product-no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="product-name">{{ $product->name }}</div>
                                    <div class="product-description">{{ Str::limit($product->description, 50) }}</div>
                                </td>
                                <td>
                                    @if($product->category)
                                        <span class="category-badge">
                                            {{ $product->category->name }}
                                        </span>
                                    @else
                                        <span class="no-category">Không có danh mục</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="product-price">{{ $product->formatted_price }}</span>
                                </td>
                                <td>
                                    <span class="stock-count {{ $product->stock > 0 ? 'in-stock' : 'out-of-stock' }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td>
                                    <span class="commission-rate-badge">
                                        {{ $product->commission_rate ?? 0 }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $product->is_active ? 'active' : 'inactive' }}">
                                        {{ $product->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="product-action-buttons">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="product-btn-edit"
                                            title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <button type="button" class="product-btn-toggle"
                                            title="{{ $product->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}"
                                            onclick="showToggleProductStatusConfirm('{{ $product->id }}', '{{ $product->name }}', {{ $product->is_active ? 'true' : 'false' }})">
                                            <i class="fas fa-{{ $product->is_active ? 'ban' : 'eye' }}"></i>
                                        </button>

                                        <button type="button" class="product-btn-delete" title="Xóa"
                                            onclick="showDeleteProductConfirm('{{ $product->id }}', '{{ $product->name }}')">
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

<!-- Hidden Forms for Actions -->
<form id="toggle-product-status-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="delete-product-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function showToggleProductStatusConfirm(productId, productName, isActive) {
    const action = isActive ? 'vô hiệu hóa' : 'kích hoạt';
    const actionText = isActive ? 'Vô hiệu hóa' : 'Kích hoạt';
    
    showConfirmPopup({
        title: `${actionText} sản phẩm`,
        message: `Bạn có chắc chắn muốn ${action} sản phẩm này?`,
        details: `Sản phẩm: ${productName}`,
        type: 'warning',
        confirmText: actionText,
        onConfirm: () => {
            const form = document.getElementById('toggle-product-status-form');
            form.action = `{{ route('admin.products.index') }}/${productId}/toggle-status`;
            form.submit();
        }
    });
}

function showDeleteProductConfirm(productId, productName) {
    showConfirmPopup({
        title: 'Xóa sản phẩm',
        message: 'Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.',
        details: `Sản phẩm: ${productName}`,
        type: 'danger',
        confirmText: 'Xóa',
        onConfirm: () => {
            const form = document.getElementById('delete-product-form');
            form.action = `{{ route('admin.products.index') }}/${productId}`;
            form.submit();
        }
    });
}
</script>
@endsection