<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Voucher;
use App\Models\User;

class VoucherAssignedNotification extends Notification
{
    use Queueable;

    protected $voucher;

    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $shopName = User::find($this->voucher->shop_id)?->name ?? 'một cửa hàng';
        $voucherCode = $this->voucher->code;

        if ($this->voucher->is_global) {
            $applyText = 'áp dụng cho toàn shop';
        } else {
            // Lấy danh sách tên sản phẩm liên kết với voucher
            $productNames = $this->voucher->products()->pluck('name')->toArray();
            $applyText = 'áp dụng cho sản phẩm: ' . implode(', ', $productNames);
        }

        return [
            'title' => 'Bạn nhận được voucher mới',
            'message' => "Bạn được tặng voucher {$voucherCode} từ {$shopName} ({$applyText}).",
            'voucher_id' => $this->voucher->id,
            'shop_id' => $this->voucher->shop_id,
            'created_at' => now(),
        ];
    }
}
