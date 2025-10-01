<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Chatbot CSS -->
    <link rel="stylesheet" href="{{ asset('css/chatbot/chatbot.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>ĐÂY LÀ TRANG DASHBOARD</h1>
    <a href="/logout">Đăng xuất</a>
    
    <!-- Chatbot Widget -->
    @include('chatbot.chatbot')
    
    <!-- Chatbot JS -->
    <script src="{{ asset('js/chatbot/chatbot.js') }}"></script>
</body>
</html>