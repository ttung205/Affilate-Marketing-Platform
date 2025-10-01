<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PublisherService;
use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalApprovalController extends Controller
{
    public function __construct(
        private PublisherService $publisherService
    ) {}

    /**
     * Hiển thị danh sách yêu cầu rút tiền cần phê duyệt
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'amount_min', 'amount_max']);
        $withdrawals = $this->publisherService->getWithdrawalsForAdmin($filters);
        $stats = $this->publisherService->getWithdrawalStats();
        
        return view('admin.withdrawals.index', compact('withdrawals', 'stats', 'filters'));
    }

    /**
     * Hiển thị chi tiết yêu cầu rút tiền
     */
    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['publisher', 'paymentMethod', 'processedBy', 'approvals.admin']);
        
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Phê duyệt yêu cầu rút tiền
     */
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->publisherService->approveWithdrawal(
                $withdrawal,
                Auth::user(),
                $request->notes
            );

            return redirect()->route('admin.withdrawals.index')
                ->with('success', 'Yêu cầu rút tiền đã được phê duyệt');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Từ chối yêu cầu rút tiền
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->publisherService->rejectWithdrawal(
                $withdrawal,
                Auth::user(),
                $request->reason
            );

            return redirect()->route('admin.withdrawals.index')
                ->with('success', 'Yêu cầu rút tiền đã bị từ chối');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Hoàn thành rút tiền
     */
    public function complete(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        try {
            $this->publisherService->completeWithdrawal(
                $withdrawal,
                Auth::user(),
                $request->transaction_reference
            );

            return redirect()->route('admin.withdrawals.index')
                ->with('success', 'Rút tiền đã được hoàn thành');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Xử lý hàng loạt
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'withdrawal_ids' => 'required|array|min:1',
            'withdrawal_ids.*' => 'exists:withdrawals,id',
            'reason' => 'required_if:action,reject|string|max:1000',
        ]);

        $withdrawals = Withdrawal::whereIn('id', $request->withdrawal_ids)
            ->where('status', 'pending')
            ->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($withdrawals as $withdrawal) {
            try {
                if ($request->action === 'approve') {
                    $this->publisherService->approveWithdrawal($withdrawal, Auth::user());
                } else {
                    $this->publisherService->rejectWithdrawal($withdrawal, Auth::user(), $request->reason);
                }
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        $message = "Đã xử lý {$successCount} yêu cầu thành công";
        if ($errorCount > 0) {
            $message .= ", {$errorCount} yêu cầu thất bại";
        }

        return redirect()->route('admin.withdrawals.index')
            ->with('success', $message);
    }

    /**
     * Lấy danh sách yêu cầu rút tiền (API)
     */
    public function getWithdrawals(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'amount_min', 'amount_max']);
        $withdrawals = $this->publisherService->getWithdrawalsForAdmin($filters);
        $stats = $this->publisherService->getWithdrawalStats();
        
        return response()->json([
            'success' => true,
            'data' => $withdrawals->items(), // Get array from paginated data
            'pagination' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
            ],
            'stats' => $stats
        ]);
    }

    /**
     * Lấy thống kê rút tiền (API)
     */
    public function getStats()
    {
        $stats = $this->publisherService->getWithdrawalStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Lấy chi tiết yêu cầu rút tiền (API)
     */
    public function getWithdrawal(Withdrawal $withdrawal)
    {
        $withdrawal->load(['publisher', 'paymentMethod', 'processedBy', 'approvals.admin']);
        
        return response()->json([
            'success' => true,
            'data' => $withdrawal
        ]);
    }

    /**
     * Phê duyệt yêu cầu rút tiền (API)
     */
    public function approveWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->publisherService->approveWithdrawal(
                $withdrawal,
                Auth::user(),
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu rút tiền đã được phê duyệt'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Từ chối yêu cầu rút tiền (API)
     */
    public function rejectWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->publisherService->rejectWithdrawal(
                $withdrawal,
                Auth::user(),
                $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu rút tiền đã bị từ chối'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Hoàn thành rút tiền (API)
     */
    public function completeWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        try {
            $this->publisherService->completeWithdrawal(
                $withdrawal,
                Auth::user(),
                $request->transaction_reference
            );

            return response()->json([
                'success' => true,
                'message' => 'Rút tiền đã được hoàn thành'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
