<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Services\PublisherService;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    public function __construct(
        private PublisherService $publisherService
    ) {}

    /**
     * Hiển thị trang rút tiền
     */
    public function index()
    {
        $publisher = Auth::user();
        $withdrawals = $publisher->withdrawals()
            ->with('paymentMethod')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $paymentMethods = $this->publisherService->getPaymentMethods($publisher);
        $wallet = $publisher->getOrCreateWallet();
        
        return view('publisher.withdrawal.index', compact('withdrawals', 'paymentMethods', 'wallet'));
    }

    /**
     * Hiển thị form tạo yêu cầu rút tiền
     */
    public function create()
    {
        $publisher = Auth::user();
        $paymentMethods = $this->publisherService->getPaymentMethods($publisher);
        
        if (empty($paymentMethods)) {
            return redirect()->route('publisher.payment-methods.index')
                ->with('warning', 'Vui lòng thêm phương thức thanh toán trước khi rút tiền');
        }
        
        return view('publisher.withdrawal.create', compact('paymentMethods'));
    }

    /**
     * Tạo yêu cầu rút tiền
     */
    public function store(Request $request)
    {
        Log::info('Withdrawal request started', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $request->validate([
            'amount' => 'required|numeric|min:10000|max:5000000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ], [
            'amount.min' => 'Số tiền rút tối thiểu là 10,000 VNĐ',
            'amount.max' => 'Số tiền rút tối đa là 5,000,000 VNĐ',
            'payment_method_id.required' => 'Vui lòng chọn phương thức thanh toán',
            'payment_method_id.exists' => 'Phương thức thanh toán không hợp lệ',
        ]);

        Log::info('Withdrawal validation passed', [
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'payment_method_id' => $request->payment_method_id
        ]);

        try {
            $publisher = Auth::user();
            
            // Kiểm tra quyền sở hữu payment method
            $paymentMethod = \App\Models\PaymentMethod::find($request->payment_method_id);
            if (!$paymentMethod || $paymentMethod->publisher_id !== $publisher->id) {
                Log::error('Payment method ownership check failed', [
                    'user_id' => $publisher->id,
                    'payment_method_id' => $request->payment_method_id,
                    'payment_method_owner' => $paymentMethod ? $paymentMethod->publisher_id : 'not_found'
                ]);
                throw new \Exception('Phương thức thanh toán không hợp lệ');
            }

            Log::info('Payment method ownership verified', [
                'user_id' => $publisher->id,
                'payment_method_id' => $paymentMethod->id
            ]);

            $withdrawal = $this->publisherService->createWithdrawal(
                $publisher,
                $request->only(['amount', 'payment_method_id'])
            );

            Log::info('Withdrawal created successfully', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $publisher->id,
                'amount' => $withdrawal->amount
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Yêu cầu rút tiền đã được gửi thành công',
                    'data' => $withdrawal
                ]);
            }

            return redirect()->route('publisher.withdrawal.index')
                ->with('success', 'Yêu cầu rút tiền đã được gửi thành công');
                
        } catch (\Exception $e) {
            Log::error('Withdrawal creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['error' => $e->getMessage()]
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Hiển thị chi tiết yêu cầu rút tiền
     */
    public function show(Withdrawal $withdrawal)
    {
        // Kiểm tra quyền truy cập
        if ($withdrawal->publisher_id !== Auth::id()) {
            abort(403);
        }

        $withdrawal->load(['paymentMethod', 'processedBy', 'approvals.admin']);
        
        return view('publisher.withdrawal.show', compact('withdrawal'));
    }

    /**
     * Hủy yêu cầu rút tiền
     */
    public function cancel(Withdrawal $withdrawal)
    {
        // Kiểm tra quyền truy cập
        if ($withdrawal->publisher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này'
            ], 403);
        }

        try {
            $this->publisherService->cancelWithdrawal($withdrawal, Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu rút tiền đã được hủy'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => ['error' => $e->getMessage()]
            ], 422);
        }
    }

    /**
     * Lấy danh sách yêu cầu rút tiền (API)
     */
    public function getWithdrawals(Request $request)
    {
        $publisher = Auth::user();
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = $publisher->withdrawals()->with('paymentMethod');
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        $withdrawals = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Transform data for frontend
        $transformedWithdrawals = $withdrawals->getCollection()->map(function ($withdrawal) {
            return [
                'id' => $withdrawal->id,
                'amount' => $withdrawal->amount,
                'fee' => $withdrawal->fee,
                'net_amount' => $withdrawal->net_amount,
                'status' => $withdrawal->status,
                'status_label' => $withdrawal->status_label,
                'created_at' => $withdrawal->created_at,
                'processed_at' => $withdrawal->processed_at,
                'completed_at' => $withdrawal->completed_at,
                'rejection_reason' => $withdrawal->rejection_reason,
                'transaction_reference' => $withdrawal->transaction_reference,
                'can_be_cancelled' => $withdrawal->canBeCancelled(),
                'payment_method' => [
                    'id' => $withdrawal->paymentMethod->id,
                    'type' => $withdrawal->paymentMethod->type,
                    'type_label' => $withdrawal->paymentMethod->type_label,
                    'account_name' => $withdrawal->paymentMethod->account_name,
                    'account_number' => $withdrawal->paymentMethod->account_number,
                    'masked_account_number' => $withdrawal->paymentMethod->masked_account_number,
                    'bank_name' => $withdrawal->paymentMethod->bank_name,
                    'icon' => $withdrawal->paymentMethod->icon,
                    'color' => $withdrawal->paymentMethod->color,
                ]
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'data' => $transformedWithdrawals,
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
                'from' => $withdrawals->firstItem(),
                'to' => $withdrawals->lastItem(),
            ]
        ]);
    }

    /**
     * Lấy chi tiết yêu cầu rút tiền (API)
     */
    public function getWithdrawal(Withdrawal $withdrawal)
    {
        // Kiểm tra quyền truy cập
        if ($withdrawal->publisher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ], 403);
        }

        $withdrawal->load(['paymentMethod', 'processedBy', 'approvals.admin']);
        
        return response()->json([
            'success' => true,
            'data' => $withdrawal
        ]);
    }

    /**
     * Tính phí rút tiền
     */
    public function calculateFee(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100000|max:5000000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $paymentMethod = \App\Models\PaymentMethod::findOrFail($request->payment_method_id);
        
        // Kiểm tra quyền sở hữu
        if ($paymentMethod->publisher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Phương thức thanh toán không hợp lệ'
            ], 403);
        }

        $feeData = $this->publisherService->calculateWithdrawalFee($paymentMethod, $request->amount);
        
        return response()->json([
            'success' => true,
            'data' => $feeData
        ]);
    }

    /**
     * Lấy thống kê rút tiền
     */
    public function getStats()
    {
        $publisher = Auth::user();
        
        $stats = [
            'total_withdrawals' => $publisher->withdrawals()->count(),
            'total_amount' => $publisher->withdrawals()->sum('amount'),
            'pending_amount' => $publisher->withdrawals()->where('status', 'pending')->sum('amount'),
            'completed_amount' => $publisher->withdrawals()->where('status', 'completed')->sum('amount'),
            'this_month' => [
                'count' => $publisher->withdrawals()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'amount' => $publisher->withdrawals()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'),
            ],
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
