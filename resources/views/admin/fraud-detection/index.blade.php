@extends('components.dashboard.layout')

@section('title', 'Bảng điều khiển phát hiện gian lận')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/fraud-detection.css') }}">
@endpush

@section('content')
<div class="fraud-detection-container">
    <!-- Page Header -->
    <div class="fraud-header">
        <div class="fraud-header-left">
            <h1>🛡️ Bảng điều khiển phát hiện gian lận</h1>
            <p>Giám sát và phát hiện gian lận trong hệ thống affiliate</p>
        </div>
        <div class="fraud-header-right">
            <a href="{{ route('admin.fraud-detection.export', ['days' => $days]) }}" class="fraud-btn fraud-btn-success">
                <i class="fas fa-download"></i>
                <span>Xuất CSV</span>
            </a>
            <button type="button" class="fraud-btn fraud-btn-warning" data-bs-toggle="modal" data-bs-target="#clearCacheModal">
                <i class="fas fa-sync"></i>
                <span>Xóa Cache</span>
            </button>
        </div>
    </div>

    <!-- Time Filter -->
    <div class="fraud-filter-card">
        <form method="GET" action="{{ route('admin.fraud-detection.index') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="days">Thời gian</label>
                    <select name="days" id="days" class="form-select" onchange="this.form.submit()">
                        <option value="1" {{ $days == 1 ? 'selected' : '' }}>24 giờ qua</option>
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 ngày qua</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 ngày qua</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 ngày qua</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="fraud-stats-grid">
        <div class="fraud-stat-card danger">
            <div class="fraud-stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="fraud-stat-content">
                <h3>{{ number_format($stats['total_fraud_attempts']) }}</h3>
                <p>Tổng số lần gian lận</p>
            </div>
        </div>

        <div class="fraud-stat-card warning">
            <div class="fraud-stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="fraud-stat-content">
                <h3>{{ number_format($stats['average_risk_score'], 1) }}</h3>
                <p>Điểm rủi ro trung bình</p>
            </div>
        </div>

        <div class="fraud-stat-card info">
            <div class="fraud-stat-icon">
                <i class="fas fa-network-wired"></i>
            </div>
            <div class="fraud-stat-content">
                <h3>{{ count($stats['top_fraud_ips']) }}</h3>
                <p>IP gian lận duy nhất</p>
            </div>
        </div>

        <div class="fraud-stat-card secondary">
            <div class="fraud-stat-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="fraud-stat-content">
                <h3>{{ count($blockedIps) }}</h3>
                <p>IP bị chặn</p>
            </div>
        </div>
    </div>

    <!-- Fraud Trend Chart -->
    <div class="fraud-chart-card">
        <h5>📊 Xu hướng gian lận</h5>
        <canvas id="fraudTrendChart" height="80"></canvas>
    </div>

    <!-- Top Fraud IPs & Publishers -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="fraud-table-card">
                <h5>🌐 IP gian lận nhiều nhất</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Địa chỉ IP</th>
                                <th>Số lần</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['top_fraud_ips'] as $ip)
                            <tr>
                                <td><code>{{ $ip->ip_address }}</code></td>
                                <td><span class="badge bg-danger">{{ $ip->attempts }}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="blockIp('{{ $ip->ip_address }}')">
                                        <i class="fas fa-ban"></i> Chặn
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="fraud-table-card">
                <h5>👤 Publisher có gian lận</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID Publisher</th>
                                <th>Số lần</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['fraud_by_publisher'] as $publisher)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $publisher->publisher_id) }}">
                                        #{{ $publisher->publisher_id }}
                                    </a>
                                </td>
                                <td><span class="badge bg-warning">{{ $publisher->attempts }}</span></td>
                                <td>
                                    <a href="{{ route('admin.users.show', $publisher->publisher_id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Fraud Attempts -->
    <div class="fraud-table-card">
        <h5>🚨 Gian lận gần đây</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Publisher</th>
                        <th>Địa chỉ IP</th>
                        <th>Điểm rủi ro</th>
                        <th>Lý do</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentFraudAttempts as $attempt)
                    <tr>
                        <td>
                            <small>{{ \Carbon\Carbon::parse($attempt->detected_at)->format('Y-m-d H:i:s') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $attempt->publisher_id) }}">
                                {{ $attempt->publisher_name }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $attempt->publisher_email }}</small>
                        </td>
                        <td><code>{{ $attempt->ip_address }}</code></td>
                        <td>
                            @php
                                $scoreClass = $attempt->risk_score >= 100 ? 'danger' : ($attempt->risk_score >= 50 ? 'warning' : 'info');
                            @endphp
                            <span class="badge bg-{{ $scoreClass }}">{{ $attempt->risk_score }}</span>
                        </td>
                        <td>
                            <small>{{ Str::limit(is_string($attempt->reasons) ? $attempt->reasons : json_encode($attempt->reasons), 50) }}</small>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" onclick="showFraudDetail({{ $attempt->id }})">
                                <i class="fas fa-info-circle"></i> Chi tiết
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>Không có lần gian lận nào trong thời gian này</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div class="modal fade" id="blockIpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.fraud-detection.block-ip') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Chặn địa chỉ IP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ip_address" class="form-label">Địa chỉ IP</label>
                        <input type="text" class="form-control" id="ip_address" name="ip_address" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Lý do</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Chặn IP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clear Cache Modal -->
