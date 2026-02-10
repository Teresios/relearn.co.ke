<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of orders.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'product', 'payment']);

        // Filter: by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: by product ID
        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }

        // Filter: by date range (from date)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filter: by date range (to date)
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter: by price range (minimum)
        if ($request->filled('price_from')) {
            $query->where('amount', '>=', (float) $request->price_from);
        }

        // Filter: by price range (maximum)
        if ($request->filled('price_to')) {
            $query->where('amount', '<=', (float) $request->price_to);
        }

        // Search: order ID, user name, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Paginate results
        $orders = $query->latest()->paginate(15);

        // Stats summary
        $stats = [
            'total' => Order::count(),
            'completed' => Order::where('status', 'completed')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('amount'),
        ];

        // Product list for filter dropdown
        $products = Product::select('id', 'name')->get();

        // Get unique payment methods (handle if column doesn't exist)
        $paymentMethods = collect();
        try {
            $paymentMethods = Order::select('payment_method')
                ->whereNotNull('payment_method')
                ->distinct()
                ->pluck('payment_method');
        } catch (\Exception $e) {
            // Column doesn't exist, initialize empty collection
            $paymentMethods = collect();
        }

        return view('admin.orders.index', compact('orders', 'stats', 'products', 'paymentMethods'));
    }

    /**
     * Display the specified order and related data.
     *
     * @param Order $order
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Order $order)
    {
        try {
            $order->load(['user', 'product', 'payment', 'downloads']);
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.index')->with('error', 'Could not load order details.');
        }

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the status of a given order.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated successfully.');
    }
}