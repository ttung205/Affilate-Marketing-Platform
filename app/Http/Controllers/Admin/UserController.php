<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics
        $stats = $this->getUserStats();

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'shop', 'publisher', 'user'])],
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'shop', 'publisher', 'user'])],
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Thông tin người dùng đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể xóa chính mình.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được xóa thành công.');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể thay đổi trạng thái của chính mình.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        return redirect()->route('admin.users.index')
            ->with('success', "Người dùng đã được {$status} thành công.");
    }

    /**
     * Show shop users management
     */
    public function shopUsers(Request $request)
    {
        $query = User::where('role', 'shop');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $shopUsers = $query->orderBy('created_at', 'desc')->paginate(15);
        $stats = $this->getShopStats();

        return view('admin.users.shop', compact('shopUsers', 'stats'));
    }

    /**
     * Show publisher users management
     */
    public function publisherUsers(Request $request)
    {
        $query = User::where('role', 'publisher');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $publisherUsers = $query->orderBy('created_at', 'desc')->paginate(15);
        $stats = $this->getPublisherStats();

        return view('admin.users.publishers', compact('publisherUsers', 'stats'));
    }

    /**
     * Get user statistics
     */
    private function getUserStats()
    {
        return [
            'total' => User::count(),
            'active' => User::where('is_active', 1)->count(),
            'inactive' => User::where('is_active', 0)->count(),
            'admin' => User::where('role', 'admin')->count(),
            'shop' => User::where('role', 'shop')->count(),
            'publisher' => User::where('role', 'publisher')->count(),
            'user' => User::where('role', 'user')->count(),
            'this_month' => User::whereMonth('created_at', now()->month)->count(),
            'last_month' => User::whereMonth('created_at', now()->subMonth()->month)->count(),
        ];
    }

    /**
     * Get shop statistics
     */
    private function getShopStats()
    {
        return [
            'total' => User::where('role', 'shop')->count(),
            'active' => User::where('role', 'shop')->where('is_active', 1)->count(),
            'inactive' => User::where('role', 'shop')->where('is_active', 0)->count(),
            'this_month' => User::where('role', 'shop')->whereMonth('created_at', now()->month)->count(),
            'last_month' => User::where('role', 'shop')->whereMonth('created_at', now()->subMonth()->month)->count(),
        ];
    }

    /**
     * Get publisher statistics
     */
    private function getPublisherStats()
    {
        return [
            'total' => User::where('role', 'publisher')->count(),
            'active' => User::where('role', 'publisher')->where('is_active', 1)->count(),
            'inactive' => User::where('role', 'publisher')->where('is_active', 0)->count(),
            'this_month' => User::where('role', 'publisher')->whereMonth('created_at', now()->month)->count(),
            'last_month' => User::where('role', 'publisher')->whereMonth('created_at', now()->subMonth()->month)->count(),
        ];
    }
}
