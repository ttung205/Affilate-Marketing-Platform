# Affiliate Links System - Hướng dẫn sử dụng

## **Tổng quan**

Hệ thống Affiliate Links cho phép publishers tạo và quản lý các link tiếp thị cho sản phẩm, với ràng buộc đảm bảo 1 publisher + 1 sản phẩm chỉ có 1 link duy nhất.

## **Cấu trúc hệ thống**

### **1. Controllers**
- **Admin AffiliateLinkController**: Quản lý affiliate links cho tất cả publishers
- **Publisher AffiliateLinkController**: Quản lý affiliate links của chính mình
- **Publisher ProductController**: Tạo affiliate link nhanh từ product page

### **2. Models**
- **AffiliateLink**: Model chính cho affiliate links
- **User**: Publisher và shop owners
- **Product**: Sản phẩm để tiếp thị
- **Campaign**: Chiến dịch marketing

### **3. Traits**
- **AffiliateLinkTrait**: Chia sẻ logic chung giữa Admin và Publisher

### **4. Database**
- **Unique Constraints**: 
  - `publisher_id + product_id` (1 publisher + 1 sản phẩm = 1 link)
  - `tracking_code` (duy nhất)
  - `short_code` (duy nhất)

## **Quy trình hoạt động**

### **Admin tạo link:**
1. Admin vào `/admin/affiliate-links/create`
2. Chọn publisher, product, campaign
3. Nhập commission rate và URL
4. Hệ thống tự động tạo tracking code và short code
5. Publisher có thể xem link trong dashboard của mình

### **Publisher tạo link:**
1. Publisher vào product page
2. Click "LẤY LINK" button
3. Modal hiện ra ở giữa màn hình
4. Hệ thống tự động tạo link
5. Publisher có thể copy link để sử dụng

## **Cách sử dụng**

### **1. Include CSS và JS**
```html
<!-- Trong layout hoặc view -->
<link rel="stylesheet" href="/css/components/affiliate-link-modal.css">
<script src="/js/components/affiliate-link-modal.js"></script>
```

### **2. Sử dụng modal**
```javascript
// Hiển thị modal cho sản phẩm
window.affiliateLinkModal.show(productId, {
    image: productImageUrl
});
```

### **3. Button "LẤY LINK"**
```html
<button 
    class="btn btn-primary" 
    onclick="window.affiliateLinkModal.show({{ $product->id }}, {image: '{{ $product->image }}'})"
>
    <i class="fas fa-link"></i> LẤY LINK
</button>
```

## **API Endpoints**

### **Tạo affiliate link**
```
POST /publisher/products/{id}/affiliate-link
```

**Response Success:**
```json
{
    "success": true,
    "message": "Tạo link tiếp thị thành công!",
    "data": {
        "affiliate_link": "https://domain.com/ref/ABC123",
        "short_code": "ABC123",
        "tracking_code": "PUBL_PROD_20250823_ABC123",
        "commission_rate": 15.00
    }
}
```

**Response Error:**
```json
{
    "success": false,
    "message": "Bạn đã có link tiếp thị cho sản phẩm này rồi!"
}
```

## **Tính năng chính**

### **1. Ràng buộc duy nhất**
- 1 publisher + 1 sản phẩm = 1 link duy nhất
- Không thể tạo link trùng lặp
- Có thể xóa link cũ để tạo link mới

### **2. Tracking tự động**
- Tự động tạo tracking code unique
- Tự động tạo short code unique
- Format: `PUBL_PROD_YYYYMMDD_RANDOM`

### **3. Modal đẹp và responsive**
- Hiển thị ở giữa màn hình
- Loading state khi tạo link
- Copy to clipboard functionality
- Error handling đầy đủ

### **4. Security**
- Publisher chỉ có thể tạo link cho chính mình
- Admin có thể tạo link cho bất kỳ ai
- Validation đầy đủ

## **Troubleshooting**

### **Lỗi "Không thể tạo link"**
1. Kiểm tra database connection
2. Kiểm tra migration đã chạy chưa
3. Kiểm tra unique constraints
4. Kiểm tra log files

### **Modal không hiển thị**
1. Kiểm tra Bootstrap CSS/JS đã load
2. Kiểm tra console errors
3. Kiểm tra file paths

### **Link không tạo được**
1. Kiểm tra publisher có quyền không
2. Kiểm tra product có tồn tại không
3. Kiểm tra database schema

## **Maintenance**

### **Clean up old links**
```sql
-- Xóa links không active quá 30 ngày
DELETE FROM affiliate_links 
WHERE status = 'inactive' 
AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### **Regenerate codes**
```php
// Trong tinker
$link = AffiliateLink::find(1);
$link->update([
    'tracking_code' => $this->generateTrackingCode($link->publisher, $link->product),
    'short_code' => $this->generateShortCode()
]);
```

## **Performance Tips**

1. **Indexes**: Đã có sẵn indexes cho `publisher_id`, `product_id`, `status`
2. **Eager Loading**: Sử dụng `with()` để load relationships
3. **Caching**: Có thể cache affiliate links cho products
4. **Pagination**: Sử dụng pagination cho danh sách links

## **Testing**

### **Chạy seeder test**
```bash
ddev exec php artisan db:seed --class=AffiliateLinkTestSeeder
```

### **Test tạo link**
```bash
# Tạo test data
ddev exec php artisan tinker
# Test tạo link
$publisher = User::where('role', 'publisher')->first();
$product = Product::first();
$link = $publisher->affiliateLinks()->create([...]);
```

## **Deployment**

1. Chạy migrations: `php artisan migrate`
2. Clear cache: `php artisan cache:clear`
3. Clear config: `php artisan config:clear`
4. Restart queue workers nếu có

## **Support**

Nếu gặp vấn đề, hãy kiểm tra:
1. Laravel logs: `storage/logs/laravel.log`
2. Database logs
3. Browser console
4. Network tab trong DevTools
