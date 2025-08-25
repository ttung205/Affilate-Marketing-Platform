@extends('components.dashboard.layout')

@section('title', 'Tạo Campaign mới')

@section('content')
<div class="campaigns-container">
    <!-- Header Section -->
    <div class="campaigns-header">
        <div class="campaigns-header-left">
            <h1 class="campaigns-title">Tạo Campaign mới</h1>
        </div>
        <div class="campaigns-header-right">
            <a href="{{ route('admin.campaigns.index') }}" class="campaigns-btn campaigns-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Quay lại</span>
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="campaigns-form-card">
        <div class="form-header">
            <h3 class="form-title">Thông tin Campaign</h3>
            <p class="form-subtitle">Điền đầy đủ thông tin để tạo campaign mới</p>
        </div>
        
        <div class="form-body">
            <form method="POST" action="{{ route('admin.campaigns.store') }}" class="campaigns-form">
                @csrf
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Tên Campaign <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-input @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Nhập tên campaign..."
                               required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">Tên campaign sẽ hiển thị cho publishers và được sử dụng để phân biệt các chiến dịch</div>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">
                            Trạng thái <span class="required">*</span>
                        </label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="">Chọn trạng thái</option>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="paused" {{ old('status') == 'paused' ? 'selected' : '' }}>Tạm dừng</option>
                        </select>
                        @error('status')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">Trạng thái ban đầu của campaign</div>
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea class="form-input @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="4" 
                              placeholder="Mô tả chi tiết về campaign...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-help">Mô tả chi tiết về mục tiêu và nội dung của campaign</div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="start_date" class="form-label">
                            Ngày bắt đầu <span class="required">*</span>
                        </label>
                        <input type="date" 
                               class="form-input @error('start_date') is-invalid @enderror" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ old('start_date') }}" 
                               required>
                        @error('start_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">Ngày bắt đầu chạy campaign</div>
                    </div>

                    <div class="form-group">
                        <label for="end_date" class="form-label">
                            Ngày kết thúc <span class="required">*</span>
                        </label>
                        <input type="date" 
                               class="form-input @error('end_date') is-invalid @enderror" 
                               id="end_date" 
                               name="end_date" 
                               value="{{ old('end_date') }}" 
                               required>
                        @error('end_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">Ngày kết thúc campaign</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="budget" class="form-label">
                            Ngân sách (VNĐ) <span class="required">*</span>
                        </label>
                        <input type="number" 
                               class="form-input @error('budget') is-invalid @enderror" 
                               id="budget" 
                               name="budget" 
                               value="{{ old('budget') }}" 
                               min="0" 
                               step="1000" 
                               placeholder="0" 
                               required>
                        @error('budget')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">Tổng ngân sách dành cho campaign (VNĐ)</div>
                    </div>

                    <div class="form-group">
                        <label for="commission_rate" class="form-label">
                            Tỷ lệ hoa hồng (%) <span class="required">*</span>
                        </label>
                        <input type="number" 
                               class="form-input @error('commission_rate') is-invalid @enderror" 
                               id="commission_rate" 
                               name="commission_rate" 
                               value="{{ old('commission_rate', '15.00') }}" 
                               min="0" 
                               max="100" 
                               step="0.01" 
                               placeholder="15.00" 
                               required>
                        @error('commission_rate')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">Tỷ lệ hoa hồng (%) mà publisher sẽ nhận được cho mỗi conversion</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="target_conversions" class="form-label">
                            Mục tiêu Conversions
                        </label>
                        <input type="number" 
                               class="form-input @error('target_conversions') is-invalid @enderror" 
                               id="target_conversions" 
                               name="target_conversions" 
                               value="{{ old('target_conversions') }}" 
                               min="0" 
                               placeholder="0">
                        @error('target_conversions')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">Số lượng conversions mục tiêu (để tham khảo)</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="campaigns-btn campaigns-btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Tạo Campaign</span>
                    </button>
                    <a href="{{ route('admin.campaigns.index') }}" class="campaigns-btn campaigns-btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Hủy bỏ</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum end date to start date
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });
    
    endDateInput.addEventListener('change', function() {
        if (startDateInput.value && this.value < startDateInput.value) {
            alert('Ngày kết thúc không thể sớm hơn ngày bắt đầu!');
            this.value = startDateInput.value;
        }
    });
});
</script>
@endsection
