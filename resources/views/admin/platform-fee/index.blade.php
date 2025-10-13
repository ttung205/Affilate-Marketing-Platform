@extends('components.dashboard.layout')

@section('title', 'Quản lý Phí sàn')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/platform-fee.css') }}">
@endpush

@section('content')
<div class="platform-fee-management-content">
    <div class="platform-fee-management-header">
        <div class="platform-fee-header-left">
            <h1 class="platform-fee-page-title">Quản lý Phí sàn</h1>
            <p class="platform-fee-page-description">Cài đặt % phí sàn cho các shop</p>
        </div>
    </div>

    <!-- Current Active Fee Card -->
    @if($currentFee)
    <div class="platform-fee-current-card">
        <div class="current-fee-badge">
            <i class="fas fa-check-circle"></i>
            Phí đang áp dụng
        </div>
        <div class="current-fee-content">
            <div class="current-fee-percentage">{{ $currentFee->fee_percentage }}%</div>
            <div class="current-fee-info">
                <p><strong>Mô tả:</strong> {{ $currentFee->description ?? 'Không có mô tả' }}</p>
                <p><strong>Có hiệu lực từ:</strong> {{ $currentFee->effective_from ? $currentFee->effective_from->format('d/m/Y H:i') : 'Ngay lập tức' }}</p>
                <p><strong>Ngày tạo:</strong> {{ $currentFee->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
    @else
    <div class="platform-fee-no-active">
        <i class="fas fa-exclamation-circle"></i>
        <p>Chưa có phí sàn nào đang được áp dụng</p>
    </div>
    @endif

    <!-- Add New Fee Form -->
    <div class="platform-fee-card">
        <div class="platform-fee-card-header">
            <h3><i class="fas fa-plus-circle"></i> Thêm cài đặt phí sàn mới</h3>
        </div>
        <div class="platform-fee-card-body">
            <form action="{{ route('admin.platform-fee.store') }}" method="POST" class="platform-fee-form">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="fee_percentage">Phí sàn (%)<span class="required">*</span></label>
                        <input type="number" 
                               id="fee_percentage" 
                               name="fee_percentage" 
                               class="form-control" 
                               step="0.01" 
                               min="0" 
                               max="100" 
                               required
                               placeholder="Ví dụ: 5.00">
                        @error('fee_percentage')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="effective_from">Có hiệu lực từ</label>
                        <input type="datetime-local" 
                               id="effective_from" 
                               name="effective_from" 
                               class="form-control">
                        <small class="form-text">Để trống nếu áp dụng ngay lập tức</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control" 
                              rows="3"
                              placeholder="Mô tả về cài đặt phí sàn này..."></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Áp dụng ngay</span>
                    </label>
                    <small class="form-text">Nếu chọn, các cài đặt cũ sẽ bị vô hiệu hóa</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu cài đặt
                </button>
            </form>
        </div>
    </div>

    <!-- Settings History -->
    <div class="platform-fee-card">
        <div class="platform-fee-card-header">
            <h3><i class="fas fa-history"></i> Lịch sử cài đặt phí sàn</h3>
        </div>
        <div class="platform-fee-card-body">
            @if($settings->count() > 0)
                <div class="platform-fee-table-wrapper">
                    <table class="platform-fee-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Phí (%)</th>
                                <th>Mô tả</th>
                                <th>Có hiệu lực từ</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                            <tr>
                                <td>{{ $setting->id }}</td>
                                <td class="fee-percentage">{{ $setting->fee_percentage }}%</td>
                                <td>{{ Str::limit($setting->description, 50) ?? 'N/A' }}</td>
                                <td>{{ $setting->effective_from ? $setting->effective_from->format('d/m/Y H:i') : 'Ngay lập tức' }}</td>
                                <td>
                                    <span class="status-badge {{ $setting->is_active ? 'active' : 'inactive' }}">
                                        {{ $setting->is_active ? 'Đang áp dụng' : 'Không áp dụng' }}
                                    </span>
                                </td>
                                <td>{{ $setting->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" 
                                                class="platform-fee-btn-edit"
                                                onclick="editFee({{ $setting->id }}, {{ $setting->fee_percentage }}, '{{ $setting->description }}', '{{ $setting->effective_from }}', {{ $setting->is_active ? 'true' : 'false' }})"
                                                title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form action="{{ route('admin.platform-fee.destroy', $setting) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Bạn có chắc muốn xóa cài đặt này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="platform-fee-btn-delete" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="platform-fee-pagination">
                    {{ $settings->links() }}
                </div>
            @else
                <div class="platform-fee-empty">
                    <i class="fas fa-inbox"></i>
                    <p>Chưa có cài đặt phí sàn nào</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Chỉnh sửa cài đặt phí sàn</h3>
            <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_fee_percentage">Phí sàn (%)<span class="required">*</span></label>
                    <input type="number" 
                           id="edit_fee_percentage" 
                           name="fee_percentage" 
                           class="form-control" 
                           step="0.01" 
                           min="0" 
                           max="100" 
                           required>
                </div>

                <div class="form-group">
                    <label for="edit_effective_from">Có hiệu lực từ</label>
                    <input type="datetime-local" 
                           id="edit_effective_from" 
                           name="effective_from" 
                           class="form-control">
                </div>

                <div class="form-group">
                    <label for="edit_description">Mô tả</label>
                    <textarea id="edit_description" 
                              name="description" 
                              class="form-control" 
                              rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <span>Áp dụng ngay</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editFee(id, percentage, description, effectiveFrom, isActive) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editForm');
    
    form.action = `/admin/platform-fee/${id}`;
    document.getElementById('edit_fee_percentage').value = percentage;
    document.getElementById('edit_description').value = description || '';
    document.getElementById('edit_is_active').checked = isActive;
    
    if (effectiveFrom && effectiveFrom !== 'null') {
        // Convert to datetime-local format
        const date = new Date(effectiveFrom);
        const formatted = date.toISOString().slice(0, 16);
        document.getElementById('edit_effective_from').value = formatted;
    } else {
        document.getElementById('edit_effective_from').value = '';
    }
    
    modal.style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}
</script>
@endsection

