<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FraudDetectionController extends Controller
{
    private FraudDetectionService $fraudDetection;

    public function __construct(FraudDetectionService $fraudDetection)
    {
        $this->fraudDetection = $fraudDetection;
    }

    /**
     * Display fraud detection dashboard
     */
    public function index(Request $request)
    {
        $days = $request->input('days', 7);
        
        // Get fraud statistics
        $stats = $this->fraudDetection->getFraudStatistics($days);
        
        // Get recent fraud attempts
        $recentFraudAttempts = DB::table('click_fraud_logs')
            ->join('users', 'click_fraud_logs.publisher_id', '=', 'users.id')
            ->leftJoin('affiliate_links', 'click_fraud_logs.affiliate_link_id', '=', 'affiliate_links.id')
            ->select(
                'click_fraud_logs.*',
                'users.name as publisher_name',
                'users.email as publisher_email',
                'affiliate_links.tracking_code'
            )
            ->orderBy('click_fraud_logs.detected_at', 'desc')
            ->limit(50)
            ->get();

        // Get blocked IPs
        $blockedIps = $this->getBlockedIps();

        // Fraud trend data (for chart)
        $fraudTrend = DB::table('click_fraud_logs')
            ->select(
                DB::raw('DATE(detected_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(risk_score) as avg_risk_score')
            )
            ->where('detected_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(detected_at)'))
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.fraud-detection.index', compact(
            'stats',
            'recentFraudAttempts',
            'blockedIps',
            'fraudTrend',
            'days'
        ));
    }

    /**
     * Get details of a specific fraud attempt
     */
    public function show(Request $request, $id)
    {
        $fraudLog = DB::table('click_fraud_logs')
            ->join('users', 'click_fraud_logs.publisher_id', '=', 'users.id')
            ->leftJoin('affiliate_links', 'click_fraud_logs.affiliate_link_id', '=', 'affiliate_links.id')
            ->leftJoin('products', 'click_fraud_logs.product_id', '=', 'products.id')
            ->leftJoin('campaigns', 'click_fraud_logs.campaign_id', '=', 'campaigns.id')
            ->select(
                'click_fraud_logs.*',
                'users.name as publisher_name',
                'users.email as publisher_email',
                'affiliate_links.tracking_code',
                'products.name as product_name',
                'campaigns.name as campaign_name'
            )
            ->where('click_fraud_logs.id', $id)
            ->first();

        if (!$fraudLog) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fraud log not found'
                ], 404);
            }
            abort(404, 'Fraud log not found');
        }

        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'fraud' => $fraudLog
            ]);
        }

        // Get other attempts from same IP
        $sameIpAttempts = DB::table('click_fraud_logs')
            ->where('ip_address', $fraudLog->ip_address)
            ->where('id', '!=', $id)
            ->orderBy('detected_at', 'desc')
            ->limit(10)
            ->get();

        // Get other attempts from same publisher
        $samePublisherAttempts = DB::table('click_fraud_logs')
            ->where('publisher_id', $fraudLog->publisher_id)
            ->where('id', '!=', $id)
            ->orderBy('detected_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.fraud-detection.show', compact(
            'fraudLog',
            'sameIpAttempts',
            'samePublisherAttempts'
        ));
    }

    /**
     * Block an IP address manually
     */
    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:500',
        ]);

        $this->fraudDetection->blockIpAddress(
            $request->ip_address,
            $request->reason
        );

        return redirect()
            ->route('admin.fraud-detection.index')
            ->with('success', "IP {$request->ip_address} đã được chặn thành công.");
    }

    /**
     * Unblock an IP address
     */
    public function unblockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        Cache::forget("blocked_ip:{$request->ip_address}");

        return redirect()
            ->route('admin.fraud-detection.index')
            ->with('success', "IP {$request->ip_address} đã được gỡ chặn.");
    }

    /**
     * Clear fraud detection cache
     */
    public function clearCache(Request $request)
    {
        $ipAddress = $request->input('ip_address');
        
        $this->fraudDetection->clearCache($ipAddress);

        $message = $ipAddress 
            ? "Cache cho IP {$ipAddress} đã được xóa."
            : "Toàn bộ cache fraud detection đã được xóa.";

        return redirect()
            ->route('admin.fraud-detection.index')
            ->with('success', $message);
    }

    /**
     * Export fraud logs
     */
    public function export(Request $request)
    {
        $days = $request->input('days', 7);

        $fraudLogs = DB::table('click_fraud_logs')
            ->join('users', 'click_fraud_logs.publisher_id', '=', 'users.id')
            ->leftJoin('affiliate_links', 'click_fraud_logs.affiliate_link_id', '=', 'affiliate_links.id')
            ->select(
                'click_fraud_logs.*',
                'users.name as publisher_name',
                'users.email as publisher_email',
                'affiliate_links.tracking_code'
            )
            ->where('click_fraud_logs.detected_at', '>=', now()->subDays($days))
            ->orderBy('click_fraud_logs.detected_at', 'desc')
            ->get();

        $filename = "fraud_logs_" . now()->format('Y-m-d_His') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($fraudLogs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Date', 'Publisher', 'Email', 'IP Address', 
                'User Agent', 'Tracking Code', 'Risk Score', 'Reasons'
            ]);

            // CSV data
            foreach ($fraudLogs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->detected_at,
                    $log->publisher_name,
                    $log->publisher_email,
                    $log->ip_address,
                    $log->user_agent,
                    $log->tracking_code,
                    $log->risk_score,
                    $log->reasons
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get list of blocked IPs from cache
     */
    private function getBlockedIps(): array
    {
        $blockedIps = [];
        
        // This is a simplified version - in production you might want to store blocked IPs in database
        // For now, we'll just return sample data
        // You can enhance this by storing blocked IPs in a dedicated table
        
        return $blockedIps;
    }

    /**
     * Delete old fraud logs (cleanup)
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 90);

        $deleted = DB::table('click_fraud_logs')
            ->where('detected_at', '<', now()->subDays($days))
            ->delete();

        return redirect()
            ->route('admin.fraud-detection.index')
            ->with('success', "Đã xóa {$deleted} fraud logs cũ hơn {$days} ngày.");
    }
}

