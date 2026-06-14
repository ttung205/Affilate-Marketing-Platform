# Canva Presentation Outline: System Integration & Automation Testing

Tài liệu này hệ thống lại toàn bộ nội dung thuyết trình slide từ 4 phần báo cáo kiểm thử. Bạn có thể copy-paste trực tiếp các phần nội dung này vào các slide tương ứng trên Canva.

---

## 🖥️ Slide 1: Tổng Quan Dự Án & Công Cụ Kiểm Thử

### Tiêu đề slide

**BÁO CÁO KIỂM THỬ HỆ THỐNG AFFILIATE MARKETING PLATFORM**
_Hệ thống quản lý chiến dịch quảng cáo, chống gian lận, ví tài chính, voucher và rút tiền_

### 1. Giới thiệu dự án

- **Affiliate Marketing Platform** là nền tảng kết nối: **Shop (Advertiser)** đăng chiến dịch/sản phẩm → **Publisher** quảng bá link affiliate → **Khách mua hàng** → Hệ thống ghi nhận **Conversion** (đối soát hoa hồng) → Publisher thực hiện **Rút tiền** qua OTP Email.

### 2. Các công cụ sử dụng kiểm thử (Testing Stack)

- **PHPUnit**: Công cụ kiểm thử cốt lõi cho Unit Test & Integration Test.
- **Laravel Dusk**: Framework kiểm thử giao diện người dùng tự động (E2E/UI Testing) qua Chrome Headless.
- **Reflection APIs**: Kỹ thuật hộp trắng để kiểm thử trực tiếp các hàm `private`.
- **Mockery (Mocking/Faking)**: Cô lập hạ tầng bằng cách giả lập Cache, Mail, và Notification.
- **SQLite in-memory (`:memory:`)**: Tăng tốc độ thực thi kiểm thử và cách ly dữ liệu.

---

## 🖥️ Slide 2: Kiểm Thử Hộp Đen (Black-box Testing)

### Tiêu đề slide

**KIỂM THỬ HỘP ĐEN (BLACK-BOX TESTING)**
_Thiết kế dựa trên Phân lớp tương đương & Phân tích giá trị biên (BVA)_

### 1. Số lượng & Phương pháp kiểm thử

- **Số lượng test cases**: **44 Test Cases** (gồm P3 UI và P4 Integration).
- **Phương pháp**: Phân lớp tương đương (gom dữ liệu hợp lệ/lỗi) và Phân tích giá trị biên (BVA) tập trung vào các trường số (ngân sách, hoa hồng, giá tiền, OTP).

### 2. Luồng kiểm thử chính được miêu tả: Vòng đời Voucher & Quy trình rút tiền OTP

- **Voucher**:
    - Tạo Voucher → Validator kiểm tra biên tỷ lệ giảm giá (`value` từ 1% đến 100%, lỗi tại 0% hoặc 101%).
    - Chỉ áp dụng khi đạt đơn tối thiểu (`min_order` ≥ 0).
- **Rút tiền & OTP**:
    - Yêu cầu rút tiền biên giới hạn `[100,000đ - 5,000,000đ]`.
    - Xác thực OTP Email gửi từ hệ thống. Nếu nhập sai quá **3 lần**, mã OTP bị hủy ngay lập tức để chống Brute Force.

### 3. Lỗi phát hiện & khắc phục khi kiểm thử

- **Lỗi thứ tự Route (Route Hijacking)**: Route tĩnh `/withdrawal/api/stats` bị nhận nhầm thành wildcard `/withdrawal/api/{withdrawal}` dẫn đến lỗi 404.
    - _Khắc phục_: Sắp xếp đưa route tĩnh lên trước route động.
- **Lỗi thiếu File View**: Thiếu file view web khi test truy cập trang rút tiền.
    - _Khắc phục_: Tạo bổ sung file view `create.blade.php`, `show.blade.php` tối giản kế thừa layout.

---

## 🖥️ Slide 3: Kiểm Thử Hộp Trắng (White-box Testing)

### Tiêu đề slide

**KIỂM THỬ HỘP TRẮNG (WHITE-BOX TESTING)**
_Bao phủ cấu trúc mã nguồn (Branch & Condition Coverage)_

### 1. Số lượng & Phương pháp kiểm thử

- **Số lượng test cases**: **38 Test Cases** (P1 Fraud và P2 Finance).
- **Phương pháp áp dụng**:
    - **Reflection API**: Bypass tính đóng gói (encapsulation) để test trực tiếp các hàm `private` trong class logic.
    - **Cache Mocking**: Giả lập dữ liệu truy cập trong Cache để kiểm tra rate limit mà không làm thay đổi tài nguyên thực.

### 2. Luồng kiểm thử chính được miêu tả: Thuật toán phát hiện gian lận (Fraud Detection)

- **Bot User-Agent Detection**: Phân loại và phát hiện Bot đáng ngờ (như `curl`, `wget`, `python-requests`, chuỗi ngắn < 20 ký tự) và cho phép các SEO bot hợp lệ qua cửa.
- **IP Rate Limiting**: Đo lường số lượt click theo giờ (biên 10 clicks/IP/giờ) và theo ngày (biên 50 clicks/IP/ngày). Nếu vượt ngưỡng, cộng dồn điểm rủi ro (Risk Score) để tự động khóa giao dịch.

