<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\AffiliateLink;
use Illuminate\Support\Facades\DB;

class SystemInfoService
{
    /**
     * Lấy thông tin hệ thống real-time
     */
    public function getSystemStats()
    {
        try {
            return [
                'total_users' => User::count(),
                'total_products' => Product::count(),
                'total_affiliate_links' => AffiliateLink::count(),
                'active_campaigns' => $this->getActiveCampaigns(),
                'recent_conversions' => $this->getRecentConversions(),
                'system_status' => 'active'
            ];
        } catch (\Exception $e) {
            return [
                'total_users' => 0,
                'total_products' => 0,
                'total_affiliate_links' => 0,
                'active_campaigns' => 0,
                'recent_conversions' => 0,
                'system_status' => 'maintenance'
            ];
        }
    }

    /**
     * Lấy thông tin user cụ thể
     */
    public function getUserContext($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return null;
            }

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'created_at' => $user->created_at->format('d/m/Y'),
                'last_login' => $user->last_login_at ?? 'Chưa đăng nhập',
                'affiliate_links_count' => $user->affiliateLinks()->count(),
                'total_earnings' => $this->getUserEarnings($user->id),
                'recent_activity' => $this->getUserRecentActivity($user->id)
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getActiveCampaigns()
    {
        // Logic để đếm campaigns đang hoạt động
        return 0; // Placeholder
    }

    private function getRecentConversions()
    {
        // Logic để đếm conversions gần đây
        return 0; // Placeholder
    }

    private function getUserEarnings($userId)
    {
        // Logic để tính tổng thu nhập của user
        return 0; // Placeholder
    }

    private function getUserRecentActivity($userId)
    {
        // Logic để lấy hoạt động gần đây của user
        return []; // Placeholder
    }
}
