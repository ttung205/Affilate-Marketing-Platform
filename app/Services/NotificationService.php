<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RealTimeNotification;

class NotificationService
{
    /**
     * Send notification to a user (database only, using polling for real-time updates)
     */
    public function sendNotification(
        User $user,
        string $type,
        array $data = []
    ): void {
        // Get notification template
        $template = NotificationTemplate::where('type', $type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            \Log::warning("Notification template not found: {$type}");
            // Use default template if not found
            $template = (object) [
                'title' => $data['title'] ?? 'Thông báo',
                'message' => $data['message'] ?? 'Bạn có thông báo mới',
                'icon' => $data['icon'] ?? 'fas fa-bell',
                'color' => $data['color'] ?? 'blue'
            ];
        }

        // Prepare notification data
        $notificationData = [
            'type' => $type,
            'title' => $template->title ?? $data['title'] ?? 'Thông báo',
            'message' => $template->message ?? $data['message'] ?? 'Bạn có thông báo mới',
            'icon' => $template->icon ?? $data['icon'] ?? 'fas fa-bell',
            'color' => $template->color ?? $data['color'] ?? 'blue',
            'data' => $data,
            'created_at' => now()->toISOString(),
        ];

        // Send notification
        try {
            $user->notify(new RealTimeNotification($notificationData));
            \Log::info("Notification sent to user {$user->id} successfully");
        } catch (\Exception $e) {
            \Log::error("Failed to send notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendBulkNotification(
        array $userIds,
        string $type,
        array $data = []
    ): void {
        $users = User::whereIn('id', $userIds)->get();
        
        foreach ($users as $user) {
            $this->sendNotification($user, $type, $data);
        }
    }

    /**
     * Send custom notification without template
     */
    public function sendCustomNotification(
        User $user,
        array $data = []
    ): void {
        // Prepare notification data với giá trị mặc định
        $notificationData = [
            'type' => 'custom',
            'title' => $data['title'] ?? 'Thông báo',
            'message' => $data['message'] ?? 'Bạn có thông báo mới',
            'icon' => $data['icon'] ?? 'fas fa-bell',
            'color' => $data['color'] ?? 'blue',
            'data' => $data,
            'created_at' => now()->toISOString(),
        ];

        // Send notification
        try {
            $user->notify(new RealTimeNotification($notificationData));
            \Log::info("Custom notification sent to user {$user->id} successfully");
        } catch (\Exception $e) {
            \Log::error("Failed to send custom notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification to all users with a specific role
     */
    public function sendToRole(
        string $role,
        string $type,
        array $data = []
    ): void {
        $userIds = User::where('role', $role)->pluck('id')->toArray();
        $this->sendBulkNotification($userIds, $type, $data);
    }

    // Specific notification methods
    public function notifyNewClick(User $publisher, array $clickData): void
    {
        $this->sendNotification($publisher, 'new_click', [
            'product_name' => $clickData['product_name'] ?? 'Unknown Product',
            'click_count' => $clickData['click_count'] ?? 1,
            'revenue' => $clickData['revenue'] ?? 0,
        ]);
    }

    public function notifyNewConversion(User $publisher, array $conversionData): void
    {
        $this->sendNotification($publisher, 'new_conversion', [
            'product_name' => $conversionData['product_name'] ?? 'Unknown Product',
            'commission' => $conversionData['commission'] ?? 0,
            'order_value' => $conversionData['order_value'] ?? 0,
        ]);
    }

    public function notifyNewOrder(User $shopOwner, array $orderData): void
    {
        $this->sendNotification($shopOwner, 'new_order', [
            'order_id' => $orderData['order_id'] ?? 'Unknown',
            'customer_name' => $orderData['customer_name'] ?? 'Unknown',
            'total_amount' => $orderData['total_amount'] ?? 0,
        ]);
    }

    public function notifyCommissionUpdate(User $publisher, array $commissionData): void
    {
        $this->sendNotification($publisher, 'commission_update', [
            'amount' => $commissionData['amount'] ?? 0,
            'period' => $commissionData['period'] ?? 'This month',
            'total_commission' => $commissionData['total_commission'] ?? 0,
        ]);
    }
}
