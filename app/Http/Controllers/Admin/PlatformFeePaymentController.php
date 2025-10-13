<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformFeePayment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformFeePaymentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Hiển thị danh sách thanh toán phí sàn
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        // Lấy số lượng theo trạng thái
        $pendingCount = PlatformFeePayment::where('status', 'pending')->count();
        $paidCount = PlatformFeePayment::where('status', 'paid')->count();
        $rejectedCount = PlatformFeePayment::where('status', 'rejected')->count();

        // Lấy danh sách thanh toán
        $query = PlatformFeePayment::with('shop');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.platform-fee-payments.index', compact(
            'payments',
            'status',
            'pendingCount',
            'paidCount',
            'rejectedCount'
        ));
    }

    /**
     * Duyệt thanh toán
     */
    public function approve(Request $request, $id)
    {
        $payment = PlatformFeePayment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Thanh toán này đã được xử lý!');
        }

        // Cập nhật trạng thái
        $payment->update([
            'status' => 'paid',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'admin_note' => $request->input('admin_note', 'Đã xác nhận thanh toán')
        ]);

        // Gửi thông báo cho shop
        $shop = User::find($payment->shop_id);
        if ($shop) {
            $this->notificationService->sendCustomNotification($shop, [
                'title' => 'Thanh toán phí sàn được duyệt',
                'message' => 'Thanh toán phí sàn ' . number_format((float)$payment->fee_amount, 0, ',', '.') . ' VNĐ của bạn đã được admin xác nhận.',
                'icon' => 'fas fa-check-circle',
                'color' => 'green',
                'action_url' => route('shop.platform-fee.index'),
                'action_text' => 'Xem chi tiết',
            ]);
        }

        return redirect()->route('admin.platform-fee-payments.index', ['status' => 'paid'])
            ->with('success', 'Đã duyệt thanh toán và gửi thông báo cho shop!');
    }

    /**
     * Xác minh thanh toán (alias của approve)
     */
    public function verify(Request $request, $id)
    {
        return $this->approve($request, $id);
    }

    /**
     * Hiển thị chi tiết thanh toán
     */
    public function show($id)
    {
        $payment = PlatformFeePayment::with('shop')->findOrFail($id);
        
        return view('admin.platform-fee-payments.show', compact('payment'));
    }

    /**
     * Từ chối thanh toán
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:500'
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối',
            'admin_note.max' => 'Lý do từ chối không được quá 500 ký tự'
        ]);

        $payment = PlatformFeePayment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Thanh toán này đã được xử lý!');
        }

        // Cập nhật trạng thái
        $payment->update([
            'status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'admin_note' => $request->input('admin_note')
        ]);

        // Gửi thông báo cho shop
        $shop = User::find($payment->shop_id);
        if ($shop) {
            $this->notificationService->sendCustomNotification($shop, [
                'title' => 'Thanh toán phí sàn bị từ chối',
                'message' => 'Thanh toán phí sàn của bạn bị từ chối. Lý do: ' . $request->input('admin_note'),
                'icon' => 'fas fa-times-circle',
                'color' => 'red',
                'action_url' => route('shop.platform-fee.index'),
                'action_text' => 'Xem chi tiết',
            ]);
        }

        return redirect()->route('admin.platform-fee-payments.index', ['status' => 'rejected'])
            ->with('success', 'Đã từ chối thanh toán và gửi thông báo cho shop!');
    }
}

