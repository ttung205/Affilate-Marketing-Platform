@extends('publisher.layouts.app')

@section('title', 'Tài khoản thanh toán')

@section('content')

    <div class="payment-methods-container">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <!-- Header -->
        <div class="payment-methods-header">
            <div class="header-content">
                <h1 class="payment-methods-title">
                    Tài khoản thanh toán
                </h1>
                <p class="payment-methods-subtitle">Quản lý tài khoản ngân hàng nhận tiền của bạn</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openPaymentMethodModal()">
                    <i class="fas fa-plus"></i>
                    Thêm tài khoản
                </button>
            </div>
        </div>

        <!-- Payment Methods List -->
        <div class="payment-methods-list">
            <div class="list-header">
                <h3 class="section-title">Danh sách tài khoản thanh toán</h3>
                <div class="list-actions">
                    <button class="btn btn-outline-primary" onclick="refreshList()">
                        <i class="fas fa-sync-alt"></i>
                        Làm mới
                    </button>
                </div>
            </div>

            <div class="payment-methods-grid" id="payment-methods-grid">
                @forelse($paymentMethods as $method)
                    <div class="payment-method-card {{ $method['is_default'] ? 'default' : '' }}"
                        data-method-id="{{ $method['id'] }}">
                        <div class="card-header">
                            <div class="method-icon">
                                <i class="{{ $method['icon'] }}"></i>
                            </div>
                            <div class="method-info">
                                <h4 class="method-title">{{ $method['type_label'] }}</h4>
                                <p class="method-subtitle">{{ $method['account_name'] ?? 'N/A' }}</p>
                            </div>
                            @if($method['is_default'])
                                <div class="default-badge">
                                    <i class="fas fa-star"></i>
                                    Mặc định
                                </div>
                            @endif
                        </div>

                        <div class="card-body">
                            <div class="method-details">
                                <div class="detail-row">
                                    <span class="detail-label">Số tài khoản:</span>
                                    <span class="detail-value">{{ $method['masked_account_number'] }}</span>
                                </div>
                                @if(isset($method['bank_name']) && $method['bank_name'])
                                    <div class="detail-row">
                                        <span class="detail-label">Ngân hàng:</span>
                                        <span class="detail-value">{{ $method['bank_name'] }}</span>
                                    </div>
                                @endif
                                @if(isset($method['branch_name']) && $method['branch_name'])
                                    <div class="detail-row">
                                        <span class="detail-label">Chi nhánh:</span>
                                        <span class="detail-value">{{ $method['branch_name'] }}</span>
                                    </div>
                                @endif
                                <div class="detail-row">
                                    <span class="detail-label">Phí rút tiền:</span>
                                    <span class="detail-value">{{ number_format($method['fee_rate'] * 100, 1) }}%</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Trạng thái:</span>
                                    <span class="detail-value">
                                        <span class="status-badge {{ $method['is_verified'] ? 'verified' : 'pending' }}">
                                            {{ $method['is_verified'] ? 'Đã xác minh' : 'Chờ xác minh' }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick="paymentMethodManager.editPaymentMethod({{ $method['id'] }})" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if(!$method['is_default'])
                                    <button class="btn btn-sm btn-outline-success"
                                        onclick="paymentMethodManager.setAsDefault({{ $method['id'] }})" title="Đặt làm mặc định">
                                        <i class="fas fa-star"></i>
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="paymentMethodManager.deletePaymentMethod({{ $method['id'] }})" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-credit-card"></i>
                        <h3>Chưa có tài khoản thanh toán nào</h3>
                        <p>Thêm tài khoản ngân hàng để có thể rút tiền</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Payment Method Modal -->
    <div id="paymentMethodModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm tài khoản thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentMethodForm" method="POST" action="{{ route('publisher.payment-methods.store') }}">
                        @csrf
                        <input type="hidden" id="methodId" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Loại tài khoản</label>
                                    <select class="form-select" id="methodType" name="type" required readonly disabled style="background-color: #f8f9fa;">
                                        <option value="bank_transfer" selected>Tài khoản ngân hàng</option>
                                    </select>
                                    <input type="hidden" name="type" value="bank_transfer">
                                    <small class="text-muted">Hiện tại chỉ hỗ trợ tài khoản ngân hàng</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tên chủ tài khoản</label>
                                    <input type="text" class="form-control" id="accountName" name="account_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số tài khoản/Số điện thoại</label>
                                    <input type="text" class="form-control" id="accountNumber" name="account_number"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6" id="bankNameField">
                                <div class="mb-3">
                                    <label class="form-label">Ngân hàng</label>
                                    <div class="custom-dropdown" id="bankDropdown">
                                        <div class="dropdown-trigger" id="bankDropdownTrigger">
                                            <input type="text" 
                                                   class="form-control dropdown-search" 
                                                   id="bankSearch" 
                                                   placeholder="Tìm kiếm ngân hàng..."
                                                   autocomplete="off">
                                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                                        </div>
                                        <div class="dropdown-panel" id="bankDropdownPanel">
                                            <div class="dropdown-items" id="bankDropdownItems">
                                                @foreach($supportedBanks as $bank)
                                                    <div class="dropdown-item" 
                                                         data-value="{{ $bank['name'] }}" 
                                                         data-code="{{ $bank['code'] }}">
                                                        <span class="bank-name">{{ $bank['name'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <input type="hidden" id="bankName" name="bank_name" value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="bankDetailsFields">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mã ngân hàng</label>
                                    <input type="text" class="form-control" id="bankCode" name="bank_code" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Chi nhánh</label>
                                    <input type="text" class="form-control" id="branchName" name="branch_name">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isDefault" name="is_default">
                                <label class="form-check-label" for="isDefault">
                                    Đặt làm tài khoản mặc định
                                </label>
                            </div>
                        </div>

                        <div class="payment-method-preview" id="paymentMethodPreview" style="display: none;">
                            <h6>Xem trước:</h6>
                            <div class="preview-card">
                                <div class="preview-icon">
                                    <i id="previewIcon" class="fas fa-credit-card"></i>
                                </div>
                                <div class="preview-content">
                                    <h5 id="previewTitle">Tài khoản thanh toán</h5>
                                    <p id="previewSubtitle">Tên chủ tài khoản</p>
                                    <p id="previewDetails">Chi tiết</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" form="paymentMethodForm" class="btn btn-primary">Lưu</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')

    <link rel="stylesheet" href="{{ asset('css/publisher/payment-methods.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/publisher/payment-methods.js') }}"></script>
@endpush

