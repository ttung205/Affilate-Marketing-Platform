<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - TTung Affiliate</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/sidebar.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        @include('components.dashboard.sidebar')
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            @include('components.dashboard.header')
            
            <!-- Page Content -->
            <div class="page-content">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Real-time Notifications Script -->
    <script>
        window.userId = {{ Auth::id() }};
    </script>
    <script src="{{ asset('js/notifications/realtime.js') }}"></script>
    @stack('scripts')
</body>
</html>
