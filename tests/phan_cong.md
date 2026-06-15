# Kế Hoạch Kiểm Thử Hệ Thống Affiliate Marketing

## 1. Phân Công Công Việc

---

# NHÓM 1: KIỂM THỬ HỘP TRẮNG (White-box Testing)

**Mục tiêu:** Kiểm tra logic bên trong mã nguồn, độ phủ mã nguồn (Code Coverage) và tính chính xác của thuật toán.

**Coverage mục tiêu:**

- Statement Coverage ≥ 85%
- Branch Coverage ≥ 85%
- Tập trung vào các module:
    - Fraud Detection
    - Tracking
    - Wallet
    - Ranking

---

## Người 1 (P1) – Logic Tracking & Chống Gian Lận

### Module phụ trách

- FraudDetectionService
- TrackingController
- Click
- User (Authentication)

### Mục tiêu

Đảm bảo mọi nhánh điều kiện trong thuật toán phát hiện gian lận và điều hướng tracking link đều được thực thi.

### Unit Test Cases (PHPUnit)

#### test_bot_detection_logic

Kiểm tra hàm `isBotUserAgent()` với:

- 20+ User-Agent hợp lệ
- 20+ User-Agent bot
- Danh sách lấy từ file cấu hình hệ thống

#### test_ip_rate_limit_calculation

Giả lập dữ liệu Cache để kiểm tra:

- Số lượng click/IP
- Số lượng click theo giờ
- Ngưỡng giới hạn click

#### test_risk_score_aggregation

Kiểm tra việc cộng dồn điểm rủi ro khi:

- User-Agent đáng ngờ
- IP vượt ngưỡng
- Cookie bất thường
- Nhiều vi phạm cùng lúc

#### test_self_click_prevention

Kiểm tra điều kiện:

```php
$publisher_id == $user_id
```

Đảm bảo publisher không thể tự click link của chính mình.

#### test_cookie_assignment_logic

Kiểm tra:

- Cookie tracking được tạo
- Cookie được lưu đúng giá trị
- Cookie tồn tại đúng thời gian quy định

### Kiểm thử nâng cao (Bonus)

#### Random Testing / Fuzz Testing

Viết script tự động tạo:

- 1000 User-Agent ngẫu nhiên
- 1000 IP giả lập

Sau đó đẩy vào:

```php
FraudDetectionService
```

Mục tiêu:

- Không phát sinh Exception
- Không crash hệ thống
- Không gây memory leak

### Cấu trúc file

```text
tests/
└── Unit/
    └── P1_Fraud/
        ├── FraudDetectionServiceTest.php
        └── TrackingControllerTest.php
```

---

## Người 2 (P2) – Tài Chính, Xếp Hạng & Phí Nền Tảng

### Module phụ trách

- PublisherWallet
- PublisherRanking
- Transaction
- PlatformFeeSetting
- PlatformFeePayment

### Mục tiêu

Đảm bảo tính chính xác tuyệt đối của:

- Các phép tính Decimal
- Cập nhật ví
- Thăng hạng Publisher
- Phí nền tảng

### Unit/Feature Test Cases

#### test_wallet_balance_consistency

Kiểm tra:

- Conversion thành công
- Hoa hồng được cộng đúng vào ví

#### test_platform_fee_deduction

Kiểm tra:

- Tính đúng phần trăm phí
- Trừ phí đúng cấu hình

Ví dụ:

```text
Commission: 100.000đ
Platform Fee: 10%

Expected:
Publisher nhận: 90.000đ
Platform nhận: 10.000đ
```

#### test_ranking_upgrade_conditions

Kiểm tra logic nâng hạng:

```text
Bronze → Silver
Silver → Gold
Gold → Platinum
```

khi đạt các mốc doanh thu tương ứng.

#### test_transaction_status_transitions

Kiểm tra luồng trạng thái:

```text
Pending
   ↓
Approved
   ↓
Completed
```

Đảm bảo không thể chuyển trạng thái sai quy tắc.

