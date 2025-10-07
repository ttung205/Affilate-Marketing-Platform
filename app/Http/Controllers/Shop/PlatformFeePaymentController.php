<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\PlatformFeeSetting;
use App\Models\PlatformFeePayment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformFeePaymentController extends Controller
{
    /**
     * Hiển thị trang thanh toán phí sàn
     */
    public function index()
    {
        $shop = Auth::user();
        
        // Lấy phí sàn hiện tại
        $currentFee = PlatformFeeSetting::getCurrentFee();
        
        if (!$currentFee) {
            return view('shop.platform-fee.index', [
                'currentFee' => null,
                'totalProductsValue' => 0,
                'feeAmount' => 0,
                'totalDebt' => 0,
                'payments' => collect()
            ]);
        }
        
        // Tính tổng giá trị sản phẩm của shop
        $totalProductsValue = Product::where('user_id', $shop->id)
            ->where('is_active', true)
            ->sum('price');
        
        // Tính số tiền phí sàn
        $feeAmount = ($totalProductsValue * $currentFee->fee_percentage) / 100;
        
        // Lấy tổng đã thanh toán
        $totalPaid = PlatformFeePayment::where('shop_id', $shop->id)
            ->where('status', 'paid')
            ->sum('fee_amount');
        
        // Tính tổng nợ
        $totalDebt = $feeAmount - $totalPaid;
        
        // Lấy lịch sử thanh toán
        $payments = PlatformFeePayment::where('shop_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('shop.platform-fee.index', compact(
            'currentFee',
            'totalProductsValue',
            'feeAmount',
            'totalDebt',
            'payments'
        ));
    }

    /**
     * Tạo QR code thanh toán
     */
    public function generateQR(Request $request)
    {
        $shop = Auth::user();
        $currentFee = PlatformFeeSetting::getCurrentFee();
        
        if (!$currentFee) {
            return back()->with('error', 'Chưa có cài đặt phí sàn!');
        }
        
        // Tính tổng giá trị sản phẩm
        $totalProductsValue = Product::where('user_id', $shop->id)
            ->where('is_active', true)
            ->sum('price');
        
        // Tính số tiền phí sàn
        $feeAmount = ($totalProductsValue * $currentFee->fee_percentage) / 100;
        
        // Lấy tổng đã thanh toán
        $totalPaid = PlatformFeePayment::where('shop_id', $shop->id)
            ->where('status', 'paid')
            ->sum('fee_amount');
        
        // Tính tổng nợ
        $totalDebt = $feeAmount - $totalPaid;
        
        if ($totalDebt <= 0) {
            return back()->with('error', 'Bạn không có khoản nợ phí sàn nào!');
        }
        
        // Tạo QR code cho MBBank
        $qrCode = $this->generateMBBankQR($totalDebt, $shop->id);
        
        // Lưu dữ liệu vào session (chưa lưu vào database)
        $request->session()->put('pending_payment', [
            'shop_id' => $shop->id,
            'total_products_value' => $totalProductsValue,
            'fee_percentage' => $currentFee->fee_percentage,
            'fee_amount' => $totalDebt,
            'qr_code' => $qrCode
        ]);
        
        return view('shop.platform-fee.payment', compact('qrCode', 'totalDebt'));
    }

    /**
     * Sinh mã QR code cho MBBank
     */
    private function generateMBBankQR($amount, $shopId)
    {
        $accountNumber = '0375401903';
        $bankCode = 'MB'; // MBBank
        $accountName = 'TTUNG PLATFORM';
        
        // Format số tiền (làm tròn không có số thập phân)
        $amountFormatted = number_format($amount, 0, '', '');
        
        // Tạo nội dung chuyển khoản
        $content = "PHI SAN SHOP {$shopId}";
        
        // Tạo QR code theo chuẩn VietQR
        // Format: https://img.vietqr.io/image/{BANK}-{ACCOUNT_NO}-{TEMPLATE}.png?amount={AMOUNT}&addInfo={DESCRIPTION}
        $qrUrl = "https://img.vietqr.io/image/{$bankCode}-{$accountNumber}-compact.png";
        $qrUrl .= "?amount={$amountFormatted}";
        $qrUrl .= "&addInfo=" . urlencode($content);
        $qrUrl .= "&accountName=" . urlencode($accountName);
        
        return $qrUrl;
    }

    /**
     * Xác nhận đã thanh toán (dành cho shop tự xác nhận)
     */
    public function confirmPayment(Request $request)
    {
        // Lấy dữ liệu từ session
        $pendingPayment = $request->session()->get('pending_payment');
        
        if (!$pendingPayment) {
            return redirect()->route('shop.platform-fee.index')
                ->with('error', 'Không tìm thấy thông tin thanh toán!');
        }
        
        // Kiểm tra quyền
        if ($pendingPayment['shop_id'] !== Auth::id()) {
            abort(403);
        }
        
        // Tạo payment record
        $payment = PlatformFeePayment::create([
            'shop_id' => $pendingPayment['shop_id'],
            'total_products_value' => $pendingPayment['total_products_value'],
            'fee_percentage' => $pendingPayment['fee_percentage'],
            'fee_amount' => $pendingPayment['fee_amount'],
            'status' => 'pending',
            'qr_code' => $pendingPayment['qr_code'],
            'note' => 'Shop đã xác nhận thanh toán, chờ admin duyệt'
        ]);
        
        // Xóa dữ liệu khỏi session
        $request->session()->forget('pending_payment');
        
        return redirect()->route('shop.platform-fee.index')
            ->with('success', 'Đã ghi nhận xác nhận thanh toán! Vui lòng chờ admin duyệt.');
    }
}
