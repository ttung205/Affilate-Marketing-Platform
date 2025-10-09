@extends('shop.layouts.app')
@section('title','Tạo Voucher')

<style>
     select[name="publisher_id"],
    select[name="type"],
    select[name="is_global"] {
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23333' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 16px center !important;
        background-size: 12px !important;
        background-color: white !important;
</style>

@section('content')
<div class="container">
  <h1 class="mb-4">Tạo Voucher</h1>

  <form action="{{ route('shop.vouchers.store') }}" method="POST">
    @csrf

    {{-- Publisher --}}
    <div class="mb-3">
      <label class="form-label">Gửi tới Publisher</label>
      <select name="publisher_id" class="form-control">
        <option value="">-- Gửi cho tất cả publisher --</option>
        @foreach ($publishers as $publisher)
          <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
        @endforeach
      </select>
    </div>

    {{-- Loại voucher --}}
    <div class="mb-3">
      <label class="form-label">Loại voucher</label>
      <select id="voucher_type" name="type" class="form-control" required>
        <option value="percent">Giảm %</option>
        <option value="fixed">Giảm cố định</option>
        
      </select>
    </div>

    {{-- Mã voucher --}}
    <div class="mb-3">
      <label class="form-label">Mã voucher</label>
      <input name="code" class="form-control" placeholder="VD: PUB2025" required>
    </div>

    {{-- Giá trị --}}
    <div class="mb-3">
      <label class="form-label">Giá trị</label>
      <input id="voucher_value" name="value" type="number" step="0.01" class="form-control" placeholder="Nhập giá trị">
      <small id="valueHint" class="text-muted"></small>
    </div>

    {{-- Đơn tối thiểu --}}
    <div class="mb-3">
      <label class="form-label">Đơn tối thiểu (VNĐ)</label>
      <input name="min_order" type="number" class="form-control">
    </div>

    {{-- Số lượng tối đa --}}
    <div class="mb-3">
      <label class="form-label">Số lượng tối đa</label>
      <input name="max_uses" type="number" class="form-control">
    </div>

    {{-- Hạn dùng --}}
    <div class="mb-3">
      <label class="form-label">Hạn dùng</label>
      <input name="expires_at" type="date" class="form-control">
    </div>

    {{-- Phạm vi áp dụng --}}
    <div class="mb-3">
      <label class="form-label">Phạm vi áp dụng</label>
      <select id="apply_scope" name="is_global" class="form-control">
        <option value="1">Toàn shop</option>
        <option value="0">Theo sản phẩm</option>
      </select>
    </div>

    {{-- Danh sách sản phẩm --}}
    <div class="mb-3" id="product-selection" style="display:none;">
      <label class="form-label">Chọn sản phẩm áp dụng</label>
      <select name="product_ids[]" class="form-control" multiple>
        @foreach($products as $prod)
          <option value="{{ $prod->id }}">{{ $prod->name }}</option>
        @endforeach
      </select>
      <small class="text-muted">Giữ Ctrl (hoặc Cmd trên Mac) để chọn nhiều sản phẩm.</small>
    </div>

    <div class="mt-4">
      <button class="btn btn-success">Tạo voucher</button>
      <a href="{{ route('shop.vouchers.index') }}" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const scope = document.getElementById('apply_scope');
  const productSel = document.getElementById('product-selection');
  const typeSelect = document.getElementById('voucher_type');
  const valueInput = document.getElementById('voucher_value');
  const hint = document.getElementById('valueHint');

  function toggleProducts() {
    productSel.style.display = (scope.value === '0') ? 'block' : 'none';
  }

  function updateValueLimit() {
    if (typeSelect.value === 'percent') {
      valueInput.min = 1;
      valueInput.max = 100;
      hint.innerText = 'Nhập giá trị % từ 1 đến 100.';
    } else {
      valueInput.removeAttribute('min');
      valueInput.removeAttribute('max');
      hint.innerText = '';
    }
  }

  scope.addEventListener('change', toggleProducts);
  typeSelect.addEventListener('change', updateValueLimit);

  toggleProducts();
  updateValueLimit();
});
</script>
@endpush
@endsection
