# Hướng dẫn cài đặt và sử dụng Google 2FA

## ✅ Đã hoàn thành

Hệ thống Google 2FA (Two-Factor Authentication) đã được tích hợp thành công vào ứng dụng Laravel của bạn!

## 📋 Các bước đã thực hiện

1. ✅ Cài đặt package `pragmarx/google2fa-laravel`
2. ✅ Tạo migration thêm các cột 2FA vào bảng users
3. ✅ Cập nhật User model
4. ✅ Tạo TwoFactorController
5. ✅ Cập nhật AuthController để xử lý 2FA khi login
6. ✅ Tạo các views cho 2FA (setup và verify)
7. ✅ Thêm routes cho các chức năng 2FA

## 🚀 Các lệnh cần chạy

Chạy các lệnh sau trong terminal để hoàn tất cài đặt:

```bash
# 1. Publish config file của Google2FA (optional)
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"

# 2. Chạy migration để thêm các cột 2FA vào bảng users
php artisan migrate

# 3. Clear cache
php artisan config:clear
php artisan cache:clear
```

## 🔧 Cấu trúc đã thêm

### Database
- `google2fa_secret` (text, nullable) - Lưu secret key của Google 2FA
- `google2fa_enabled` (boolean, default: false) - Trạng thái bật/tắt 2FA
- `google2fa_enabled_at` (timestamp, nullable) - Thời gian kích hoạt 2FA

### Routes
- `GET /2fa/setup` - Trang thiết lập 2FA (yêu cầu đăng nhập)
- `POST /2fa/enable` - Kích hoạt 2FA (yêu cầu đăng nhập)
- `POST /2fa/disable` - Tắt 2FA (yêu cầu đăng nhập)
- `GET /2fa/verify` - Trang xác thực 2FA khi đăng nhập
- `POST /2fa/verify` - Xử lý xác thực 2FA

### Controllers
- `TwoFactorController` - Xử lý tất cả logic 2FA
- `AuthController` (đã cập nhật) - Kiểm tra 2FA khi login

### Views
- `resources/views/auth/2fa-setup.blade.php` - Trang thiết lập 2FA
- `resources/views/auth/2fa-verify.blade.php` - Trang xác thực 2FA khi đăng nhập

## 📱 Hướng dẫn sử dụng cho User

### Bật Google 2FA:

1. Đăng nhập vào tài khoản
2. Truy cập: `/2fa/setup`
3. Tải ứng dụng Google Authenticator:
   - Android: https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2
   - iOS: https://apps.apple.com/app/google-authenticator/id388497605
4. Quét mã QR hoặc nhập secret key thủ công
5. Nhập mã 6 chữ số từ app để kích hoạt
6. ✅ 2FA đã được bật!

### Đăng nhập với 2FA:

1. Nhập email và mật khẩu như bình thường
2. Hệ thống sẽ chuyển đến trang xác thực 2FA
3. Mở Google Authenticator app
4. Nhập mã 6 chữ số
5. ✅ Đăng nhập thành công!

### Tắt 2FA:

1. Truy cập: `/2fa/setup`
2. Nhập mật khẩu để xác nhận
3. Click "Tắt 2FA"
4. ✅ 2FA đã được tắt!

## 🔗 Thêm link vào Dashboard/Profile

Để user dễ dàng truy cập, bạn có thể thêm link vào header hoặc trang profile:

```php
<a href="{{ route('2fa.setup') }}" class="nav-link">
    <i class="fas fa-shield-alt"></i> Xác thực 2 bước
</a>
```

Hoặc hiển thị trạng thái 2FA:

```php
@if(auth()->user()->google2fa_enabled)
    <span class="badge badge-success">
        <i class="fas fa-check"></i> 2FA Đã bật
    </span>
@else
    <span class="badge badge-warning">
        <i class="fas fa-times"></i> 2FA Chưa bật
    </span>
    <a href="{{ route('2fa.setup') }}" class="btn btn-sm btn-primary">
        Bật ngay
    </a>
@endif
```

## 🎨 Tính năng

- ✅ QR Code tự động generate
- ✅ Hiển thị secret key để nhập thủ công
- ✅ Xác thực mã 6 chữ số
- ✅ Tích hợp hoàn toàn với flow login hiện tại
- ✅ Hỗ trợ bật/tắt 2FA
- ✅ Giao diện đẹp, responsive
- ✅ Tự động submit khi nhập đủ 6 số
- ✅ Bảo mật cao với session handling

## 🔐 Bảo mật

- Secret key được mã hóa trong database
- Session 2FA được xóa sau khi verify
- Yêu cầu mật khẩu khi tắt 2FA
- Logout tự động sau khi xác thực credentials trước khi verify 2FA

## ⚠️ Lưu ý

1. Đảm bảo package `bacon/bacon-qr-code` đã được cài đặt (đi kèm với pragmarx/google2fa-laravel)
2. Thời gian server phải chính xác (quan trọng cho TOTP)
3. User nên lưu secret key hoặc backup codes (có thể thêm tính năng này sau)
4. Nên có recovery method nếu user mất điện thoại (có thể thêm tính năng recovery codes)

## 🚀 Tính năng mở rộng (có thể thêm sau)

- [ ] Recovery codes (mã khôi phục)
- [ ] Backup codes khi bật 2FA
- [ ] SMS 2FA (alternative)
- [ ] Email verification code
- [ ] Trusted devices (không cần verify lại)
- [ ] Admin force enable 2FA cho tất cả users
- [ ] Activity log cho 2FA events

## 📞 Support

Nếu có vấn đề, kiểm tra:
1. Log file: `storage/logs/laravel.log`
2. Database có chạy migration chưa
3. Thời gian server có chính xác không
4. Session driver có hoạt động không

Happy coding! 🎉

