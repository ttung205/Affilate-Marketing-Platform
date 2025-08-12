<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'Email hoặc mật khẩu không đúng.',
    'password' => 'Mật khẩu không đúng.',
    'throttle' => 'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau :seconds giây.',

    /*
    |--------------------------------------------------------------------------
    | Custom Authentication Messages
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom authentication messages for your application.
    | Feel free to modify these messages according to your needs.
    |
    */

    'login' => [
        'title' => 'Đăng nhập',
        'subtitle' => 'Chào mừng trở lại!',
        'description' => 'Đăng nhập để tiếp tục hành trình của bạn',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'remember' => 'Ghi nhớ đăng nhập',
        'forgot' => 'Quên mật khẩu?',
        'submit' => 'Đăng nhập',
        'with_google' => 'Đăng nhập bằng Google',
        'no_account' => 'Chưa có tài khoản?',
        'register' => 'Đăng ký ngay',
        'errors' => [
            'invalid_credentials' => 'Email hoặc mật khẩu không đúng.',
            'too_many_attempts' => 'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau.',
            'account_locked' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
        ],
    ],

    'register' => [
        'title' => 'Đăng ký',
        'subtitle' => 'Tạo tài khoản mới',
        'description' => 'Bắt đầu hành trình affiliate marketing của bạn',
        'name' => 'Họ và tên',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'role' => 'Vai trò',
        'terms' => 'Tôi đồng ý với điều khoản sử dụng và chính sách bảo mật',
        'submit' => 'Tạo tài khoản',
        'with_google' => 'Đăng ký bằng Google',
        'has_account' => 'Đã có tài khoản?',
        'login' => 'Đăng nhập',
        'errors' => [
            'name_required' => 'Vui lòng nhập họ và tên.',
            'email_required' => 'Vui lòng nhập email.',
            'email_invalid' => 'Email không hợp lệ.',
            'email_unique' => 'Email này đã được sử dụng.',
            'password_required' => 'Vui lòng nhập mật khẩu.',
            'password_min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password_confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role_required' => 'Vui lòng chọn vai trò.',
            'terms_required' => 'Bạn phải đồng ý với điều khoản sử dụng.',
        ],
    ],

    'google' => [
        'registration' => [
            'title' => 'Hoàn tất đăng ký',
            'subtitle' => 'Thông tin từ Google đã được điền sẵn, bạn chỉ cần chọn vai trò',
            'info_title' => 'Thông tin từ Google',
            'name' => 'Tên',
            'email' => 'Email',
            'email_note' => 'Email này sẽ được sử dụng làm tài khoản đăng nhập',
            'role' => 'Vai trò',
            'role_placeholder' => 'Chọn vai trò của bạn',
            'roles' => [
                'user' => 'Người dùng thường',
                'publisher' => 'Publisher',
                'shop' => 'Shop',
                'admin' => 'Admin',
            ],
            'submit' => 'Hoàn tất đăng ký',
            'back_to_login' => 'Quay lại đăng nhập',
            'errors' => [
                'session_expired' => 'Phiên đăng ký đã hết hạn. Vui lòng đăng nhập bằng Google lại.',
                'registration_failed' => 'Đăng ký thất bại. Vui lòng thử lại.',
            ],
        ],
        'login' => [
            'failed' => 'Đăng nhập bằng Google thất bại.',
            'user_not_found' => 'Không tìm thấy tài khoản Google.',
            'account_locked' => 'Tài khoản của bạn đã bị khóa.',
        ],
    ],

    'logout' => [
        'title' => 'Đăng xuất',
        'confirm' => 'Bạn có chắc chắn muốn đăng xuất?',
        'success' => 'Đã đăng xuất thành công.',
    ],

    'password' => [
        'reset' => [
            'title' => 'Đặt lại mật khẩu',
            'email' => 'Email',
            'submit' => 'Gửi link đặt lại mật khẩu',
            'sent' => 'Link đặt lại mật khẩu đã được gửi đến email của bạn.',
            'token' => 'Token đặt lại mật khẩu không hợp lệ.',
            'reset' => 'Mật khẩu đã được đặt lại thành công.',
        ],
        'confirm' => [
            'title' => 'Xác nhận mật khẩu',
            'description' => 'Đây là khu vực an toàn của ứng dụng. Vui lòng xác nhận mật khẩu trước khi tiếp tục.',
            'password' => 'Mật khẩu',
            'submit' => 'Xác nhận',
        ],
    ],

    'verification' => [
        'title' => 'Xác thực email',
        'description' => 'Cảm ơn bạn đã đăng ký! Trước khi bắt đầu, bạn có thể xác thực địa chỉ email của mình bằng cách nhấp vào liên kết mà chúng tôi vừa gửi đến email của bạn? Nếu bạn không nhận được email, chúng tôi sẽ gửi lại.',
        'resend' => 'Gửi lại email xác thực',
        'sent' => 'Link xác thực mới đã được gửi.',
        'verified' => 'Email của bạn đã được xác thực thành công.',
    ],

];
