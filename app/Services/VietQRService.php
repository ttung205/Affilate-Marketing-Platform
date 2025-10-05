<?php

namespace App\Services;

use App\Models\Withdrawal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VietQRService
{
    /**
     * Generate VietQR code cho việc chuyển tiền rút tiền
     * 
     * @param Withdrawal $withdrawal
     * @return array
     */
    public function generateQRForWithdrawal(Withdrawal $withdrawal): array
    {
        try {
            $paymentMethod = $withdrawal->paymentMethod;
            
            // Lấy thông tin tài khoản
            $accountNo = $paymentMethod->account_number;
            $accountName = $paymentMethod->account_name;
            $bankCode = $paymentMethod->bank_code ?? $this->getBankCode($paymentMethod->bank_name);
            $amount = (int) $withdrawal->net_amount; // Số tiền thực nhận
            
            // Tạo nội dung chuyển khoản
            $description = $this->generateTransferDescription($withdrawal);
            
            // Generate QR code URL using VietQR API
            $qrUrl = $this->generateVietQRUrl($bankCode, $accountNo, $amount, $description, $accountName);
            
            return [
                'success' => true,
                'qr_url' => $qrUrl,
                'account_no' => $accountNo,
                'account_name' => $accountName,
                'bank_name' => $paymentMethod->bank_name,
                'bank_code' => $bankCode,
                'amount' => $amount,
                'description' => $description,
                'withdrawal_id' => $withdrawal->id,
            ];
            
        } catch (\Exception $e) {
            Log::error('VietQR generation failed', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Không thể tạo mã QR: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate VietQR URL
     * 
     * @param string $bankCode
     * @param string $accountNo
     * @param int $amount
     * @param string $description
     * @param string $accountName
     * @return string
     */
    private function generateVietQRUrl(
        string $bankCode, 
        string $accountNo, 
        int $amount, 
        string $description,
        string $accountName
    ): string {
        // VietQR API v2 - Free tier
        // Docs: https://www.vietqr.io/danh-sach-api
        
        $baseUrl = 'https://img.vietqr.io/image';
        
        // Template: compact2 (có logo ngân hàng, gọn gàng)
        $template = 'compact2';
        
        // Build URL
        $url = sprintf(
            '%s/%s-%s-%s.jpg?amount=%d&addInfo=%s&accountName=%s',
            $baseUrl,
            $bankCode,
            $accountNo,
            $template,
            $amount,
            urlencode($description),
            urlencode($accountName)
        );
        
        return $url;
    }
    
    /**
     * Generate transfer description
     * 
     * @param Withdrawal $withdrawal
     * @return string
     */
    private function generateTransferDescription(Withdrawal $withdrawal): string
    {
        // Format: RUT{withdrawal_id} {publisher_name}
        // VD: RUT123 Nguyen Van A
        $publisherName = $withdrawal->publisher->name ?? '';
        $description = sprintf('RUT%d %s', $withdrawal->id, $publisherName);
        
        // Giới hạn độ dài (VietQR max 25 ký tự)
        if (strlen($description) > 25) {
            $description = substr($description, 0, 25);
        }
        
        return $description;
    }
    
    /**
     * Get bank code from bank name
     * 
     * @param string|null $bankName
     * @return string
     */
    private function getBankCode(?string $bankName): string
    {
        // Mapping tên ngân hàng -> mã ngân hàng (VietQR)
        $bankMapping = [
            'Vietcombank' => 'VCB',
            'Ngân hàng TMCP Ngoại thương Việt Nam' => 'VCB',
            'VCB' => 'VCB',
            
            'Techcombank' => 'TCB',
            'Ngân hàng TMCP Kỹ thương Việt Nam' => 'TCB',
            'TCB' => 'TCB',
            
            'BIDV' => 'BIDV',
            'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam' => 'BIDV',
            
            'Vietinbank' => 'CTG',
            'Ngân hàng TMCP Công thương Việt Nam' => 'CTG',
            'CTG' => 'CTG',
            
            'Agribank' => 'AGRIBANK',
            'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam' => 'AGRIBANK',
            
            'ACB' => 'ACB',
            'Ngân hàng TMCP Á Châu' => 'ACB',
            
            'MB Bank' => 'MB',
            'MBBank' => 'MB',
            'Ngân hàng TMCP Quân đội' => 'MB',
            
            'VPBank' => 'VPB',
            'Ngân hàng TMCP Việt Nam Thịnh Vượng' => 'VPB',
            
            'TPBank' => 'TPB',
            'Ngân hàng TMCP Tiên Phong' => 'TPB',
            
            'Sacombank' => 'SACOMBANK',
            'Ngân hàng TMCP Sài Gòn Thương Tín' => 'SACOMBANK',
            
            'VIB' => 'VIB',
            'Ngân hàng TMCP Quốc tế Việt Nam' => 'VIB',
            
            'SHB' => 'SHB',
            'Ngân hàng TMCP Sài Gòn - Hà Nội' => 'SHB',
            
            'HDBank' => 'HDB',
            'Ngân hàng TMCP Phát triển TP.HCM' => 'HDB',
            
            'OCB' => 'OCB',
            'Ngân hàng TMCP Phương Đông' => 'OCB',
            
            'MSB' => 'MSB',
            'Ngân hàng TMCP Hàng Hải' => 'MSB',
            
            'VietABank' => 'VAB',
            'Ngân hàng TMCP Việt Á' => 'VAB',
            
            'SeABank' => 'SEAB',
            'Ngân hàng TMCP Đông Nam Á' => 'SEAB',
            
            'ABBANK' => 'ABB',
            'Ngân hàng TMCP An Bình' => 'ABB',
            
            'NamABank' => 'NAB',
            'Ngân hàng TMCP Nam Á' => 'NAB',
            
            'PGBank' => 'PGB',
            'Ngân hàng TMCP Xăng dầu Petrolimex' => 'PGB',
            
            'VietBank' => 'VIETBANK',
            'Ngân hàng TMCP Việt Nam Thương Tín' => 'VIETBANK',
            
            'BaoVietBank' => 'BVB',
            'Ngân hàng TMCP Bảo Việt' => 'BVB',
            
            'LienVietPostBank' => 'LPB',
            'Ngân hàng TMCP Bưu Điện Liên Việt' => 'LPB',
        ];
        
        // Tìm mã ngân hàng
        foreach ($bankMapping as $key => $code) {
            if (stripos($bankName, $key) !== false) {
                return $code;
            }
        }
        
        // Mặc định trả về VCB nếu không tìm thấy
        return 'VCB';
    }
    
    /**
     * Get all supported banks with codes
     * 
     * @return array
     */
    public function getSupportedBanks(): array
    {
        return [
            ['code' => 'VCB', 'name' => 'Vietcombank', 'full_name' => 'Ngân hàng TMCP Ngoại thương Việt Nam'],
            ['code' => 'TCB', 'name' => 'Techcombank', 'full_name' => 'Ngân hàng TMCP Kỹ thương Việt Nam'],
            ['code' => 'BIDV', 'name' => 'BIDV', 'full_name' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam'],
            ['code' => 'CTG', 'name' => 'Vietinbank', 'full_name' => 'Ngân hàng TMCP Công thương Việt Nam'],
            ['code' => 'AGRIBANK', 'name' => 'Agribank', 'full_name' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam'],
            ['code' => 'ACB', 'name' => 'ACB', 'full_name' => 'Ngân hàng TMCP Á Châu'],
            ['code' => 'MB', 'name' => 'MB Bank', 'full_name' => 'Ngân hàng TMCP Quân đội'],
            ['code' => 'VPB', 'name' => 'VPBank', 'full_name' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng'],
            ['code' => 'TPB', 'name' => 'TPBank', 'full_name' => 'Ngân hàng TMCP Tiên Phong'],
            ['code' => 'SACOMBANK', 'name' => 'Sacombank', 'full_name' => 'Ngân hàng TMCP Sài Gòn Thương Tín'],
            ['code' => 'VIB', 'name' => 'VIB', 'full_name' => 'Ngân hàng TMCP Quốc tế Việt Nam'],
            ['code' => 'SHB', 'name' => 'SHB', 'full_name' => 'Ngân hàng TMCP Sài Gòn - Hà Nội'],
            ['code' => 'HDB', 'name' => 'HDBank', 'full_name' => 'Ngân hàng TMCP Phát triển TP.HCM'],
            ['code' => 'OCB', 'name' => 'OCB', 'full_name' => 'Ngân hàng TMCP Phương Đông'],
            ['code' => 'MSB', 'name' => 'MSB', 'full_name' => 'Ngân hàng TMCP Hàng Hải'],
            ['code' => 'VAB', 'name' => 'VietABank', 'full_name' => 'Ngân hàng TMCP Việt Á'],
            ['code' => 'SEAB', 'name' => 'SeABank', 'full_name' => 'Ngân hàng TMCP Đông Nam Á'],
            ['code' => 'ABB', 'name' => 'ABBANK', 'full_name' => 'Ngân hàng TMCP An Bình'],
            ['code' => 'NAB', 'name' => 'NamABank', 'full_name' => 'Ngân hàng TMCP Nam Á'],
            ['code' => 'PGB', 'name' => 'PGBank', 'full_name' => 'Ngân hàng TMCP Xăng dầu Petrolimex'],
            ['code' => 'BVB', 'name' => 'BaoVietBank', 'full_name' => 'Ngân hàng TMCP Bảo Việt'],
            ['code' => 'LPB', 'name' => 'LienVietPostBank', 'full_name' => 'Ngân hàng TMCP Bưu Điện Liên Việt'],
        ];
    }
}
