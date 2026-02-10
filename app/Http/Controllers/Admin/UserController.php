<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminBulkEmail;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->role($request->role);
        }

        // Filter by verification status
        if ($request->has('verified') && $request->verified !== '') {
            if ($request->verified) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->with(['roles', 'orders'])->withCount('orders')->latest()->paginate(15);
        
        // Automatically assign affiliate role to users with approved affiliates
        foreach ($users as $user) {
            $approvedAffiliate = $user->affiliate()->whereNotNull('approved_at')->first();
            if ($approvedAffiliate && !$user->hasRole('affiliate')) {
                try {
                    $user->assignRole('affiliate');
                } catch (\Exception $e) {
                    // Role doesn't exist, skip silently
                    // In production, you may want to log this
                }
            }
        }
        
        // Calculate stats
        $stats = [
            'total' => User::count(),
            'customers' => User::role('customer')->count(),
            'admins' => User::role('admin')->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)->count()
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'orders.product', 'downloads.order.product']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Toggle user role between admin and customer.
     */
    public function toggleRole(User $user)
    {
        // Prevent changing own role
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot change your own role.');
        }

        $currentUser = auth()->user();

        // Check if current user has permission to manage admin roles
        if (!$currentUser->hasPermissionTo('manage-admin-roles')) {
            return redirect()->back()->with('error', 'You do not have permission to manage admin roles.');
        }

        // Prevent removing super_admin role if you're not a super_admin
        if ($user->hasRole('super_admin') && !$currentUser->hasRole('super_admin')) {
            return redirect()->back()->with('error', 'Only Super Admins can manage other Super Admins.');
        }

        if ($user->hasRole('admin') || $user->hasRole('super_admin') || $user->hasRole('product_admin')) {
            // Remove all admin roles
            $user->removeRole('admin');
            $user->removeRole('super_admin');
            $user->removeRole('product_admin');
            $user->assignRole('customer');
            $message = 'User role changed to customer.';
        } else {
            $user->removeRole('customer');
            $user->assignRole('admin');
            $message = 'User role changed to admin.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Assign a specific role to a user.
     */
    public function assignRole(User $user, Request $request)
    {
        // Prevent changing own role
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot change your own role.');
        }

        $currentUser = auth()->user();
        $role = $request->input('role', 'admin');

        // Check if current user has permission to manage admin roles
        if (!$currentUser->hasPermissionTo('manage-admin-roles')) {
            return redirect()->back()->with('error', 'You do not have permission to manage admin roles.');
        }

        // Prevent assigning super_admin if you're not a super_admin
        if ($role === 'super_admin' && !$currentUser->hasRole('super_admin')) {
            return redirect()->back()->with('error', 'Only Super Admins can assign Super Admin role.');
        }

        // Remove all admin roles first
        $user->removeRole('admin');
        $user->removeRole('super_admin');
        $user->removeRole('product_admin');
        $user->removeRole('customer');

        // Assign the new role
        $user->assignRole($role);

        $message = 'User role changed to ' . ucfirst(str_replace('_', ' ', $role)) . '.';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Send a bulk email to users matching the given filters.
     */
    public function bulkEmail(Request $request)
    {
        $request->validate([
            'subject' => 'required|string',
            'message' => 'required|string',
            'filters' => 'nullable|string'
        ]);

        // Parse filters from the modal's hidden field
        $filters = json_decode($request->filters, true) ?? [];
        $query = User::query();

        // Apply filters as per your index logic
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if (isset($filters['role']) && $filters['role']) {
            $query->role($filters['role']);
        }
        if (isset($filters['verified']) && $filters['verified'] !== '') {
            if ($filters['verified']) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->with(['roles', 'orders'])->withCount('orders')->get();

        foreach ($users as $user) {
            // Use queue for performance on large lists
            Mail::to($user->email)->queue(new AdminBulkEmail($request->subject, $request->message, $user));
        }

        return back()->with('success', 'Bulk email is being sent to '.$users->count().' user(s).');
    }
}