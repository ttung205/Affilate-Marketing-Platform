<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\NotificationTemplate;
use App\Services\NotificationService;

class NotificationManagementController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Hiển thị trang quản lý thông báo
     */
    public function index()
    {
        $templates = NotificationTemplate::where('is_active', true)->get();
        $userCounts = [
            'admin' => User::where('role', 'admin')->count(),
            'shop' => User::where('role', 'shop')->count(),
            'publisher' => User::where('role', 'publisher')->count(),
            'all' => User::count(),
        ];

        return view('admin.notifications.index', compact('templates', 'userCounts'));
    }

    /**
     * Gửi thông báo cho tất cả users
     */
    public function sendToAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $users = User::all();
        $count = 0;
        
        foreach ($users as $user) {
            try {
                $this->notificationService->sendNotification($user, $request->type, [
                    'title' => $request->title,
                    'message' => $request->message,
                    'icon' => $request->icon ?? 'fas fa-bell',
                    'color' => $request->color ?? 'blue',
                    'admin_sent' => true,
                    'admin_name' => auth()->user()->name,
                ]);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Đã gửi thông báo cho {$count} người dùng",
            'count' => $count
        ]);
    }

    /**
     * Gửi thông báo cho role cụ thể
     */
    public function sendToRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string|in:admin,shop,publisher',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $users = User::where('role', $request->role)->get();
        
        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy người dùng nào với role: {$request->role}"
            ], 400);
        }

        foreach ($users as $user) {
            $this->notificationService->sendNotification($user, $request->type, [
                'title' => $request->title,
                'message' => $request->message,
                'icon' => $request->icon ?? 'fas fa-bell',
                'color' => $request->color ?? 'blue',
                'admin_sent' => true,
                'admin_name' => auth()->user()->name,
                'target_role' => $request->role,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã gửi thông báo cho {$users->count()} {$request->role}",
            'count' => $users->count()
        ]);
    }

    /**
     * Gửi thông báo cho user cụ thể
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $user = User::findOrFail($request->user_id);
        
        $this->notificationService->sendNotification($user, $request->type, [
            'title' => $request->title,
            'message' => $request->message,
            'icon' => $request->icon ?? 'fas fa-bell',
            'color' => $request->color ?? 'blue',
            'admin_sent' => true,
            'admin_name' => auth()->user()->name,
            'target_user' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã gửi thông báo cho {$user->name}",
            'user' => $user->name
        ]);
    }

    /**
     * Lấy danh sách users theo role
     */
    public function getUsersByRole(Request $request)
    {
        $role = $request->get('role');
        
        if ($role === 'all' || $role === '' || $role === null) {
            $users = User::select('id', 'name', 'email', 'role')->get();
        } else {
            $users = User::where('role', $role)->select('id', 'name', 'email', 'role')->get();
        }

        return response()->json($users);
    }

    /**
     * Lấy thống kê thông báo
     */
    public function getStats()
    {
        $stats = [
            'total_notifications' => \DB::table('notifications')->count(),
            'unread_notifications' => \DB::table('notifications')->whereNull('read_at')->count(),
            'notifications_today' => \DB::table('notifications')
                ->whereDate('created_at', today())
                ->count(),
            'notifications_this_week' => \DB::table('notifications')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return response()->json($stats);
    }
}
