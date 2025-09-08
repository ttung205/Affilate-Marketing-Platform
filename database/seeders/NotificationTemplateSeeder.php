<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'type' => 'new_click',
                'title' => 'Click mới! 🎯',
                'message' => 'Bạn vừa có {{click_count}} click mới cho sản phẩm {{product_name}}',
                'icon' => 'fas fa-mouse-pointer',
                'color' => 'blue',
                'channels' => ['database', 'broadcast'],
                'is_active' => true,
            ],
            [
                'type' => 'new_conversion',
                'title' => 'Conversion thành công! 💰',
                'message' => 'Chúc mừng! Bạn vừa có conversion cho {{product_name}} với hoa hồng {{commission}} VND',
                'icon' => 'fas fa-chart-line',
                'color' => 'green',
                'channels' => ['database', 'broadcast'],
                'is_active' => true,
            ],
            [
                'type' => 'new_order',
                'title' => 'Đơn hàng mới! 📦',
                'message' => 'Bạn có đơn hàng mới #{{order_id}} từ {{customer_name}} trị giá {{total_amount}} VND',
                'icon' => 'fas fa-shopping-cart',
                'color' => 'purple',
                'channels' => ['database', 'broadcast'],
                'is_active' => true,
            ],
            [
                'type' => 'commission_update',
                'title' => 'Cập nhật hoa hồng! 💵',
                'message' => 'Hoa hồng {{period}} của bạn đã được cập nhật: {{amount}} VND (Tổng: {{total_commission}} VND)',
                'icon' => 'fas fa-coins',
                'color' => 'yellow',
                'channels' => ['database', 'broadcast'],
                'is_active' => true,
            ],
            [
                'type' => 'campaign_status',
                'title' => 'Cập nhật chiến dịch! 📢',
                'message' => 'Chiến dịch {{campaign_name}} đã {{status}}',
                'icon' => 'fas fa-bullhorn',
                'color' => 'indigo',
                'channels' => ['database', 'broadcast'],
                'is_active' => true,
            ],
            [
                'type' => 'new_user_registration',
                'title' => 'Người dùng mới! 👤',
                'message' => 'Có {{role}} mới đăng ký: {{user_name}} ({{user_email}})',
                'icon' => 'fas fa-user-plus',
                'color' => 'teal',
                'channels' => ['database', 'broadcast'],
                'is_active' => true,
            ],
            [
                'type' => 'test',
                'title' => 'Test Notification! 🧪',
                'message' => '{{message}} - Chào {{user_name}}!',
                'icon' => 'fas fa-flask',
                'color' => 'gray',
                'channels' => ['database', 'broadcast'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                ['type' => $template['type']],
                $template
            );
        }
    }
}
