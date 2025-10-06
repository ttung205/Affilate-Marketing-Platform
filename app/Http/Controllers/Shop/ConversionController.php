<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Conversion;
use App\Services\NotificationService;
use App\Services\PublisherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConversionController extends Controller
{
    public function __construct(
        private PublisherService $publisherService,
        private NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $shop = Auth::user();

        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối',
        ];

        $filters = [
            'status' => $request->filled('status') ? $request->get('status') : null,
            'search' => $request->filled('search') ? trim($request->get('search')) : null,
            'date_from' => $request->filled('date_from') ? $request->get('date_from') : null,
            'date_to' => $request->filled('date_to') ? $request->get('date_to') : null,
        ];

        $query = Conversion::with(['product', 'affiliateLink.publisher'])
            ->where('shop_id', $shop->id);

        if ($filters['status'] && array_key_exists($filters['status'], $statuses)) {
            $query->where('status', $filters['status']);
        }

        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('affiliateLink.publisher', function ($publisherQuery) use ($search) {
                        $publisherQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters['date_from']) {
            $query->whereDate('converted_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('converted_at', '<=', $filters['date_to']);
        }

        $conversions = $query
            ->orderByDesc('converted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $summary = [
            'pending' => $this->buildStatusSummary($shop->id, 'pending'),
            'approved' => $this->buildStatusSummary($shop->id, 'approved'),
            'rejected' => $this->buildStatusSummary($shop->id, 'rejected'),
        ];

        return view('shop.conversions.index', [
            'conversions' => $conversions,
            'filters' => $filters,
            'statuses' => $statuses,
            'summary' => $summary,
        ]);
    }

    public function updateStatus(Request $request, Conversion $conversion)
    {
        $shop = Auth::user();

        if ($conversion->shop_id !== $shop->id) {
            abort(403);
        }

        if (!$conversion->isPending()) {
            return redirect()
                ->route('shop.conversions.index')
                ->with('error', 'Chỉ có thể xử lý các đơn hàng đang chờ duyệt.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'status_note' => 'nullable|string|max:1000',
        ]);

        $newStatus = $validated['status'];
        $note = trim($validated['status_note'] ?? '');

        if ($newStatus === 'rejected' && $conversion->is_commission_processed) {
            return redirect()
                ->route('shop.conversions.index')
                ->with('error', 'Không thể từ chối đơn hàng đã xử lý hoa hồng.');
        }

        DB::transaction(function () use ($conversion, $shop, $newStatus, $note) {
            $conversion->status = $newStatus;
            $conversion->status_changed_by = $shop->id;
            $conversion->status_changed_at = now();
            $conversion->status_note = $note !== '' ? $note : null;
            $conversion->save();

            if ($newStatus === 'approved') {
                $this->publisherService->processConversionCommission($conversion);
            }
        });

        $conversion->refresh();

        $this->notifyPublisher($conversion);

        $message = $newStatus === 'approved'
            ? 'Đã duyệt đơn hàng và cập nhật hoa hồng cho publisher.'
            : 'Đã từ chối đơn hàng.';

        return redirect()
            ->route('shop.conversions.index')
            ->with('success', $message);
    }

    private function buildStatusSummary(int $shopId, string $status): array
    {
        $query = Conversion::where('shop_id', $shopId)
            ->where('status', $status);

        $count = $query->count();
        $amount = $count > 0 ? (clone $query)->sum('amount') : 0;
        $commission = $count > 0 ? (clone $query)->sum('commission') : 0;

        return [
            'count' => $count,
            'amount' => $amount,
            'commission' => $commission,
        ];
    }

    private function notifyPublisher(Conversion $conversion): void
    {
        try {
            $publisher = $conversion->publisher;
            if (!$publisher) {
                return;
            }

            $status = $conversion->status;
            $title = $status === 'approved'
                ? 'Đơn hàng đã được duyệt'
                : 'Đơn hàng bị từ chối';

            $message = $status === 'approved'
                ? sprintf('Shop %s đã duyệt đơn hàng %s.', $conversion->shop?->name ?? 'không xác định', $conversion->order_id)
                : sprintf('Shop %s đã từ chối đơn hàng %s.', $conversion->shop?->name ?? 'không xác định', $conversion->order_id);

            $this->notificationService->sendCustomNotification($publisher, [
                'title' => $title,
                'message' => $message,
                'icon' => $status === 'approved' ? 'fas fa-check-circle' : 'fas fa-times-circle',
                'color' => $status === 'approved' ? 'green' : 'red',
                'conversion_id' => $conversion->id,
                'order_id' => $conversion->order_id,
                'status' => $status,
                'amount' => $conversion->amount,
                'commission' => $conversion->commission,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send conversion notification', [
                'conversion_id' => $conversion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
