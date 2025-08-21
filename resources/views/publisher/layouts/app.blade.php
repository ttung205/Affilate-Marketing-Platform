<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TTung Affiliate Publisher')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Dashboard CSS chung -->
    <link rel="stylesheet" href="{{ asset('css/dashboard/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/confirm-popup.css') }}">
    
    <!-- Publisher CSS riêng biệt -->
    <link rel="stylesheet" href="{{ asset('css/publisher/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/publisher/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/publisher/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/publisher/profile.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="dashboard-container">
        @include('publisher.components.sidebar')
        
        <div class="main-content">
            @include('publisher.components.header')
            
            <!-- Alert System -->
            @include('components.alerts')
            
            <main class="content-area">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Alert System JavaScript -->
    <script src="{{ asset('js/components/alerts.js') }}"></script>
    <!-- Confirm Popup JavaScript -->
    <script src="{{ asset('js/components/confirm-popup.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
