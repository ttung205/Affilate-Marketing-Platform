<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Services\PublisherService;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    public function __construct(
        private PublisherService $publisherService
    ) {}

    /**
     * Hiển thị danh sách tài khoản thanh toán
     */
    public function index()
    {
        $publisher = Auth::user();
        $paymentMethods = $this->publisherService->getPaymentMethods($publisher);
        $supportedBanks = $this->publisherService->getSupportedBanks();
        
        return view('publisher.payment-methods.index', compact('paymentMethods', 'supportedBanks'));
    }

    /**
     * Hiển thị form tạo tài khoản thanh toán
     */
    public function create()
    {
        $supportedBanks = $this->publisherService->getSupportedBanks();
        
        return view('publisher.payment-methods.create', compact('supportedBanks'));
    }

    /**
     * Tạo tài khoản thanh toán mới
     */
    public function store(Request $request)
    {
        Log::info('Payment method creation started', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        // Log raw request data
        Log::info('Raw request data', [
            'all' => $request->all(),
            'input' => $request->input(),
            'json' => $request->json()->all(),
            'content_type' => $request->header('Content-Type')
        ]);

        $request->validate([
            'type' => 'required|in:bank_transfer',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'nullable|string|max:10',
            'branch_name' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ], [
            'type.required' => 'Vui lòng chọn loại tài khoản thanh toán',
            'type.in' => 'Hiện tại chỉ hỗ trợ tài khoản ngân hàng',
            'account_name.required' => 'Vui lòng nhập tên chủ tài khoản',
            'account_number.required' => 'Vui lòng nhập số tài khoản',
            'bank_name.required' => 'Vui lòng chọn ngân hàng',
        ]);

        Log::info('Payment method validation passed', [
            'user_id' => Auth::id(),
            'type' => $request->type,
            'account_name' => $request->account_name
        ]);

        try {
            $publisher = Auth::user();
            $data = $request->all();
            
            // Xử lý is_default: convert "on" to true, missing to false
            $data['is_default'] = $request->has('is_default') ? true : false;
            
            Log::info('Processed payment method data', [
                'user_id' => Auth::id(),
                'is_default' => $data['is_default'],
                'original_is_default' => $request->input('is_default')
            ]);
            
            // Kiểm tra số lượng payment methods hiện tại
            $currentCount = $publisher->paymentMethods()->count();
            Log::info('Current payment methods count', [
                'user_id' => $publisher->id,
                'current_count' => $currentCount
            ]);
            
            $paymentMethod = $this->publisherService->createPaymentMethod(
                $publisher,
                $data
            );

            Log::info('Payment method created successfully', [
                'payment_method_id' => $paymentMethod->id,
                'user_id' => $publisher->id,
                'type' => $paymentMethod->type
            ]);

            return redirect()->route('publisher.payment-methods.index')
                ->with('success', 'Tài khoản thanh toán đã được thêm thành công');
                
        } catch (\Exception $e) {
            Log::error('Payment method creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Hiển thị form chỉnh sửa tài khoản thanh toán
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        // Kiểm tra quyền sở hữu
        if ($paymentMethod->publisher_id !== Auth::id()) {
            abort(403);
        }

        $supportedBanks = $this->publisherService->getSupportedBanks();
        
        return view('publisher.payment-methods.edit', compact('paymentMethod', 'supportedBanks'));
    }

    /**
     * Cập nhật tài khoản thanh toán
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        // Kiểm tra quyền sở hữu
        if ($paymentMethod->publisher_id !== Auth::id()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này'
                ], 403);
            }
            abort(403);
        }

        $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'nullable|string|max:10',
            'branch_name' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ], [
            'account_name.required' => 'Vui lòng nhập tên chủ tài khoản',
            'account_number.required' => 'Vui lòng nhập số tài khoản',
            'bank_name.required' => 'Vui lòng chọn ngân hàng',
        ]);

        try {
            $this->publisherService->updatePaymentMethod($paymentMethod, $request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tài khoản thanh toán đã được cập nhật thành công'
                ]);
            }

            return redirect()->route('publisher.payment-methods.index')
                ->with('success', 'Tài khoản thanh toán đã được cập nhật thành công');
                
        } catch (\Exception $e) {
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
     * Xóa tài khoản thanh toán
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Kiểm tra quyền sở hữu
        if ($paymentMethod->publisher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này'
            ], 403);
        }

        try {
            $this->publisherService->deletePaymentMethod($paymentMethod);

            return response()->json([
                'success' => true,
                'message' => 'Tài khoản thanh toán đã được xóa thành công'
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
     * Đặt làm tài khoản mặc định
     */
    public function setDefault(PaymentMethod $paymentMethod)
    {
        // Kiểm tra quyền sở hữu
        if ($paymentMethod->publisher_id !== Auth::id()) {
            abort(403);
        }

        try {
            $this->publisherService->setAsDefaultPaymentMethod($paymentMethod);

            return response()->json([
                'success' => true,
                'message' => 'Đã đặt làm tài khoản mặc định'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Lấy danh sách tài khoản thanh toán (API)
     */
    public function getPaymentMethods()
    {
        try {
            $publisher = Auth::user();
            $paymentMethods = $this->publisherService->getPaymentMethods($publisher);
            
            return response()->json([
                'success' => true,
                'payment_methods' => $paymentMethods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách tài khoản thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy tài khoản thanh toán mặc định (API)
     */
    public function getDefaultPaymentMethod()
    {
        $publisher = Auth::user();
        $defaultMethod = $publisher->defaultPaymentMethod;
        
        return response()->json([
            'success' => true,
            'data' => $defaultMethod
        ]);
    }

    /**
     * Tính phí rút tiền (API)
     */
    public function calculateFee(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100000|max:5000000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
        
        // Kiểm tra quyền sở hữu
        if ($paymentMethod->publisher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản thanh toán không hợp lệ'
            ], 403);
        }

        $feeData = $this->publisherService->calculateWithdrawalFee($paymentMethod, $request->amount);
        
        return response()->json([
            'success' => true,
            'data' => $feeData
        ]);
    }

    /**
     * Lấy danh sách ngân hàng hỗ trợ (API)
     */
    public function getSupportedBanks()
    {
        $banks = $this->publisherService->getSupportedBanks();
        
        return response()->json([
            'success' => true,
            'data' => $banks
        ]);
    }
}