### 3. Lỗi phát hiện & khắc phục khi kiểm thử

- **Lỗi rò rỉ giao dịch cơ sở dữ liệu (Database Transaction Leak)**: Trong API đối soát hoa hồng, hệ thống mở transaction trước khi kiểm tra link affiliate. Khi link không tồn tại (404), hàm kết thúc đột ngột nhưng **không rollback**, làm treo kết nối DB sau đó.
    - _Khắc phục_: Đưa dòng lệnh check link lên trước khi mở Database Transaction.
- **Lỗi truy cập hàm private**: Lỗi "Call to private method" khi test unit.
    - _Khắc phục_: Sử dụng `ReflectionMethod` và gọi `setAccessible(true)`.

---

## 🖥️ Slide 4: Hạ Tầng E2E Automation Testing (Laravel Dusk)

### Tiêu đề slide

**QUY TRÌNH HẠ TẦNG & MÔ HÌNH HÓA KIỂM THỬ UI TỰ ĐỘNG (LARAVEL DUSK)**
_Cách cô lập dữ liệu và cấu trúc mã nguồn theo chuẩn công nghiệp POM_

### 1. Quy trình thực thi kiểm thử E2E tự động hóa (Step-by-step)

- **Bước 1: Khởi động Database**: Dusk Runner tự động chạy `DatabaseMigrations` (`migrate:fresh`) để thiết lập một DB SQLite sạch hoàn toàn trong tệp `database.sqlite`.
- **Bước 2: Kích hoạt Driver**: Dusk khởi chạy tiến trình **ChromeDriver** tại cổng `9515`.
- **Bước 3: Gửi HTTP Requests**: Trình duyệt Chrome Headless được điều hướng truy cập trang web thông qua Web Server (được cấu hình bằng tệp môi trường `.env.dusk.local` riêng biệt).
- **Bước 4: Tương tác & Khẳng định (Assert)**: Dusk mô phỏng hành vi click chuột, gõ phím, đợi phần tử UI hiển thị (`waitFor`) và so sánh kết quả mong đợi.
- **Bước 5: Chụp ảnh minh chứng**: Nếu test case thất bại, hệ thống tự động chụp màn hình lưu vào thư mục `/screenshots` để debug.

### 2. Mô hình Page Object Model (POM) & Components

Giúp tách biệt bộ định vị HTML selector khỏi logic kiểm thử:

- **Pages (Trang nghiệp vụ)**:
    - `CampaignPage`: Quản lý việc điền form và tạo chiến dịch tại `/admin/campaigns/create`.
    - `ProductPage` & `CategoryPage`: Đóng gói hành vi tạo sản phẩm/danh mục của Admin.
    - `DashboardPage`: Định hướng trang tổng quan sau khi đăng nhập thành công.
- **Components (Thành phần dùng chung)**:
    - `Chat`: Mô hình hóa widget chatbot (`#chatbot-widget`), quản lý việc gửi tin nhắn và chờ phản hồi.
    - `Notification`: Mô hình hóa dropdown thông báo chuông (`.notification-dropdown`).

---

## 🖥️ Slide 5: Chi Tiết 3 Luồng Kiểm Thử UI Tự Động Cốt Lõi (Phần 3)

### Tiêu đề slide

**CHI TIẾT 3 LUỒNG KIỂM THỰC TẾ TRÊN GIAO DIỆN (UI E2E FLOWS)**
_Mô phỏng hành vi thực tế của Admin & Publisher_

### 1. Luồng 1: Admin quản lý Chiến dịch & Sản phẩm (Campaign & Product Flow)

- **Hành vi tự động**: Đăng nhập Admin → Vào `/admin/campaigns/create` → Điền thông tin hợp lệ → Submit.
- **Kiểm thử biên (BVA)**: Tự động điền các mốc biên:
    - Ngân sách: `budget = -1` (Báo lỗi đỏ), `budget = 0` (Tạo thành công).
    - Tỷ lệ hoa hồng: `commission_rate = -0.01` (Chặn), `100.00%` (Tạo thành công).
    - Tự động chèn giá trị ngày tháng bắt đầu/kết thúc thông qua JavaScript để tránh lỗi xung đột sự kiện `change`.

### 2. Luồng 2: Publisher tìm kiếm & Bộ lọc Dashboard (Search & Filter Flow)

- **Hành vi tự động**: Đăng nhập Publisher → Truy cập `/publisher/products`.
- **Kiểm thử tìm kiếm & lọc**:
    - Nhập từ khóa hợp lệ (`"Áo Thun"`) → Khẳng định có hiển thị sản phẩm tương ứng.
    - Nhập từ khóa không tồn tại (`"Không có thực"`) → Xác nhận hiển thị thông báo trống.
    - Lọc danh mục hợp lệ từ dropdown → Khẳng định danh sách hiển thị đúng sản phẩm thuộc danh mục.

### 3. Luồng 3: Tương tác Chatbot thời gian thực & Notification

