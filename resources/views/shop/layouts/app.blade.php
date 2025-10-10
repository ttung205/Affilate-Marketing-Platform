<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Affiliate Marketing Shop')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Dashboard CSS chung -->
    <link rel="stylesheet" href="{{ asset('css/dashboard/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/breadcrumb-override.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/confirm-popup.css') }}">
    
    <link rel="stylesheet" href="{{ asset('css/shop/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shop/products.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shop/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shop/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifications/modal.css') }}">
    <!-- Chatbot CSS -->
    <link rel="stylesheet" href="{{ asset('css/chatbot/chatbot.css') }}">

    <link rel="stylesheet" href="{{ asset('css/shop/vouchers.css') }}">
    
    @stack('styles')
</head>

<body>
    <div class="dashboard-container">
        @include('shop.components.sidebar')
        
        <div class="main-content">
            @include('shop.components.header')
            
            <!-- Alert System -->
            @include('components.alerts')
            
            <main class="content-area">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Chatbot Widget -->
    @include('chatbot.chatbot')
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Alert System JavaScript -->
    <script src="{{ asset('js/components/alerts.js') }}"></script>
    <!-- Confirm Popup JavaScript -->
    <script src="{{ asset('js/components/confirm-popup.js') }}"></script>
    
    <!-- Real-time Notifications Script -->
    <script>
        window.userId = {{ Auth::id() }};
    </script>
    <script src="{{ asset('js/notifications/realtime.js') }}"></script>
    <!-- Chatbot JS -->
    <script src="{{ asset('js/chatbot/chatbot.js') }}"></script>
    
    @stack('scripts')
</body>

</html>
