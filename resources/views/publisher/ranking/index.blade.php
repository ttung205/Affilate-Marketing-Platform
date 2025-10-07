@extends('publisher.layouts.app')

@section('title', 'Hạng Publisher')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('publisher.dashboard') }}" class="breadcrumb-link">
            <i class="fas fa-home"></i>
            <span>Publisher</span>
        </a>
    </li>
    <li class="breadcrumb-item">
        <i class="fas fa-chevron-right breadcrumb-arrow"></i>
    </li>
    <li class="breadcrumb-item active">
        <span>Hệ thống hạng</span>
    </li>
@endsection

@section('content')
    <div class="publisher-ranking-page">
        <div class="page-header">
            <h1>Hệ thống hạng Publisher</h1>
            <p>Xem hạng hiện tại và tiến độ lên hạng tiếp theo</p>
        </div>

        <!-- Current Ranking Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card ranking-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="ranking-icon me-3" style="font-size: 3rem;">
                                        {{ $currentRanking->icon ?? '⭐' }}
                                    </div>
                                    <div>
                                        <h3 class="mb-1" style="color: {{ $currentRanking->color ?? '#6B7280' }}">
                                            {{ $currentRanking->name ?? 'Chưa có hạng' }}
                                        </h3>
                                        <p class="text-muted mb-0">
                                            {{ $currentRanking->description ?? 'Bắt đầu tạo link và kiếm hoa hồng để có hạng!' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="ranking-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Tổng Link:</span>
                                        <span class="stat-value">{{ $publisher->affiliateLinks()->count() }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Tổng Hoa Hồng:</span>
                                        <span
                                            class="stat-value">{{ number_format($publisher->getCombinedCommissionAttribute(), 0, ',', '.') }}
                                            VND</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress to Next Ranking -->
        @if(!$progress['is_max_level'])
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                🎯 Tiến độ lên hạng {{ $progress['next_ranking']->name }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="progress-item">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>📎 Số Link</span>
                                            <span>{{ $progress['stats']['total_links'] }}/{{ $progress['next_ranking']->min_links }}</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: {{ $progress['progress']['links_progress'] }}%"
                                                aria-valuenow="{{ $progress['progress']['links_progress'] }}" aria-valuemin="0"
                                                aria-valuemax="100">
                                                {{ $progress['progress']['links_progress'] }}%
                                            </div>
                                        </div>
                                        @if($progress['progress']['links_needed'] > 0)
                                            <small class="text-muted">Cần thêm {{ $progress['progress']['links_needed'] }}
                                                link</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="progress-item">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>💰 Hoa Hồng</span>
                                            <span>{{ number_format($progress['stats']['total_commission'], 0, ',', '.') }}/{{ number_format($progress['next_ranking']->min_commission, 0, ',', '.') }}
                                                VND</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $progress['progress']['commission_progress'] }}%"
                                                aria-valuenow="{{ $progress['progress']['commission_progress'] }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                {{ $progress['progress']['commission_progress'] }}%
                                            </div>
                                        </div>
                                        @if($progress['progress']['commission_needed'] > 0)
                                            <small class="text-muted">Cần thêm
                                                {{ number_format($progress['progress']['commission_needed'], 0, ',', '.') }}
                                                VND</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- All Rankings -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">📊 Tất cả hạng Publisher</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($allRankings as $ranking)
                                <div class="col-md-6 col-lg-3 mb-4">
                                    <div class="ranking-tier-card {{ $currentRanking && $currentRanking->id == $ranking->id ? 'current-tier' : '' }}"
                                        style="border-color: {{ $ranking->color }}">
                                        <div class="tier-header" style="background-color: {{ $ranking->color }}20">
                                            <div class="tier-icon">{{ $ranking->icon }}</div>
                                            <h6 class="tier-name">{{ $ranking->name }}</h6>
                                        </div>
                                        <div class="tier-body">
                                            <div class="tier-requirements">
                                                <div class="requirement">
                                                    <i class="fas fa-link"></i>
                                                    <span>{{ $ranking->min_links }} links</span>
                                                </div>
                                                <div class="requirement">
                                                    <i class="fas fa-coins"></i>
                                                    <span>{{ number_format($ranking->min_commission, 0, ',', '.') }} VND</span>
                                                </div>
                                            </div>
                                            @if($ranking->bonus_percentage > 0)
                                                <div class="tier-bonus">
                                                    <span class="badge bg-success">+{{ $ranking->bonus_percentage }}% bonus</span>
                                                </div>
                                            @endif
                                            <div class="tier-benefits">
                                                <small>{{ $ranking->benefits }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/publisher/ranking.css') }}">
@endpush

@push('scripts')
    <script>
        function updateRanking() {
            fetch('{{ route("publisher.ranking.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.data.is_upgrade) {
                            alert('🎉 ' + data.message);
                        } else {
                            alert('✅ ' + data.message);
                        }
                        location.reload();
                    } else {
                        alert('⚠️ ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Có lỗi xảy ra khi cập nhật hạng');
                });
        }
    </script>
@endpush