- **Hành vi tự động**: Publisher click mở khung chat → Nhập `"hello"` → Hệ thống bắt sự kiện và khẳng định nhận được phản hồi tự động `"Tôi có thể giúp gì cho bạn?"`.
- **Kiểm thử biên độ dài tin nhắn**: Trống tin nhắn (chặn gửi), gửi 1000 kí tự (gửi thành công), gửi 1005 kí tự (chặn tại form).
- **Notification**: Tự động polling và hiển thị số thông báo chưa đọc → Click "Đánh dấu đã đọc" → Khẳng định badge số lượng biến mất trên chuông thông báo.

---

## 🖥️ Slide 6: Kiểm Thử Nâng Cao (Advanced Testing)

### Tiêu đề slide

**KIỂM THỬ NÂNG CAO (ADVANCED TESTING)**
_Chống sập hệ thống (Fuzzing) và Đảm bảo giao diện đa trình duyệt (Cross-browser)_

### 1. Fuzz Testing (Kiểm thử ngẫu nhiên độc hại)

- **Mục tiêu**: Đảm bảo bộ lọc chống gian lận (P1) và xử lý Voucher (P4) không bị crash khi nhận các chuỗi dữ liệu độc hại hoặc ngẫu nhiên.
- **Kịch bản thực thi**:
    - Tự động sinh **1000 lượt yêu cầu** chứa IP ngẫu nhiên, User-Agent bất thường (SQL Injection payload, XSS script, chuỗi siêu dài, null byte).
    - Gọi liên tục hàm `detectFraud()` và xử lý Voucher.
- **Kết quả**: Hệ thống xử lý an toàn, **không phát sinh Exception**, không rò rỉ bộ nhớ (memory leak), chứng minh độ ổn định cực cao.

### 2. Cross-browser & Responsive Testing

- **Đa trình duyệt**: Chạy các kịch bản Dusk và Playwright trên các nhân trình duyệt khác nhau (**Chrome, Firefox, Safari**) để kiểm tra tính tương thích.
- **Responsive Viewport**: Tự động thay đổi kích thước màn hình:
    - _Desktop_ (1920 × 1080 px) → _Tablet_ (768 × 1024 px) → _Mobile_ (375 × 812 px).
    - **Kết quả**: Bố cục Dashboard, Sidebar co giãn hoàn hảo, không bị vỡ layout (UI Integrity).

---

## 🖥️ Slide 7: Mô Hình Cộng Tác GitHub (Collaboration Workflow)

### Tiêu đề slide

**MÔ HÌNH HÓA CỘNG TÁC GITHUB**
_Quy trình làm việc nhóm chuyên nghiệp và kiểm soát chất lượng mã nguồn_

### 1. Quy ước đặt tên chi nhánh (Branch Conventions)

Hệ thống chia nhánh theo mã số phần việc của từng thành viên:

- `p1/feature-fraud-testing` (Chống gian lận & Click)
- `p2/feature-finance-testing` (Tài chính & Ví)
- `p3/feature-ui-testing` (Laravel Dusk UI)
- `p4/feature-withdrawal-testing` (Voucher & OTP Rút tiền)

### 2. Quy trình Pull Request & Review chất lượng

1.  **Lập trình viên** hoàn thành test trên local → Push code lên branch riêng.
2.  **Tạo Pull Request (PR)** trỏ về nhánh `master`.
3.  **Hệ thống phân loại PR** bằng các nhãn (Labels): `white-box`, `black-box`, `bug`, `enhancement`.
4.  **Thành viên khác bắt buộc review**: Ít nhất **2 lượt Approve** từ đồng nghiệp sau khi kiểm tra kỹ lưỡng (không lỗi logic, coverage đạt mục tiêu) mới được merge vào nhánh chính.

---

## 🖥️ Slide 8: Tổng Kết Kết Quả & Lời Cảm Ơn

### Tiêu đề slide

**TỔNG KẾT KẾT QUẢ ĐÃ ĐẠT ĐƯỢC**

### 1. Kết quả kiểm thử toàn diện

- **Hoàn thành 100%** chỉ tiêu kiểm thử hộp trắng (White-box) và hộp đen (Black-box) đề ra trong ma trận RTM.
- **Code Coverage đạt tỷ lệ ấn tượng cho các module nghiệp vụ được giao**:
    - Các Controller cốt lõi đạt từ **99.17% đến 100%** (`Shop/VoucherController`: 100%, `Publisher/WithdrawalController`: 100%, `Publisher/ConversionController`: 100%, `Shop/ConversionController`: 99.17%).
    - Lớp logic chống gian lận (`FraudDetectionService`): Đạt **67.61%** độ bao phủ dòng.
- **Phát hiện và vá thành công** nhiều lỗi logic bảo mật nghiêm trọng (rò rỉ kết nối DB, chiếm quyền routing, xung đột cú pháp SQLite vs MySQL).

### 2. Lời cảm ơn

- Cảm ơn Thầy Cô trong Hội đồng và các bạn đã chú ý lắng nghe!
- _Sẵn sàng trả lời các câu hỏi phản biện từ Hội đồng._
