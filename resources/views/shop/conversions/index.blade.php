@extends('shop.layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Quản lý Đơn Publisher')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop/conversions.css') }}">
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
</li>
<li class="breadcrumb-item active">
    <span>Đơn Publisher</span>
</li>
@endsection

@section('content')
<div class="conversions-page">
    <div class="page-header">
        <div>
            <h1>Đơn Publisher</h1>
            <p>Theo dõi và duyệt các đơn hàng đến từ publisher của shop.</p>
        </div>
        <div class="summary-cards">
            <div class="summary-card pending">
                <span class="label">Chờ duyệt</span>
                <span class="value">{{ number_format($summary['pending']['count']) }}</span>
                <span class="amount">{{ number_format($summary['pending']['amount']) }} VNĐ</span>
            </div>
            <div class="summary-card approved">
                <span class="label">Đã duyệt</span>
                <span class="value">{{ number_format($summary['approved']['count']) }}</span>
                <span class="amount">{{ number_format($summary['approved']['amount']) }} VNĐ</span>
            </div>
            <div class="summary-card rejected">
                <span class="label">Đã từ chối</span>
                <span class="value">{{ number_format($summary['rejected']['count']) }}</span>
                <span class="amount">{{ number_format($summary['rejected']['amount']) }} VNĐ</span>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('shop.conversions.index') }}" class="filter-form">
            <div class="filter-group">
                <label for="search">Tìm kiếm</label>
                <input type="text" id="search" name="search" value="{{ $filters['search'] }}" placeholder="Mã đơn, tracking code, publisher...">
            </div>
            <div class="filter-group">
                <label for="status">Trạng thái</label>
                <select name="status" id="status">
                    <option value="">Tất cả</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="date_from">Từ ngày</label>
                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="filter-group">
                <label for="date_to">Đến ngày</label>
                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Lọc dữ liệu
                </button>
                <a href="{{ route('shop.conversions.index') }}" class="btn btn-secondary">Đặt lại</a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h2>Danh sách đơn hàng</h2>
            <span class="total-count">Tổng cộng: {{ number_format($conversions->total()) }} đơn</span>
        </div>

        @if($conversions->count() > 0)
        <div class="table-responsive">
            <table class="conversions-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Sản phẩm</th>
                        <th>Publisher</th>
                        <th>Giá trị đơn</th>
                        <th>Hoa hồng</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conversions as $conversion)
                    <tr class="status-{{ $conversion->status }}">
                        <td>
                            <strong>{{ $conversion->order_id }}</strong>
                            <div class="sub-text">Tracking: {{ $conversion->tracking_code }}</div>
                        </td>
                        <td>
                            <div class="product-cell">
                                <img src="{{ get_image_url($conversion->product->image) }}" alt="{{ $conversion->product->name }}">
                                <div>
                                    <div class="product-name">{{ $conversion->product->name }}</div>
                                    <div class="sub-text">{{ $conversion->product->category->name ?? 'Không có danh mục' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="publisher-cell">
                                <div class="publisher-name">{{ $conversion->affiliateLink->publisher->name ?? 'N/A' }}</div>
                                <div class="sub-text">{{ $conversion->affiliateLink->publisher->email ?? 'Chưa cập nhật' }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="amount">{{ number_format($conversion->amount) }} VNĐ</div>
                            <div class="sub-text">Tỷ lệ: {{ $conversion->getCommissionRateAttribute() }}%</div>
                        </td>
                        <td>
                            <div class="commission">{{ number_format($conversion->commission) }} VNĐ</div>
                            @if($conversion->is_commission_processed)
                                <div class="badge badge-success">Đã trả hoa hồng</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $conversion->converted_at?->format('d/m/Y H:i') }}</div>
                            <div class="sub-text">Cập nhật: {{ $conversion->status_changed_at?->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}</div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $conversion->status }}">{{ $statuses[$conversion->status] ?? strtoupper($conversion->status) }}</span>
                        </td>
                        <td>
                            @if($conversion->status_note)
                                <span class="status-note" title="{{ $conversion->status_note }}">
                                    {{ Str::limit($conversion->status_note, 40) }}
                                </span>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td>
                            @if($conversion->isPending())
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('shop.conversions.update-status', $conversion) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                    </form>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $conversion->id }}">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>

                                    <div class="modal fade" id="rejectModal{{ $conversion->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $conversion->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('shop.conversions.update-status', $conversion) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="rejectModalLabel{{ $conversion->id }}">Từ chối đơn hàng</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Bạn có chắc chắn muốn từ chối đơn hàng <strong>{{ $conversion->order_id }}</strong> không?</p>
                                                        <input type="hidden" name="status" value="rejected">
                                                        <div class="mb-3">
                                                            <label for="status_note_{{ $conversion->id }}" class="form-label">Ghi chú (tuỳ chọn)</label>
                                                            <textarea class="form-control" id="status_note_{{ $conversion->id }}" name="status_note" rows="3" placeholder="Lý do từ chối..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted">--</div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $conversions->links() }}
        </div>
        @else
        <div class="empty-state">
            <img src="{{ asset('images/empty-state.svg') }}" alt="No conversions">
            <p>Chưa có đơn hàng nào từ publisher.</p>
        </div>
        @endif
    </div>
</div>
@endsection