<div class="modal fade" id="clearCacheModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.fraud-detection.clear-cache') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Xóa Cache</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có muốn xóa cache cho IP cụ thể hay toàn bộ cache?</p>
                    <div class="mb-3">
                        <label for="cache_ip_address" class="form-label">Địa chỉ IP (để trống để xóa toàn bộ)</label>
                        <input type="text" class="form-control" id="cache_ip_address" name="ip_address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Xóa Cache</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fraud Detail Modal -->
<div class="modal fade" id="fraudDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Chi tiết gian lận</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="fraudDetailContent" style="font-size: 1.05rem;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Fraud Trend Chart
const fraudTrendData = @json($fraudTrend);
const ctx = document.getElementById('fraudTrendChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: fraudTrendData.map(item => item.date),
        datasets: [
            {
                label: 'Lần gian lận',
                data: fraudTrendData.map(item => item.count),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(239, 68, 68)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Điểm rủi ro TB',
                data: fraudTrendData.map(item => item.avg_risk_score),
                borderColor: 'rgb(245, 158, 11)',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(245, 158, 11)',
                tension: 0.3,
                fill: true,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        animation: {
            duration: 0
        },
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 15,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                borderColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 1,
                titleFont: {
                    size: 13,
                    weight: '600'
                },
                bodyFont: {
                    size: 12
                },
                displayColors: true,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += context.parsed.y.toFixed(1);
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 11
                    },
                    color: '#6b7280'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Lần gian lận',
                    font: {
                        size: 12,
                        weight: '600'
                    },
                    color: '#374151'
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    font: {
                        size: 11
                    },
                    color: '#6b7280'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Điểm rủi ro',
                    font: {
                        size: 12,
                        weight: '600'
                    },
                    color: '#374151'
                },
                grid: {
                    drawOnChartArea: false,
                },
                ticks: {
                    font: {
                        size: 11
                    },
                    color: '#6b7280'
                }
            }
        }
    }
});

function blockIp(ipAddress) {
    document.getElementById('ip_address').value = ipAddress;
    new bootstrap.Modal(document.getElementById('blockIpModal')).show();
}

