@extends('components.dashboard.layout')

@section('title', 'Quản lý Phí sàn')

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
                                                class="btn btn-sm btn-edit"
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
                                            <button type="submit" class="btn btn-sm btn-delete" title="Xóa">
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

<style>
/* Platform Fee Styles */
.platform-fee-management-content {
    padding: 20px;
}

.platform-fee-management-header {
    margin-bottom: 30px;
}

.platform-fee-page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.platform-fee-page-description {
    color: #718096;
    font-size: 14px;
}

/* Current Fee Card */
.platform-fee-current-card {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    color: white;
    position: relative;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
}

.current-fee-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
}

.current-fee-percentage {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 15px;
}

.current-fee-info p {
    margin: 8px 0;
    font-size: 14px;
    opacity: 0.95;
}

.platform-fee-no-active {
    background: #fef3c7;
    border: 2px dashed #f59e0b;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    color: #92400e;
    margin-bottom: 30px;
}

.platform-fee-no-active i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
}

/* Card */
.platform-fee-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    overflow: hidden;
}

.platform-fee-card-header {
    background: #f8fafc;
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.platform-fee-card-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 10px;
}

.platform-fee-card-body {
    padding: 30px;
}

/* Form */
.platform-fee-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.required {
    color: #ef4444;
    margin-left: 4px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-text {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-top: 6px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.error-message {
    color: #ef4444;
    font-size: 12px;
    margin-top: 6px;
    display: block;
}

/* Buttons */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn-secondary:hover {
    background: #d1d5db;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-edit {
    background: #f59e0b;
    color: white;
}

.btn-edit:hover {
    background: #d97706;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
}

/* Table */
.platform-fee-table-wrapper {
    overflow-x: auto;
}

.platform-fee-table {
    width: 100%;
    border-collapse: collapse;
}

.platform-fee-table thead {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.platform-fee-table thead th {
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: white;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.platform-fee-table tbody tr {
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.2s;
}

.platform-fee-table tbody tr:hover {
    background: #f8fafc;
}

.platform-fee-table tbody td {
    padding: 16px;
    font-size: 14px;
    color: #4b5563;
}

.fee-percentage {
    font-weight: 700;
    color: #3b82f6;
    font-size: 16px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.platform-fee-empty {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}

.platform-fee-empty i {
    font-size: 64px;
    margin-bottom: 15px;
    display: block;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    margin: 5% auto;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 20px 30px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
    color: #1a202c;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #9ca3af;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.modal-body {
    padding: 30px;
}

.modal-footer {
    padding: 20px 30px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

/* Pagination */
.platform-fee-pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
    .platform-fee-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .current-fee-percentage {
        font-size: 36px;
    }
}
</style>
@endsection

