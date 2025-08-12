# 🚀 TTung Affiliate Marketing Platform

> **Nền tảng tiếp thị liên kết hàng đầu** được xây dựng bằng Laravel 10, giúp kết nối người bán (Shop) và người tiếp thị (Publisher) để tăng doanh số và chia sẻ lợi nhuận một cách hiệu quả.

## ✨ Tính năng nổi bật

### �� **Quản lý Shop**
- Dashboard quản lý sản phẩm và đơn hàng
- Theo dõi doanh thu và hiệu suất bán hàng
- Quản lý chiến dịch marketing
- Phân tích dữ liệu khách hàng

### 📢 **Quản lý Publisher**
- Hệ thống đăng ký và xác thực Publisher
- Theo dõi hiệu suất tiếp thị
- Quản lý hoa hồng và thanh toán
- Công cụ tạo link affiliate

### ��‍�� **Quản lý Admin**
- Dashboard tổng quan hệ thống
- Quản lý người dùng và phân quyền
- Giám sát giao dịch và báo cáo
- Cài đặt hệ thống và bảo mật

## ��️ Công nghệ sử dụng

### **Backend**
- **Laravel 10** - Framework PHP hiện đại
- **MySQL** - Cơ sở dữ liệu chính
- **Redis** - Cache và session
- **Queue Jobs** - Xử lý tác vụ nền

### **Frontend**
- **Blade Templates** - Template engine
- **CSS3 + JavaScript** - Giao diện responsive
- **FontAwesome** - Icon library
- **Google OAuth** - Đăng nhập bằng Google

### **Infrastructure**
- **Composer** - Quản lý dependencies
- **Artisan CLI** - Công cụ phát triển
- **PHPUnit** - Unit testing
- **Git** - Version control

## 📊 Thống kê ấn tượng

- **+45%** Hiệu suất tiếp thị
- **50+** Quốc gia phủ sóng
- **2.5K+** Người dùng đang hoạt động
- **$1.2M** Doanh thu được tạo ra
- **100+** Dự án thành công
- **4.9/5** Đánh giá từ người dùng

## �� Cài đặt và chạy dự án

### **Yêu cầu hệ thống**
- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Node.js >= 16.0

### **Bước 1: Clone dự án**
```bash
git clone https://github.com/your-username/ttung-laravel.git
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
php artisan serve
npm run dev
```

Truy cập: `http://localhost:8000`

## 🔐 Xác thực và bảo mật

### **Google OAuth Integration**
- Đăng nhập/đăng ký bằng Google
- Xác thực 2 lớp
- Quản lý session an toàn

### **Role-based Access Control**
- **Admin**: Quản lý toàn bộ hệ thống
- **Shop**: Quản lý sản phẩm và đơn hàng
- **Publisher**: Quản lý chiến dịch tiếp thị

## 📱 Responsive Design

- **Desktop**: Tối ưu cho màn hình lớn
- **Tablet**: Giao diện thích ứng
- **Mobile**: Trải nghiệm di động hoàn hảo

## �� Tính năng đặc biệt

### **Dashboard Analytics**
- Biểu đồ doanh thu real-time
- Phân tích xu hướng bán hàng
- Báo cáo hiệu suất Publisher

### **Affiliate Link Generator**
- Tạo link affiliate tự động
- Theo dõi click và conversion
- Quản lý hoa hồng theo cấp độ

### **Multi-language Support**
- Tiếng Việt (mặc định)
- Tiếng Anh (sắp tới)
- Dễ dàng mở rộng ngôn ngữ

## 🤝 Đóng góp

Chúng tôi rất hoan nghênh mọi đóng góp! Hãy:

1. Fork dự án
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit thay đổi (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Mở Pull Request

## �� License

Dự án này được phân phối dưới giấy phép MIT. Xem file `LICENSE` để biết thêm chi tiết.

## �� Liên hệ

- **Email**: contact@ttung.com
- **Website**: https://ttung.com
- **GitHub**: [@ttung-dev](https://github.com/ttung-dev)

---

<div align="center">

**⭐ Nếu dự án này hữu ích, hãy cho chúng tôi một ngôi sao! ⭐**

</div>