// Show Fraud Detail in Modal
function showFraudDetail(fraudId) {
    const modal = new bootstrap.Modal(document.getElementById('fraudDetailModal'));
    const contentDiv = document.getElementById('fraudDetailContent');
    
    // Show loading spinner
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch fraud detail via AJAX
    fetch(`/admin/fraud-detection/${fraudId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const fraud = data.fraud;
            const reasons = Array.isArray(fraud.reasons) ? fraud.reasons : JSON.parse(fraud.reasons || '[]');
            
            let reasonsHtml = '';
            if (Array.isArray(reasons) && reasons.length > 0) {
                reasonsHtml = reasons.map(reason => `
                    <li class="mb-3 fs-5">
                        <i class="fas fa-exclamation-circle text-danger me-2"></i>
                        <strong>${reason}</strong>
                    </li>
                `).join('');
            } else {
                reasonsHtml = '<li class="text-muted fs-5">Không có lý do cụ thể</li>';
            }
            
            // Get risk score class
            let scoreClass = 'info';
            if (fraud.risk_score >= 100) scoreClass = 'danger';
            else if (fraud.risk_score >= 50) scoreClass = 'warning';
            
            contentDiv.innerHTML = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="border-start border-primary border-4 ps-3">
                            <h5 class="mb-2"><i class="fas fa-user text-primary"></i> Publisher</h5>
                            <p class="mb-1 fs-5"><strong>${fraud.publisher_name || 'N/A'}</strong></p>
                            <p class="text-muted mb-0">${fraud.publisher_email || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-start border-info border-4 ps-3">
                            <h5 class="mb-2"><i class="fas fa-clock text-info"></i> Thời gian phát hiện</h5>
                            <p class="mb-0 fs-5">${new Date(fraud.detected_at).toLocaleString('vi-VN')}</p>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="border-start border-warning border-4 ps-3">
                            <h5 class="mb-2"><i class="fas fa-network-wired text-warning"></i> Địa chỉ IP</h5>
                            <p class="mb-0"><code class="fs-5 bg-light px-3 py-2 rounded">${fraud.ip_address}</code></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-start border-danger border-4 ps-3">
                            <h5 class="mb-2"><i class="fas fa-chart-line text-danger"></i> Điểm rủi ro</h5>
                            <p class="mb-0">
                                <span class="badge bg-${scoreClass}" style="font-size: 1.5rem; padding: 0.5rem 1rem;">${fraud.risk_score}</span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-laptop text-secondary"></i> User Agent</h5>
                    <p class="mb-0 bg-light p-3 rounded">${fraud.user_agent || 'N/A'}</p>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-link text-secondary"></i> URL</h5>
                    <p class="mb-0 text-break bg-light p-3 rounded">${fraud.url || 'N/A'}</p>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-globe text-secondary"></i> Referer</h5>
                    <p class="mb-0 text-break bg-light p-3 rounded">${fraud.referer || 'N/A'}</p>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-list text-danger"></i> Lý do phát hiện gian lận</h5>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <ul class="list-unstyled mb-0">
                            ${reasonsHtml}
                        </ul>
                    </div>
                </div>
                
                ${fraud.metadata ? `
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-info-circle text-info"></i> Metadata</h5>
                    <pre class="bg-light p-3 rounded mb-0"><code>${JSON.stringify(JSON.parse(fraud.metadata), null, 2)}</code></pre>
                </div>
                ` : ''}
                
                <hr class="my-4">
                
                <div class="d-flex gap-3 justify-content-center">
                    <button class="btn btn-danger btn-lg px-4" onclick="blockIpFromDetail('${fraud.ip_address}')">
                        <i class="fas fa-ban me-2"></i>Chặn IP này
                    </button>
                    <a href="/admin/users/${fraud.publisher_id}" class="btn btn-info btn-lg px-4" target="_blank">
                        <i class="fas fa-user me-2"></i>Xem Publisher
                    </a>
                </div>
            `;
        } else {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message || 'Không thể tải chi tiết'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Đã xảy ra lỗi khi tải dữ liệu
            </div>
        `;
    });
}

function blockIpFromDetail(ipAddress) {
    // Close fraud detail modal
    bootstrap.Modal.getInstance(document.getElementById('fraudDetailModal')).hide();
    
    // Show block IP modal
    setTimeout(() => {
        blockIp(ipAddress);
    }, 300);
}
</script>
@endpush

