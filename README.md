# 🚀 Affiliate Marketing Platform

> **Nền tảng tiếp thị liên kết hoàn chỉnh** được xây dựng bằng Laravel 12, giúp kết nối người bán (Shop) và người tiếp thị (Publisher) để tăng doanh số và chia sẻ lợi nhuận một cách hiệu quả.

## ✨ Tính năng đã được implement

### 👨‍💼 **Hệ thống Admin**
- **Dashboard tổng quan** với thống kê real-time
- **Quản lý người dùng** (Admin, Shop, Publisher)
- **Quản lý sản phẩm** với danh mục và hình ảnh
- **Quản lý chiến dịch** và link affiliate
- **Quản lý danh mục** sản phẩm
- **Quản lý thông báo** và template
- **Quản lý rút tiền** với phê duyệt
- **Báo cáo và phân tích** chi tiết

### 🛒 **Hệ thống Shop**
- **Dashboard** quản lý sản phẩm
- **Quản lý sản phẩm** CRUD đầy đủ
- **Hồ sơ cá nhân** và thiết lập

### 📢 **Hệ thống Publisher**
- **Dashboard** với thống kê chi tiết
- **Quản lý link affiliate** với tracking click
- **Quản lý ví** và thu nhập
- **Quản lý phương thức thanh toán** (ngân hàng)
- **Yêu cầu rút tiền** với phê duyệt
- **Theo dõi hiệu suất** và hoa hồng
- **Hồ sơ cá nhân** và thiết lập

### 🔗 **Hệ thống Affiliate Tracking**
- **Tạo link affiliate** thông minh
- **Theo dõi click** real-time
- **Xử lý conversion** và tính hoa hồng
- **Hệ thống transaction** chi tiết
- **Commission management** tự động

### 🔐 **Xác thực và Bảo mật**
- **Đăng nhập/Đăng ký** với Google OAuth
- **Quản lý mật khẩu** và reset
- **Phân quyền** theo vai trò (Admin/Shop/Publisher)
- **Session management** an toàn
- **Validation** đầy đủ cho tất cả form

## 🛠️ Công nghệ sử dụng

### **Backend**
- **Laravel 12** - Framework PHP hiện đại
- **MySQL** - Cơ sở dữ liệu chính
- **Eloquent ORM** - Query builder mạnh mẽ
- **Queue Jobs** - Xử lý tác vụ nền
- **Artisan CLI** - Công cụ phát triển

### **Frontend**
- **Blade Templates** - Template engine
- **Bootstrap CSS** - Responsive design
- **JavaScript ES6+** - Tương tác động
- **FontAwesome** - Icon library
- **Real-time notifications** - Cập nhật tức thời

### **Database Models**
- **User** - Quản lý người dùng đa vai trò
- **Product** - Sản phẩm với danh mục
- **Category** - Danh mục sản phẩm
- **Campaign** - Chiến dịch marketing
- **AffiliateLink** - Link tiếp thị liên kết
- **Click** - Theo dõi lượt click
- **Conversion** - Chuyển đổi và hoa hồng
- **Transaction** - Giao dịch tài chính
- **PublisherWallet** - Ví của publisher
- **PaymentMethod** - Phương thức thanh toán
- **Withdrawal** - Yêu cầu rút tiền
- **Notification** - Hệ thống thông báo

### **Infrastructure**
- **Composer** - Quản lý dependencies PHP
- **Git** - Version control
- **DDEV** - Development environment

