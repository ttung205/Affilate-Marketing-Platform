<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TTung Affiliate Admin')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Dashboard CSS chung -->
    <link rel="stylesheet" href="{{ asset('css/dashboard/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/header.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        @include('components.dashboard.sidebar')
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            @include('components.dashboard.header')
            
            <!-- Content -->
            @yield('content')
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>