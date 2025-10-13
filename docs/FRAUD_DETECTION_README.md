# 🛡️ Fraud Detection System - Documentation

## 📋 Tổng quan

Hệ thống Fraud Detection được thiết kế để bảo vệ nền tảng affiliate marketing khỏi các hành vi gian lận như:
- Click fraud (click ảo, bot clicking)
- Publisher self-clicking
- Rate abuse
- Bot/Scraper attacks

---

## ✅ Tính năng đã triển khai

### 1. **Bot Detection**
- ✅ Phát hiện bot patterns trong User Agent
- ✅ Whitelist cho legitimate bots (GoogleBot, BingBot...)
- ✅ Blacklist bot patterns (crawler, scraper, headless...)
- ✅ Kiểm tra User Agent hợp lệ (length, format)

### 2. **Rate Limiting**
- ✅ Giới hạn clicks per IP per hour (default: 10)
- ✅ Giới hạn clicks per IP per day (default: 50)
- ✅ Giới hạn clicks per link per IP per day (default: 3)
- ✅ Cache-based counting với Redis/Memory

### 3. **Publisher Self-Clicking Detection**
- ✅ Tracking publisher IPs
- ✅ Phát hiện publisher clicking own links
- ✅ Risk scoring cho suspicious behavior

### 4. **Rapid Click Detection**
- ✅ Phát hiện clicks nhanh liên tiếp (< 2 giây)
- ✅ Timestamp tracking cho mỗi click

### 5. **IP Blocking**
- ✅ Auto-ban IPs với risk score >= 100
- ✅ Manual IP blocking qua Admin dashboard
- ✅ Whitelist/Blacklist management
- ✅ Block duration: 30 ngày (configurable)

### 6. **Risk Scoring System**
- ✅ Risk score từ 0-100+
- ✅ Threshold: >= 50 = fraud
- ✅ Auto-block: >= 100
- ✅ Multiple checks với weighted scoring

### 7. **Fraud Logging**
- ✅ Database table: `click_fraud_logs`
- ✅ Log tất cả fraud attempts
- ✅ Metadata: IP, User Agent, Reasons, Risk Score
- ✅ Retention: 90 ngày (configurable)

### 8. **Admin Dashboard**
- ✅ Real-time fraud statistics
- ✅ Fraud trend charts
- ✅ Top fraud IPs
- ✅ Publishers with fraud attempts
- ✅ Recent fraud attempts table
- ✅ Export fraud logs (CSV)
- ✅ Clear cache functionality

---

## 🚀 Cài đặt

### 1. Chạy Migration

```bash
php artisan migrate
```

Migration sẽ tạo bảng `click_fraud_logs`:
```sql
- id
- affiliate_link_id
- publisher_id
- product_id
- campaign_id
- ip_address
- user_agent
- reasons (JSON)
- risk_score
- detected_at
- timestamps
```

### 2. Cấu hình (Optional)

Tạo file `.env` entries:

```env
# Fraud Detection Settings
FRAUD_MAX_CLICKS_PER_HOUR=10
FRAUD_MAX_CLICKS_PER_DAY=50
FRAUD_MAX_CLICKS_PER_LINK_PER_DAY=3

FRAUD_BOT_DETECTION_ENABLED=true
FRAUD_IP_BLOCKING_ENABLED=true
FRAUD_SELF_CLICK_DETECTION_ENABLED=true
FRAUD_RAPID_CLICK_DETECTION_ENABLED=true

FRAUD_LOGGING_ENABLED=true
FRAUD_NOTIFICATIONS_ENABLED=true
FRAUD_ADMIN_EMAIL=admin@example.com

# Advanced
FRAUD_FINGERPRINT_TRACKING=false
FRAUD_GEOLOCATION_CHECKING=false
FRAUD_VPN_DETECTION=false
```

### 3. Clear Cache (nếu cần)

```bash
php artisan cache:clear
```

---

## 📖 Cách sử dụng

### Tự động

Fraud detection đã được tích hợp vào `TrackingController`:
- Mỗi click qua affiliate link sẽ tự động được check
- Fraud clicks sẽ bị block và không nhận commission
- Valid clicks sẽ được xử lý bình thường

### Admin Dashboard

Truy cập: `/admin/fraud-detection`

**Tính năng:**
1. **Dashboard Overview**
   - Total fraud attempts
   - Average risk score
   - Unique fraud IPs
   - Blocked IPs count

2. **Fraud Trend Chart**
   - Visual timeline của fraud attempts
   - Average risk score theo ngày

3. **Top Fraud IPs**
   - Danh sách IPs có nhiều fraud attempts
   - Quick block button

4. **Publishers with Fraud**
   - Publishers có fraud attempts
   - Link to user profile

5. **Recent Fraud Attempts**
   - Chi tiết từng fraud attempt
   - Timestamp, IP, Risk Score, Reasons

6. **Actions**
   - Block/Unblock IP manually
   - Export fraud logs (CSV)
   - Clear cache
   - Cleanup old logs

---

## 🔍 Fraud Detection Logic

### Risk Scoring

| Check | Risk Score |
|-------|-----------|
| Bot detected | +100 (instant block) |
| Clicks/hour exceeded | +50 |
| Clicks/day exceeded | +70 |
| Duplicate clicks/link | +30 |
| Empty/short User Agent | +40 |
| Publisher self-clicking | +80 |
| Rapid sequential clicks | +60 |