## 🏗️ Kiến trúc hệ thống

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Quản lý admin
│   │   ├── Publisher/      # Quản lý publisher
│   │   ├── Shop/          # Quản lý shop
│   │   └── Auth/          # Xác thực
│   ├── Models/            # Database models
│   ├── Services/          # Business logic
│   └── Notifications/     # Hệ thống thông báo
├── database/
│   ├── migrations/        # Database schema
│   └── seeders/          # Dữ liệu mẫu
├── public/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── images/           # Assets
├── resources/
│   └── views/            # Blade templates
└── routes/               # Route definitions
```

## 🚀 Cài đặt và chạy dự án

### **Yêu cầu hệ thống**
- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Node.js >= 16.0
- DDEV (recommended)

### **Bước 1: Clone dự án**
```bash
git clone https://github.com/ttung125/ttung-laravel.git
cd ttung-laravel
```

### **Bước 2: Cài đặt dependencies**
```bash
composer install
npm install
```

### **Bước 3: Cấu hình môi trường**
```bash
cp .env.example .env
php artisan key:generate
```

### **Bước 4: Cấu hình database**
```bash
# Chỉnh sửa .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ttung_affiliate
DB_USERNAME=root
DB_PASSWORD=
```

### **Bước 5: Chạy migration và seeder**
```bash
php artisan migrate
php artisan db:seed
```

### **Bước 6: Khởi chạy dự án**
```bash
# Sử dụng DDEV (recommended)
ddev start
ddev exec php artisan serve --host=0.0.0.0

# Hoặc sử dụng PHP built-in
php artisan serve
npm run dev
```

Truy cập: `http://localhost:8000` hoặc `https://ttung-laravel.ddev.site`

## 🔐 Tài khoản test

### **Admin Account**
- Email: `admin@example.com`
- Password: `password`

### **Shop Account**
- Email: `shop@example.com`
- Password: `password`

### **Publisher Account**
- Email: `publisher@example.com`
- Password: `password`

## 📊 Tính năng đã hoàn thành

### ✅ **Core Features**
- [x] Hệ thống đăng ký/đăng nhập với Google OAuth
- [x] Phân quyền đa vai trò (Admin/Shop/Publisher)
- [x] Quản lý sản phẩm và danh mục
- [x] Tạo và quản lý link affiliate
- [x] Theo dõi click và conversion
- [x] Tính toán hoa hồng tự động
- [x] Hệ thống ví và giao dịch
- [x] Yêu cầu và phê duyệt rút tiền
- [x] Hệ thống thông báo real-time
- [x] Dashboard với thống kê chi tiết

### ✅ **User Interface**
- [x] Responsive design cho tất cả thiết bị
- [x] Giao diện admin hiện đại
- [x] Giao diện publisher thân thiện
- [x] Giao diện shop trực quan
- [x] Real-time notifications

### ✅ **Business Logic**
- [x] Commission calculation (CPC + CPA)
- [x] Wallet management với hold period
- [x] Payment method validation
- [x] Withdrawal approval workflow
- [x] Transaction history tracking

## 🔄 Workflow hoạt động

### **1. Tạo Link Affiliate**
1. Publisher đăng nhập và tạo link affiliate
2. Hệ thống tạo unique shortcode
3. Publisher chia sẻ link để thu hút traffic

### **2. Theo dõi Click**
1. User click vào link affiliate
2. Hệ thống ghi nhận click với tracking ID
3. Publisher nhận commission CPC nếu có

### **3. Conversion & Commission**
1. User mua hàng thành công
2. Hệ thống ghi nhận conversion
3. Publisher nhận commission CPA
4. Cộng tiền vào ví với hold period

### **4. Rút tiền**
1. Publisher yêu cầu rút tiền
2. Admin phê duyệt yêu cầu
3. Hệ thống xử lý thanh toán
4. Cập nhật trạng thái và thông báo

## 🤝 Đóng góp

Chúng tôi rất hoan nghênh mọi đóng góp! Hãy:

1. Fork dự án
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit thay đổi (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Mở Pull Request

## 📄 License

Dự án này được phân phối dưới giấy phép MIT. Xem file `LICENSE` để biết thêm chi tiết.

## 📞 Liên hệ

- **Email**: tung18102k5@gmail.com
- **Website**: https://ttung.com
- **GitHub**: [@ttung-dev](https://github.com/ttung125)

---

<div align="center">

**⭐ Nếu dự án này hữu ích, hãy cho chúng tôi một ngôi sao! ⭐**

**Built with ❤️ using Laravel 12**

</div>
