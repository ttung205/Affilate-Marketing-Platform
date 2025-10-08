@extends('publisher.layouts.app')

@section('title', 'Bảng Xếp Hạng Publisher')

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
    <li class="breadcrumb-item">
        <a href="{{ route('publisher.ranking.index') }}" class="breadcrumb-link">
            <span>Hệ thống hạng</span>
        </a>
    </li>
    <li class="breadcrumb-item">
        <i class="fas fa-chevron-right breadcrumb-arrow"></i>
    </li>
    <li class="breadcrumb-item active">
        <span>Bảng xếp hạng</span>
    </li>
@endsection

@section('content')
    <div class="publisher-leaderboard-page">
        <div class="page-header">
            <h1> Bảng Xếp Hạng Publisher</h1>
            <p>Xem thứ hạng của các publisher trong hệ thống</p>
        </div>

        @foreach($leaderboard as $tier)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card leaderboard-card" style="border-left: 5px solid {{ $tier['ranking']->color }}">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, {{ $tier['ranking']->color }}20 0%, {{ $tier['ranking']->color }}10 100%);">
                            <div class="d-flex align-items-center">
                                <div class="tier-icon me-3" style="font-size: 2rem;">
                                    {{ $tier['ranking']->icon }}
                                </div>
                                <div>
                                    <h5 class="mb-1" style="color: {{ $tier['ranking']->color }}">
                                        Hạng {{ $tier['ranking']->name }}
                                    </h5>
                                    <small class="text-muted">{{ $tier['publishers']->count() }} publisher</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($tier['publishers']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="15%">Publisher</th>
                                                <th width="15%">Tổng Link</th>
                                                <th width="20%">Tổng Hoa Hồng</th>
                                                <th width="15%">Tỷ lệ Conversion</th>
                                                <th width="15%">Bonus</th>
                                                <th width="15%">Thành tích</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tier['publishers'] as $index => $publisher)
                                                <tr>
                                                    <td>
                                                        @if($index < 3)
                                                            @if($index == 0) 🥇
                                                            @elseif($index == 1) 🥈
                                                            @else 🥉
                                                            @endif
                                                        @else
                                                            <span class="rank-number">{{ $index + 1 }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar me-2">
                                                                @if($publisher->avatar)
                                                                    <img src="{{ $publisher->avatar }}" alt="{{ $publisher->name }}"
                                                                        class="rounded-circle" width="32" height="32">
                                                                @else
                                                                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                                                        style="width: 32px; height: 32px; background-color: {{ $tier['ranking']->color }}20; color: {{ $tier['ranking']->color }}">
                                                                        {{ substr($publisher->name, 0, 1) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <div class="publisher-name">{{ $publisher->name }}</div>
                                                                <small class="text-muted">ID: {{ $publisher->id }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-primary">{{ $publisher->affiliateLinks()->count() }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="commission-amount">
                                                            {{ number_format($publisher->getCombinedCommissionAttribute(), 0, ',', '.') }}
                                                            VND
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="conversion-rate">
                                                            {{ $publisher->getConversionRateAttribute() }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($tier['ranking']->bonus_percentage > 0)
                                                            <span class="badge bg-success">+{{ $tier['ranking']->bonus_percentage }}%</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="achievement-badges">
                                                            @if($publisher->affiliateLinks()->count() >= 50)
                                                                <span class="badge bg-warning" title="50+ Links">📎</span>
                                                            @endif
                                                            @if($publisher->getCombinedCommissionAttribute() >= 10000000)
                                                                <span class="badge bg-success" title="10M+ VND">💰</span>
                                                            @endif
                                                            @if($publisher->getConversionRateAttribute() >= 5)
                                                                <span class="badge bg-info" title="5%+ Conversion">🎯</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Chưa có publisher nào đạt hạng {{ $tier['ranking']->name }}</h6>
                                        <small class="text-muted">Hãy là người đầu tiên!</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Statistics -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">📈 Thống kê tổng quan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($leaderboard as $tier)
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="stat-card">
                                        <div class="stat-icon" style="color: {{ $tier['ranking']->color }}">
                                            {{ $tier['ranking']->icon }}
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ $tier['publishers']->count() }}</div>
                                            <div class="stat-label">Hạng {{ $tier['ranking']->name }}</div>
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