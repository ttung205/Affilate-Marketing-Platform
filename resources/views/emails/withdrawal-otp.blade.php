<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác thực rút tiền</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 10px;
        }
        .otp-code {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            letter-spacing: 5px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background: #fef3cd;
            border: 1px solid #fecaca;
            color: #92400e;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .withdrawal-details {
            background: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #374151;
        }
        .detail-value {
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            <h2>Xác thực rút tiền</h2>
        </div>

        <p>Xin chào <strong>{{ $user->name }}</strong>,</p>

        <p>Bạn đã yêu cầu rút tiền từ tài khoản của mình. <strong>Để đảm bảo bảo mật tối đa, tất cả yêu cầu rút tiền đều yêu cầu xác thực OTP.</strong> Vui lòng sử dụng mã OTP dưới đây để hoàn tất giao dịch:</p>

        <div class="otp-code">
            {{ $otp }}
        </div>

        <div class="info-box">
            <strong>Thông tin yêu cầu rút tiền:</strong>
            <div class="withdrawal-details">
                <div class="detail-row">
                    <span class="detail-label">Thời gian yêu cầu:</span>
                    <span class="detail-value">{{ now()->format('H:i d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="warning">
            <strong>⚠️ Lưu ý quan trọng:</strong>
            <ul>
                <li>Mã OTP này có hiệu lực trong <strong>10 phút</strong> (hết hạn lúc {{ now()->addMinutes(10)->format('H:i d/m/Y') }})</li>
                <li>Không chia sẻ mã này với bất kỳ ai</li>
                <li>Nếu bạn không thực hiện yêu cầu này, vui lòng liên hệ hỗ trợ ngay lập tức</li>
                <li>Chỉ nhập mã OTP trên website chính thức của chúng tôi</li>
            </ul>
        </div>

        <p>Nếu bạn gặp khó khăn hoặc cần hỗ trợ, vui lòng liên hệ:</p>
        <ul>
            <li>Email: support@{{ parse_url(config('app.url'), PHP_URL_HOST) }}</li>
            <li>Hotline: 1900-xxxx</li>
        </ul>

        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống {{ config('app.name') }}</p>
            <p>Vui lòng không trả lời email này</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