#### test_concurrency_withdrawal

Kiểm tra Race Condition khi:

- 2 yêu cầu rút tiền gửi đồng thời
- Không được phát sinh số dư âm

### Kiểm thử nâng cao

**Không dùng Unit Test thuần túy.**

Sử dụng:

- Apache JMeter
- wrk
- hoặc Integration Test

Bắn đồng thời:

```text
20 - 30 request withdrawal
```

Mục tiêu:

- Không trừ tiền hai lần
- Không tạo giao dịch trùng
- Không âm số dư

### Coverage Report

Sử dụng:

```text
Xdebug
```

Xuất:

```text
HTML Coverage Report
```

Đưa ảnh minh chứng vào báo cáo.

### Cấu trúc file

```text
tests/
└── Unit/
    └── P2_Finance/
        ├── PublisherWalletTest.php
        └── RankingUpgradeTest.php
```

---

# NHÓM 2: KIỂM THỬ HỘP ĐEN (Black-box Testing)

**Mục tiêu:** Kiểm thử từ góc nhìn người dùng cuối.

---

## Người 3 (P3) – Hành Trình Nhà Quảng Cáo & Tương Tác

### Module phụ trách

- Campaign
- Product
- Category
- ChatMessage
- Notification

### Automation UI Testing

#### test_ui_campaign_creation_flow

Tự động:

- Điền form tạo chiến dịch
- Chọn Category
- Upload ảnh sản phẩm
- Submit thành công

#### test_real_time_chat_interaction

Kiểm tra:

Publisher gửi tin nhắn

↓

Admin nhận ngay lập tức

↓

Tin nhắn hiển thị đúng nội dung

#### test_notification_delivery

Kiểm tra:

- Chuông thông báo tăng số lượng
- Nội dung thông báo hiển thị đúng

#### test_product_search_and_filter

Kiểm tra:

- Tìm kiếm sản phẩm
- Lọc theo Category
- Lọc theo trạng thái

### Cross-browser & Viewport Testing

Chạy trên trình duyệt chính (Chrome/Edge) và kiểm tra tính tương thích giao diện:

- Giả lập đa độ phân giải: Desktop (1920x1080), Tablet (768x1024), Mobile (375x812)
- Kiểm tra tính Responsive
- Đảm bảo giao diện không bị vỡ layout

### Evidence

Laravel Dusk tự động lưu:

- Screenshot khi kiểm thử thất bại (hoặc chụp thủ công lúc thành công) tại `tests/Browser/screenshots/`
- Console logs từ trình duyệt tại `tests/Browser/console/`

### Cấu trúc file

```text
tests/Browser/
└── P3_UI/
    ├── CampaignCreationUiTest.php
    ├── CategoryManagementUiTest.php
    ├── ProductManagementUiTest.php
    ├── RealTimeChatTest.php
    └── CrossBrowserExecution.php
```

---

## Người 4 (P4) – Conversion, Voucher & Quy Trình Thanh Toán

### Module phụ trách

- Conversion
- Voucher
- Withdrawal
- WithdrawalApproval
- WithdrawalOTPMail

### Feature / Integration Test

#### test_voucher_stacking_logic

Kiểm tra:

- Voucher hợp lệ
- Voucher hết hạn
- Áp dụng nhiều voucher
- Giới hạn số lượng voucher

#### test_conversion_attribution

Giả lập:

```text
Click
 ↓
Mua hàng
 ↓
Conversion
```

Kiểm tra Publisher được gán chính xác.

#### test_full_withdrawal_process_with_otp

Kiểm tra toàn bộ luồng:

```text
Yêu cầu rút tiền
       ↓
Nhận OTP qua Email
       ↓
Nhập OTP
       ↓
Admin duyệt
       ↓
Hoàn thành
```

#### test_invalid_withdrawal_inputs

Kiểm tra giá trị biên:

| Trường hợp      | Kết quả mong đợi |
| --------------- | ---------------- |
| Rút tiền âm     | Reject           |
| Rút tiền = 0    | Reject           |
| Rút > số dư     | Reject           |
| Rút < tối thiểu | Reject           |
| OTP sai         | Reject           |

---

# 2. RTM (Requirement Traceability Matrix)

## Danh sách Requirement

| ID     | Requirement         |
| ------ | ------------------- |
| REQ-01 | Rút tiền            |
| REQ-02 | Quản lý Voucher     |
| REQ-03 | Tracking Click      |
| REQ-04 | Fraud Detection     |
| REQ-05 | Tính Hoa Hồng       |
| REQ-06 | Nâng Hạng Publisher |
| REQ-07 | Chat Real-time      |
| REQ-08 | Notification        |

## Ví dụ RTM

| Requirement | Test Cases                                                                                             |
| ----------- | ------------------------------------------------------------------------------------------------------ |
| REQ-01      | test_full_withdrawal_process_with_otp, test_invalid_withdrawal_inputs, test_wallet_balance_consistency |
| REQ-02      | test_voucher_stacking_logic                                                                            |
| REQ-03      | test_cookie_assignment_logic                                                                           |
| REQ-04      | test_bot_detection_logic, test_risk_score_aggregation                                                  |
| REQ-05      | test_wallet_balance_consistency                                                                        |
| REQ-06      | test_ranking_upgrade_conditions                                                                        |
| REQ-07      | test_real_time_chat_interaction                                                                        |
| REQ-08      | test_notification_delivery                                                                             |

---

# 3. Chiến Lược GitHub

## Quy Ước Branch

```text
p1/feature-fraud-testing
p2/feature-finance-testing
p3/feature-ui-testing
p4/feature-withdrawal-testing
```

## Labels

```text
bug
white-box
black-box
enhancement
```

## Pull Request Process

1. Tạo branch riêng
2. Commit code test
3. Tạo Pull Request
4. Thành viên khác review
5. Có ít nhất 2 comment review
6. Được approve mới merge

Ví dụ comment review:

```text
Code test sạch.
Coverage đạt yêu cầu.
Không phát hiện lỗi logic.
Approved.
```

---

# 4. Cấu Trúc Thư Mục Kiểm Thử

```text
tests/
├── Unit/
│   ├── P1_Fraud/
│   │   ├── FraudDetectionServiceTest.php
│   │   └── TrackingControllerTest.php
│   │
│   └── P2_Finance/
│       ├── PublisherWalletTest.php
│       └── RankingUpgradeTest.php
│
├── Feature/
│   ├── P3_UI/
│   │   ├── CampaignCreationUiTest.php
│   │   └── RealTimeChatTest.php
│   │
│   └── P4_Integration/
│       ├── VoucherStackingTest.php
│       └── WithdrawalProcessTest.php
│
├── Fuzzing/
│   └── FraudFuzzingScript.php
│
└── e2e/
    └── campaign_flow.spec.js
```

---

# 5. Chiến Lược Đạt Điểm Xuất Sắc

| Tiêu chí            | Chiến lược                                   |
| ------------------- | -------------------------------------------- |
| Chương trình (1.5đ) | Khai thác hệ thống Fraud Detection           |
| Hộp đen (1.5đ)      | Phân lớp tương đương + Giá trị biên chi tiết |
| Hộp trắng (1.5đ)    | Coverage ≥ 85%, chụp ảnh code phủ xanh       |
| Tự động (1.0đ)      | Laravel Test + Playwright                    |
| Nâng cao (1.0đ)     | Fuzz Testing + Cross-browser Testing         |
| GitHub (0.5đ)       | Branch, PR, Review chuyên nghiệp             |
| Báo cáo (1.5đ)      | RTM, Coverage Report, Video, Screenshot      |

## Deliverables cuối cùng

- Báo cáo kiểm thử
- RTM
- HTML Coverage Report
- Video Playwright
- Screenshot Evidence
- GitHub Pull Requests
- Test Source Code
- Kết quả Fuzz Testing
- Kết quả Cross-browser Testing