**Total Risk Score:**
- 0-29: Clean
- 30-49: Suspicious
- 50-99: Fraud (blocked)
- 100+: High fraud (auto IP ban)

### Bot Patterns

**Blocked patterns:**
```
bot, crawl, spider, scraper, curl, wget, python, java, 
perl, ruby, php, scrape, harvest, extract, archiver, 
validator, monitor, checker, scan, headless
```

**Allowed bots:**
```
googlebot, bingbot, slackbot, twitterbot, 
facebookexternalhit, linkedinbot, whatsapp, telegrambot
```

---

## 📊 Database Schema

### Table: `click_fraud_logs`

```sql
CREATE TABLE click_fraud_logs (
    id BIGINT PRIMARY KEY,
    affiliate_link_id BIGINT NULL,
    publisher_id BIGINT NULL,
    product_id BIGINT NULL,
    campaign_id BIGINT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    reasons JSON,
    risk_score INT DEFAULT 0,
    detected_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_ip (ip_address),
    INDEX idx_publisher (publisher_id),
    INDEX idx_detected (detected_at),
    INDEX idx_ip_detected (ip_address, detected_at),
    INDEX idx_risk (risk_score)
);
```

---

## 🧪 Testing

### Run Test Script

```bash
php test_fraud_detection.php
```

**Test Cases:**
1. ✅ Normal click (legitimate user)
2. ❌ Bot user agent
3. ❌ Empty user agent
4. ❌ Too short user agent
5. ✅ Legitimate bot (GoogleBot)
6. ❌ Scraper pattern
7. ❌ Headless browser
8. 🔄 Rate limiting (15 clicks simulation)

### Manual Testing

1. **Test Bot Detection:**
```bash
curl -H "User-Agent: python-bot/1.0" http://localhost:8000/track/TEST123
# Should be blocked
```

2. **Test Normal Click:**
```bash
curl -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0" \
  http://localhost:8000/track/TEST123
# Should pass
```

3. **Test Rate Limiting:**
```bash
# Click same link 11 times rapidly
for i in {1..11}; do
  curl http://localhost:8000/track/TEST123
done
# 11th click should be blocked
```

---

## 🔧 API Methods

### FraudDetectionService

```php
// Detect fraud
$result = $fraudService->detectFraud($affiliateLink, $ipAddress, $userAgent);
// Returns: ['is_fraud' => bool, 'reason' => string, 'risk_score' => int]

// Check if IP blocked
$isBlocked = $fraudService->isIpBlocked($ipAddress);

// Block IP manually
$fraudService->blockIpAddress($ipAddress, $reason);

// Increment click counter
$fraudService->incrementClickCounter($ipAddress);

// Get statistics
$stats = $fraudService->getFraudStatistics($days = 7);

// Clear cache
$fraudService->clearCache($ipAddress = null);
```

---

## 📈 Performance

### Caching Strategy

- Click counters: Redis/Memory cache (1 hour TTL)
- Blocked IPs: Cache (30 days)
- Publisher IPs: Cache (1 hour)
- Fraud stats: Database queries (no cache)

### Optimization Tips

1. **Use Redis** cho caching (faster than file/database)
2. **Index database** properly (already done)
3. **Cleanup old logs** regularly:
   ```bash
   php artisan fraud:cleanup --days=90
   ```

---

## 🚨 Monitoring

### Logs

Fraud attempts được log vào:
1. **Laravel Log**: `storage/logs/laravel.log`
2. **Database**: `click_fraud_logs` table
3. **Admin Dashboard**: Real-time view

### Notifications

Configure email notifications:
```php
// config/fraud_detection.php
'notifications' => [
    'notify_admin_on_high_risk' => true,
    'admin_email' => 'admin@example.com',
]
```

---

## 🔐 Security Best Practices

1. ✅ **Always validate** clicks before paying commission
2. ✅ **Monitor** fraud dashboard daily
3. ✅ **Cleanup** old logs monthly
4. ✅ **Update** bot patterns regularly
5. ✅ **Review** high-risk publishers
6. ✅ **Backup** fraud logs before cleanup

---

## 🆘 Troubleshooting

### Issue: Legitimate clicks blocked

**Solution:**
- Check if IP is in blacklist
- Lower rate limiting thresholds
- Add IP to whitelist
- Check User Agent patterns

### Issue: Too many false positives

**Solution:**
- Increase risk threshold (50 → 70)
- Adjust individual check scores
- Review bot patterns
- Check cache issues

### Issue: Cache not working

**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
# Check CACHE_DRIVER in .env
```

---

## 📝 Future Enhancements

### Đề xuất cải tiến:

1. **Browser Fingerprinting**
   - Device fingerprint tracking
   - Canvas fingerprinting
   - WebGL fingerprinting

2. **Machine Learning**
   - ML model để predict fraud
   - Anomaly detection
   - Pattern recognition

3. **Geolocation**
   - IP geolocation checking
   - VPN/Proxy detection
   - Country restrictions

4. **Advanced Analytics**
   - Fraud prediction
   - Risk modeling
   - Behavior analysis

5. **Real-time Alerts**
   - Slack/Discord notifications
   - SMS alerts
   - Dashboard push notifications

---

## 📞 Support

Nếu có vấn đề, liên hệ:
- Email: admin@example.com
- Dashboard: `/admin/fraud-detection`

---

## 📄 License

MIT License - Feel free to customize for your needs.

---

**Built with ❤️ for secure affiliate marketing**

