<?php

namespace App\Services;

use App\Models\User;
use App\Models\Click;
use App\Models\Conversion;
use App\Models\Transaction;
use App\Models\PublisherWallet;
use App\Models\PaymentMethod;
use App\Models\Withdrawal;
use App\Models\WithdrawalApproval;
use App\Notifications\WithdrawalRequestNotification;
use App\Notifications\WithdrawalStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class PublisherService
{
    /**
     * ========================================
     * COMMISSION MANAGEMENT
     * ========================================
     */

    /**
     * Xử lý hoa hồng từ click
     */
    public function processClickCommission(Click $click): void
    {
        try {
            DB::beginTransaction();

            $publisher = $click->publisher;
            $affiliateLink = $click->affiliateLink;
            
            // Tính hoa hồng từ click (CPC)
            $clickCommission = $this->calculateClickCommission($affiliateLink);
            
            if ($clickCommission > 0) {
                // Cộng vào ví
                $this->addToWallet($publisher, $clickCommission, 'commission_earned', [
                    'type' => 'click_commission',
                    'click_id' => $click->id,
                    'affiliate_link_id' => $affiliateLink->id,
                    'cpc' => $affiliateLink->getCostPerClickAttribute(),
                    'description' => "Hoa hồng từ click - Link: {$affiliateLink->tracking_code}"
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing click commission", [
                'click_id' => $click->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Xử lý hoa hồng từ conversion
     */
    public function processConversionCommission(Conversion $conversion): void
    {
        if ($conversion->is_commission_processed) {
            return;
        }

        if (!$conversion->isApproved()) {
            return;
        }

        try {
            DB::beginTransaction();

            $publisher = $conversion->publisher;
            $affiliateLink = $conversion->affiliateLink;
            
            $conversionCommission = (float) $conversion->commission;
            
            if ($conversionCommission > 0) {
                $this->addToWallet($publisher, $conversionCommission, 'commission_earned', [
                    'type' => 'conversion_commission',
                    'conversion_id' => $conversion->id,
                    'affiliate_link_id' => $affiliateLink->id,
                    'order_amount' => $conversion->amount,
                    'commission_rate' => $conversion->getCommissionRateAttribute(),
                    'description' => "Hoa hồng từ conversion - Order: {$conversion->order_id}"
                ]);

                $conversion->update([
                    'is_commission_processed' => true,
                    'commission_processed_at' => now(),
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing conversion commission", [
                'conversion_id' => $conversion->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Tính hoa hồng từ click
     */
    private function calculateClickCommission($affiliateLink): float
    {
        return $affiliateLink->getCostPerClickAttribute();
    }

    /**
     * Lấy tổng hoa hồng đã kiếm được của publisher
     */
    public function getTotalEarnings(User $publisher): array
    {
        // Sync dữ liệu từ transactions vào wallet trước
        $this->syncWalletFromTransactions($publisher);
        
        // Tính hoa hồng từ transactions (chính xác)
        $clickCommission = $publisher->transactions()
            ->where('type', 'commission_earned')
            ->where('reference_type', 'click_commission')
            ->sum('amount');
            
        $conversionCommission = $publisher->transactions()
            ->where('type', 'commission_earned')
            ->where('reference_type', 'conversion_commission')
            ->sum('amount');
            
        $totalEarnings = $clickCommission + $conversionCommission;

        return [
            'click_commission' => $clickCommission,
            'conversion_commission' => $conversionCommission,
            'total_earnings' => $totalEarnings,
            'available_balance' => $publisher->getAvailableBalance(),
            'pending_balance' => $publisher->getOrCreateWallet()->pending_balance,
        ];
    }

    /**
     * ========================================
     * WALLET MANAGEMENT
     * ========================================
     */

    /**
     * Đảm bảo tính toán hoa hồng chính xác
     */
    public function ensureAccurateCommissionCalculation(User $publisher): void
    {
        // Sync wallet từ transactions để đảm bảo dữ liệu chính xác
        $this->syncWalletFromTransactions($publisher);
    }

    /**
     * Lấy dữ liệu ví cho publisher
     */
    public function getWalletData(User $publisher): array
    {
        // Đảm bảo tính toán chính xác trước khi lấy dữ liệu
        $this->ensureAccurateCommissionCalculation($publisher);
        
        $wallet = $publisher->getOrCreateWallet();
        $earnings = $this->getTotalEarnings($publisher);
        
        return [
            'wallet' => $wallet,
            'totalEarnings' => $earnings['total_earnings'],
            'clickEarnings' => $earnings['click_commission'],
            'conversionEarnings' => $earnings['conversion_commission'],
            'availableBalance' => $earnings['available_balance'],
            'pendingBalance' => $earnings['pending_balance'],
            'withdrawalFees' => $this->getWithdrawalFees($publisher),
            'monthlyStats' => $this->getMonthlyStats($publisher),
            'withdrawalHistory' => $this->getWithdrawalHistory($publisher),
            'recentTransactions' => $this->getRecentTransactions($publisher),
            'paymentMethods' => $this->getPaymentMethods($publisher),
        ];
    }

    /**
     * Cộng tiền vào ví
     */
    private function addToWallet(User $publisher, float $amount, string $type, array $metadata = []): void
    {
        // Lấy hoặc tạo ví
        $wallet = $publisher->getOrCreateWallet();
        
        // Cộng trực tiếp vào available balance (không cần hold period)
        $wallet->increment('balance', $amount);
        $wallet->increment('total_earned', $amount);
        
        // Tạo transaction record
        Transaction::create([
            'publisher_id' => $publisher->id,
            'type' => $type,
            'amount' => $amount,
            'status' => 'completed',
            'description' => $metadata['description'] ?? 'Hoa hồng từ affiliate',
            'reference_type' => $metadata['type'] ?? 'commission',
            'reference_id' => $metadata['click_id'] ?? $metadata['conversion_id'] ?? null,
            'metadata' => $metadata,
            'processed_at' => now(),
        ]);
    }

    /**
     * Sync dữ liệu từ transactions vào wallet
     */
    public function syncWalletFromTransactions(User $publisher): void
    {
        try {
            $wallet = $publisher->getOrCreateWallet();
            
            // Tính tổng hoa hồng từ click và conversion
            $clickCommission = $publisher->getClickCommissionAttribute();
            $conversionCommission = $publisher->getTotalCommissionAttribute();
            $totalEarned = $clickCommission + $conversionCommission;
            
            // Tính tổng đã rút (lấy giá trị tuyệt đối vì withdrawal đã được lưu là số âm)
            $totalWithdrawn = abs(Transaction::where('publisher_id', $publisher->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount'));
            
            // Số dư khả dụng = Tổng hoa hồng - Tổng đã rút
            $availableBalance = max(0, $totalEarned - $totalWithdrawn);
            
            // Cập nhật wallet
            $wallet->update([
                'total_earned' => $totalEarned,
                'total_withdrawn' => $totalWithdrawn,
                'pending_balance' => 0, // Không cần hold period
                'balance' => $availableBalance,
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error syncing wallet from transactions", [
                'publisher_id' => $publisher->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Kiểm tra có thể rút tiền không
     */
    public function canWithdraw(User $publisher, float $amount): array
    {
        $wallet = $publisher->getOrCreateWallet();
        $errors = [];
        
        // Kiểm tra số dư
        if (!$wallet->canWithdraw($amount)) {
            $errors[] = 'Số dư không đủ';
        }
        
        // Kiểm tra giới hạn hàng ngày
        if ($amount > $wallet->withdrawal_limit) {
            $errors[] = 'Vượt quá giới hạn rút tiền hàng ngày';
        }
        
        // Kiểm tra số tiền tối thiểu
        if ($amount < 100000) {
            $errors[] = 'Số tiền rút tối thiểu là 100,000 VNĐ';
        }
        
        // Kiểm tra số tiền tối đa
        if ($amount > 5000000) {
            $errors[] = 'Số tiền rút tối đa là 5,000,000 VNĐ';
        }
        
        return [
            'can_withdraw' => empty($errors),
            'errors' => $errors,
            'available_balance' => $wallet->balance,
            'withdrawal_limit' => $wallet->withdrawal_limit,
        ];
    }

    /**
     * ========================================
     * PAYMENT METHOD MANAGEMENT
     * ========================================
     */

    /**
     * Tạo phương thức thanh toán mới
     */
    public function createPaymentMethod(User $publisher, array $data): PaymentMethod
    {
        $this->validatePaymentMethodData(array_merge($data, ['publisher_id' => $publisher->id]));

        return DB::transaction(function () use ($publisher, $data) {
            // Nếu đây là phương thức mặc định, bỏ mặc định của các phương thức khác
            if ($data['is_default'] ?? false) {
                $publisher->paymentMethods()->update(['is_default' => false]);
            }

            $paymentMethod = PaymentMethod::create([
                'publisher_id' => $publisher->id,
                'type' => $data['type'],
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'bank_name' => $data['bank_name'] ?? null,
                'bank_code' => $data['bank_code'] ?? null,
                'branch_name' => $data['branch_name'] ?? null,
                'is_default' => $data['is_default'] ?? false,
            ]);

            return $paymentMethod;
        });
    }

    /**
     * Cập nhật phương thức thanh toán
     */
    public function updatePaymentMethod(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $this->validatePaymentMethodData($data, $paymentMethod->id);

        return DB::transaction(function () use ($paymentMethod, $data) {
            // Nếu đây là phương thức mặc định, bỏ mặc định của các phương thức khác
            if ($data['is_default'] ?? false) {
                $paymentMethod->publisher->paymentMethods()
                    ->where('id', '!=', $paymentMethod->id)
                    ->update(['is_default' => false]);
            }

            $paymentMethod->update([
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'bank_name' => $data['bank_name'] ?? null,
                'bank_code' => $data['bank_code'] ?? null,
                'branch_name' => $data['branch_name'] ?? null,
                'is_default' => $data['is_default'] ?? false,
            ]);

            return $paymentMethod;
        });
    }

    /**
     * Xóa phương thức thanh toán
     */
    public function deletePaymentMethod(PaymentMethod $paymentMethod): bool
    {
        // Kiểm tra xem có đang được sử dụng trong withdrawal không
        if ($paymentMethod->withdrawals()->whereIn('status', ['pending', 'approved', 'processing'])->exists()) {
            throw new \Exception('Không thể xóa phương thức thanh toán đang được sử dụng');
        }

        return DB::transaction(function () use ($paymentMethod) {
            $paymentMethod->delete();
            return true;
        });
    }

    /**
     * Đặt làm phương thức mặc định
     */
    public function setAsDefaultPaymentMethod(PaymentMethod $paymentMethod): PaymentMethod
    {
        return DB::transaction(function () use ($paymentMethod) {
            // Bỏ mặc định của các phương thức khác
            $paymentMethod->publisher->paymentMethods()
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);

            // Đặt làm mặc định
            $paymentMethod->update(['is_default' => true]);

            return $paymentMethod;
        });
    }

    /**
     * Lấy danh sách phương thức thanh toán của publisher
     */
    public function getPaymentMethods(User $publisher): array
    {
        try {
            return $publisher->paymentMethods()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'type' => $method->type,
                        'type_label' => $method->type_label,
                        'display_name' => $method->display_name,
                        'account_name' => $method->account_name,
                        'account_number' => $method->account_number,
                        'masked_account_number' => $method->masked_account_number,
                        'bank_name' => $method->bank_name,
                        'bank_code' => $method->bank_code,
                        'branch_name' => $method->branch_name,
                        'is_default' => $method->is_default,
                        'icon' => $method->icon ?? 'fas fa-credit-card',
                        'color' => $method->color ?? 'gray',
                        'fee_rate' => $method->fee_rate,
                        'created_at' => $method->created_at,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting payment methods', [
                'publisher_id' => $publisher->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Tính phí rút tiền
     */
    public function calculateWithdrawalFee(PaymentMethod $paymentMethod, float $amount): array
    {
        $fee = $paymentMethod->calculateFee($amount);
        $netAmount = $amount - $fee;

        return [
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'fee_rate' => $paymentMethod->fee_rate,
            'fee_percentage' => round($paymentMethod->fee_rate * 100, 2),
        ];
    }

    /**
     * ========================================
     * WITHDRAWAL MANAGEMENT
     * ========================================
     */



    /**
     * Create withdrawal after OTP verification (from session data)
     */
    public function createWithdrawal(User $publisher, array $data): Withdrawal
    {
        // Validate withdrawal
        $validation = $this->canWithdraw($publisher, $data['amount']);
        if (!$validation['can_withdraw']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);
        
        // Tính phí
        $fee = $paymentMethod->calculateFee((float) $data['amount']);
        $netAmount = (float) $data['amount'] - $fee;

        return DB::transaction(function () use ($publisher, $data, $paymentMethod, $fee, $netAmount) {
            // Trừ tiền từ ví
            $wallet = $publisher->getOrCreateWallet();
            $wallet->balance -= (float) $data['amount'];
            $wallet->save();

            // Tạo withdrawal record với status pending (chờ admin duyệt)
            $withdrawal = Withdrawal::create([
                'publisher_id' => $publisher->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $data['amount'],
                'fee' => $fee,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'payment_method_type' => $paymentMethod->type,
                'payment_details' => [
                    'account_name' => $paymentMethod->account_name,
                    'account_number' => $paymentMethod->account_number,
                    'bank_name' => $paymentMethod->bank_name,
                    'bank_code' => $paymentMethod->bank_code,
                    'branch_name' => $paymentMethod->branch_name,
                ],
            ]);

            // Tạo transaction record
            Transaction::create([
                'publisher_id' => $publisher->id,
                'type' => 'withdrawal',
                'amount' => -(float) $data['amount'],
                'description' => "Rút tiền #{$withdrawal->id}",
                'reference_id' => $withdrawal->id,
                'reference_type' => 'withdrawal',
            ]);

            // Gửi thông báo cho admin
            $withdrawal->load('publisher'); // Load relationship để tránh N+1 query
            $admins = User::where('role', 'admin')->get();
            
            try {
                Notification::send($admins, new WithdrawalRequestNotification($withdrawal));
            } catch (\Exception $e) {
                Log::error("Failed to send withdrawal notification to admins", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return $withdrawal;
        });
    }


    /**
     * Phê duyệt yêu cầu rút tiền
     */
    public function approveWithdrawal(Withdrawal $withdrawal, User $admin, string $notes = null): Withdrawal
    {
        if (!$withdrawal->isPending()) {
            throw new \Exception('Chỉ có thể phê duyệt yêu cầu đang chờ');
        }

        return DB::transaction(function () use ($withdrawal, $admin, $notes) {
            // Cập nhật status
            $withdrawal->update([
                'status' => 'approved',
                'processed_by' => $admin->id,
                'processed_at' => now(),
                'admin_notes' => $notes,
            ]);

            // Tạo approval record
            WithdrawalApproval::create([
                'withdrawal_id' => $withdrawal->id,
                'admin_id' => $admin->id,
                'action' => 'approve',
                'notes' => $notes,
            ]);

            // Cập nhật transaction status
            if ($withdrawal->transactions()->exists()) {
                $withdrawal->transactions()->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);
            }

            // Gửi thông báo
            $this->sendWithdrawalStatusNotification($withdrawal, 'approved');

            return $withdrawal;
        });
    }

    /**
     * Từ chối yêu cầu rút tiền
     */
    public function rejectWithdrawal(Withdrawal $withdrawal, User $admin, string $reason): Withdrawal
    {
        if (!$withdrawal->isPending()) {
            throw new \Exception('Chỉ có thể từ chối yêu cầu đang chờ');
        }

        return DB::transaction(function () use ($withdrawal, $admin, $reason) {
            // Cập nhật status
            $withdrawal->update([
                'status' => 'rejected',
                'processed_by' => $admin->id,
                'processed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Tạo approval record
            WithdrawalApproval::create([
                'withdrawal_id' => $withdrawal->id,
                'admin_id' => $admin->id,
                'action' => 'reject',
                'notes' => $reason,
            ]);

            // Hoàn lại số dư
            $withdrawal->publisher->getOrCreateWallet()->increment('balance', $withdrawal->amount);

            // Cập nhật transaction status
            if ($withdrawal->transactions()->exists()) {
                $withdrawal->transactions()->update([
                    'status' => 'failed',
                    'processed_at' => now(),
                ]);
            }

            // Gửi thông báo
            $this->sendWithdrawalStatusNotification($withdrawal, 'rejected');

            return $withdrawal;
        });
    }

    /**
     * Hoàn thành rút tiền
     */
    public function completeWithdrawal(Withdrawal $withdrawal, User $admin, string $transactionReference = null): Withdrawal
    {
        if (!$withdrawal->isApproved()) {
            throw new \Exception('Chỉ có thể hoàn thành yêu cầu đã được phê duyệt');
        }

        return DB::transaction(function () use ($withdrawal, $admin, $transactionReference) {
            // Cập nhật status
            $withdrawal->update([
                'status' => 'completed',
                'completed_at' => now(),
                'transaction_reference' => $transactionReference,
            ]);

            // Cập nhật transaction status
            if ($withdrawal->transactions()->exists()) {
                $withdrawal->transactions()->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);
            }

            // Gửi thông báo
            $this->sendWithdrawalStatusNotification($withdrawal, 'completed');

            return $withdrawal;
        });
    }

    /**
     * Hủy yêu cầu rút tiền
     */
    public function cancelWithdrawal(Withdrawal $withdrawal, User $user): Withdrawal
    {
        if (!$withdrawal->canBeCancelled()) {
            throw new \Exception('Không thể hủy yêu cầu này');
        }

        return DB::transaction(function () use ($withdrawal, $user) {
            // Cập nhật status
            $withdrawal->update([
                'status' => 'cancelled',
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            // Hoàn lại số dư
            $withdrawal->publisher->getOrCreateWallet()->increment('balance', $withdrawal->amount);

            // Cập nhật transaction status
            if ($withdrawal->transactions()->exists()) {
                $withdrawal->transactions()->update([
                    'status' => 'cancelled',
                    'processed_at' => now(),
                ]);
            }

            // Gửi thông báo
            $this->sendWithdrawalStatusNotification($withdrawal, 'cancelled');

            return $withdrawal;
        });
    }

    /**
     * Lấy danh sách yêu cầu rút tiền cho admin
     */
    public function getWithdrawalsForAdmin(array $filters = [])
    {
        $query = Withdrawal::with(['publisher', 'paymentMethod', 'processedBy', 'approvals.admin']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Filter by amount range
        if (isset($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (isset($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Lấy thống kê rút tiền
     */
    public function getWithdrawalStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return [
            'pending_count' => Withdrawal::where('status', 'pending')->count(),
            'approved_count' => Withdrawal::where('status', 'approved')->count(),
            'completed_count' => Withdrawal::where('status', 'completed')->count(),
            'rejected_count' => Withdrawal::where('status', 'rejected')->count(),
            'total_amount' => Withdrawal::sum('amount'),
            'pending_amount' => Withdrawal::where('status', 'pending')->sum('amount'),
            'today' => [
                'count' => Withdrawal::whereDate('created_at', $today)->count(),
                'amount' => Withdrawal::whereDate('created_at', $today)->sum('amount'),
            ],
            'this_month' => [
                'count' => Withdrawal::where('created_at', '>=', $thisMonth)->count(),
                'amount' => Withdrawal::where('created_at', '>=', $thisMonth)->sum('amount'),
            ],
        ];
    }

    /**
     * ========================================
     * HELPER METHODS
     * ========================================
     */

    /**
     * Lấy thông tin phí rút tiền
     */
    private function getWithdrawalFees(User $publisher): array
    {
        return $publisher->paymentMethods()
            ->get()
            ->map(function($method) {
                return [
                    'id' => $method->id,
                    'type' => $method->type,
                    'type_label' => $method->type_label,
                    'fee_rate' => $method->fee_rate,
                    'fee_amount' => $method->calculateFee(100000), // Phí cho 100k
                    'icon' => $method->icon ?? 'fas fa-credit-card',
                    'color' => $method->color ?? 'gray',
                ];
            })
            ->toArray();
    }

    /**
     * Lấy thống kê theo tháng
     */
    private function getMonthlyStats(User $publisher): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        
        return [
            'current_month' => [
                'clicks' => $this->getClicksInPeriod($publisher, $currentMonth),
                'conversions' => $this->getConversionsInPeriod($publisher, $currentMonth),
                'commission' => $this->getCommissionInPeriod($publisher, $currentMonth),
            ],
            'last_month' => [
                'clicks' => $this->getClicksInPeriod($publisher, $lastMonth),
                'conversions' => $this->getConversionsInPeriod($publisher, $lastMonth),
                'commission' => $this->getCommissionInPeriod($publisher, $lastMonth),
            ],
        ];
    }

    /**
     * Lấy lịch sử rút tiền
     */
    private function getWithdrawalHistory(User $publisher, int $limit = 10): array
    {
        return $publisher->withdrawals()
            ->with('paymentMethod')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Lấy giao dịch gần đây
     */
    private function getRecentTransactions(User $publisher, int $limit = 10)
    {
        return $publisher->transactions()
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Validate dữ liệu phương thức thanh toán
     */
    private function validatePaymentMethodData(array $data, ?int $excludeId = null): void
    {
        $rules = [
            'type' => 'required|in:bank_transfer,momo,zalopay,vnpay,phone_card',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'is_default' => 'boolean',
        ];

        // Rules cho bank_transfer
        if ($data['type'] === 'bank_transfer') {
            $rules['bank_name'] = 'required|string|max:255';
            $rules['bank_code'] = 'nullable|string|max:10';
            $rules['branch_name'] = 'nullable|string|max:255';
        }

        // Rules cho e-wallet
        if (in_array($data['type'], ['momo', 'zalopay', 'vnpay'])) {
            $rules['account_number'] = 'required|string|regex:/^[0-9]{10,11}$/';
        }

        // Rules cho phone_card
        if ($data['type'] === 'phone_card') {
            $rules['account_number'] = 'required|string|regex:/^[0-9]{10,11}$/';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        // Kiểm tra trùng lặp: chỉ báo lỗi khi trùng cả account_number và bank
        // Đối với bank_transfer: kiểm tra trùng account_number + bank_code (hoặc bank_name nếu không có bank_code)
        // Đối với loại khác: kiểm tra trùng account_number + type
        $query = PaymentMethod::where('account_number', $data['account_number'])
            ->where('type', $data['type'])
            ->where('publisher_id', $data['publisher_id'] ?? null);

        // Nếu là bank_transfer, thêm điều kiện kiểm tra ngân hàng
        if ($data['type'] === 'bank_transfer') {
            // Ưu tiên kiểm tra theo bank_code nếu có, không thì dùng bank_name
            if (!empty($data['bank_code'])) {
                $query->where('bank_code', $data['bank_code']);
            } elseif (!empty($data['bank_name'])) {
                $query->where('bank_name', $data['bank_name']);
            }
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            if ($data['type'] === 'bank_transfer') {
                throw new \Exception('Số tài khoản này đã tồn tại tại ngân hàng ' . ($data['bank_name'] ?? 'này'));
            } else {
                throw new \Exception('Phương thức thanh toán này đã tồn tại');
            }
        }
    }

    /**
     * Gửi thông báo yêu cầu rút tiền
     */
    private function sendWithdrawalRequestNotification(Withdrawal $withdrawal): void
    {
        try {
            // Gửi cho admin
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new WithdrawalRequestNotification($withdrawal));
            }
        } catch (\Exception $e) {
            Log::error("Failed to send withdrawal request notification", [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gửi thông báo trạng thái rút tiền
     */
    private function sendWithdrawalStatusNotification(Withdrawal $withdrawal, string $status): void
    {
        try {
            // Load relationship nếu chưa được load
            if (!$withdrawal->relationLoaded('publisher')) {
                $withdrawal->load('publisher');
            }
            
            // Gửi thông báo cho publisher
            $withdrawal->publisher->notify(new WithdrawalStatusNotification($withdrawal, $status));
        } catch (\Exception $e) {
            Log::error("Failed to send withdrawal status notification", [
                'withdrawal_id' => $withdrawal->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // Helper methods for statistics
    private function getClicksInPeriod(User $publisher, Carbon $startDate): int
    {
        return $publisher->affiliateLinks()
            ->join('clicks', 'affiliate_links.id', '=', 'clicks.affiliate_link_id')
            ->where('clicks.created_at', '>=', $startDate)
            ->where('clicks.created_at', '<', $startDate->copy()->addMonth())
            ->count();
    }

    private function getConversionsInPeriod(User $publisher, Carbon $startDate): int
    {
        return $publisher->conversions()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $startDate->copy()->addMonth())
            ->count();
    }

    private function getCommissionInPeriod(User $publisher, Carbon $startDate): float
    {
        return $publisher->conversions()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $startDate->copy()->addMonth())
            ->sum('commission');
    }

    /**
     * Lấy danh sách ngân hàng hỗ trợ
     */
    public function getSupportedBanks(): array
    {
        return [
            ['code' => 'AGR', 'name' => 'Agribank'],
            ['code' => 'VCB', 'name' => 'Vietcombank'],
            ['code' => 'BIDV', 'name' => 'BIDV'],
            ['code' => 'VTB', 'name' => 'VietinBank'],
            ['code' => 'MB', 'name' => 'MBBank'],
            ['code' => 'TCB', 'name' => 'Techcombank'],
            ['code' => 'VPB', 'name' => 'VPBank'],
            ['code' => 'ACB', 'name' => 'ACB'],
            ['code' => 'STB', 'name' => 'Sacombank'],
            ['code' => 'VIB', 'name' => 'VIB'],
            ['code' => 'HDB', 'name' => 'HDBank'],
            ['code' => 'TPB', 'name' => 'TPBank'],
            ['code' => 'SEAB', 'name' => 'SeABank'],
            ['code' => 'EIB', 'name' => 'Eximbank'],
            ['code' => 'NAB', 'name' => 'Nam A Bank'],
            ['code' => 'NCB', 'name' => 'NCB'],
            ['code' => 'BAB', 'name' => 'Bac A Bank'],
            ['code' => 'BAOVIET', 'name' => 'Baoviet Bank'],
            ['code' => 'VAB', 'name' => 'VietABank'],
            ['code' => 'KLB', 'name' => 'Kienlongbank'],
            ['code' => 'SGICB', 'name' => 'Saigonbank'],
            ['code' => 'PVCB', 'name' => 'PVcomBank'],
            ['code' => 'BVB', 'name' => 'BVBank'],
            ['code' => 'ABB', 'name' => 'ABBANK'],
            ['code' => 'OCB', 'name' => 'OCB'],
            ['code' => 'SCB', 'name' => 'SCB'],
            ['code' => 'VIETBANK', 'name' => 'VietBank'],
            ['code' => 'PGB', 'name' => 'PGBank'],
            ['code' => 'LPB', 'name' => 'LPBank'],
        ];
    }

    /**
     * Lấy thống kê phương thức thanh toán
     */
    public function getPaymentMethodStats(): array
    {
        $total = PaymentMethod::count();
        $byType = PaymentMethod::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total' => $total,
            'by_type' => $byType,
        ];
    }
}
