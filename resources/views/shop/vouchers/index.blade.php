@extends('shop.layouts.app')

@section('title', 'Voucher cho Publisher')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Danh sách Voucher</h1>
        <a href="{{ route('shop.vouchers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo voucher mới
        </a>
    </div>

    {{-- Hiển thị thông báo thành công --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($vouchers->isEmpty())
        <div class="alert alert-info">Chưa có voucher nào được tạo.</div>
    @else
        @foreach($vouchers as $v)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1">{{ $v->code }}</h5>
                            <div class="text-muted small">
                                @if($v->publisher)
                                    <i class="fas fa-user"></i> {{ $v->publisher->name }}
                                @else
                                    <i class="fas fa-users"></i> Gửi cho tất cả publisher
                                @endif
                            </div>
                        </div>
                        <span class="badge {{ $v->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $v->is_active ? 'Đang hoạt động' : 'Vô hiệu' }}
                        </span>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div>Loại: <strong>{{ ucfirst($v->type) }}</strong></div>
                            <div>Đơn tối thiểu: <strong>{{ number_format($v->min_order, 0, ',', '.') }}đ</strong></div>
                        </div>
                        <div class="col-md-4">
                            <div>
                                Giá trị: <strong>
                                    @if($v->type === 'percent')
                                        {{ $v->value }}%
                                    @elseif($v->type === 'fixed')
                                        {{ number_format($v->value, 0, ',', '.') }}đ
                                    @else
                                        {{-- Miễn phí vận chuyển --}}
                                    @endif
                                </strong>
                            </div>
                            <div>Đã dùng: <strong>{{ $v->used_count }}/{{ $v->max_uses }}</strong></div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div>HSD: {{ $v->expires_at ? $v->expires_at->format('d/m/Y') : '-' }}</div>

                            {{-- nút Chi tiết (nếu cần) --}}
                            {{-- <a href="{{ route('shop.vouchers.show', $v) }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a> --}}

        
                            <button
                                type="button"
                                class="btn btn-danger btn-sm mt-2 btn-open-delete-modal"
                                data-action="{{ route('shop.vouchers.destroy', $v) }}"
                                data-code="{{ $v->code }}">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </button>

          
                            <noscript>
                                <form action="{{ route('shop.vouchers.destroy', $v) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm mt-2">
                                        <i class="fas fa-trash-alt"></i> Xóa
                                    </button>
                                </form>
                            </noscript>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $vouchers->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="voucherDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="voucherDeleteForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Xác nhận xóa voucher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <p id="voucherDeleteMessage">Bạn có chắc muốn xóa voucher này không?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-danger">Xóa</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.btn-open-delete-modal');
    const deleteModalEl = document.getElementById('voucherDeleteModal');
    const deleteForm = document.getElementById('voucherDeleteForm');
    const deleteMessage = document.getElementById('voucherDeleteMessage');

    let bsModal;
    if (typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(deleteModalEl);
    }

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const action = this.getAttribute('data-action');
            const code = this.getAttribute('data-code') || '';
            deleteForm.setAttribute('action', action);
            deleteMessage.textContent = `Bạn có chắc chắn muốn xóa voucher "${code}"?`;
            // show modal
            if (bsModal) bsModal.show();
        });
    });
});
</script>
@endpush
