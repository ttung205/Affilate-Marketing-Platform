@if(session('success') || session('error') || session('warning') || session('info'))
    <div class="alert-container" id="alertContainer">
        @if(session('success'))
            <div class="alert alert-success" data-alert="success">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Thành công!</div>
                    <div class="alert-message">{{ session('success') }}</div>
                </div>
                <button class="alert-close" onclick="closeAlert(this)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress"></div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error" data-alert="error">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Lỗi!</div>
                    <div class="alert-message">{{ session('error') }}</div>
                </div>
                <button class="alert-close" onclick="closeAlert(this)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress"></div>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning" data-alert="warning">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Cảnh báo!</div>
                    <div class="alert-message">{{ session('warning') }}</div>
                </div>
                <button class="alert-close" onclick="closeAlert(this)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress"></div>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info" data-alert="info">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Thông tin!</div>
                    <div class="alert-message">{{ session('info') }}</div>
                </div>
                <button class="alert-close" onclick="closeAlert(this)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress"></div>
            </div>
        @endif
    </div>
@endif