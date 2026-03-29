<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * View all orders with filtering and pagination
     */
    public function viewAllOrders(Request $request)
    {
        Gate::authorize('admin.view-orders');

        $query = Order::with('user', 'vendor', 'orderItems.product');

        // Apply filters
        if ($request->has('vendor_id') && $request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
                        ->paginate(20);

        return $this->apiResponse([
            'orders' => $orders,
        ]);
    }

    /**
     * View detailed information for a specific order
     */
    public function viewOrderDetails($orderId)
    {
        Gate::authorize('admin.view-order');

        $order = Order::with('user', 'vendor', 'orderItems.product', 'payment')
                     ->findOrFail($orderId);

        // Calculate total from order items for verification
        $calculatedTotal = $order->orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return $this->apiResponse([
            'order' => [
                'id' => $order->id,
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'vendor' => [
                    'id' => $order->vendor->id,
                    'name' => $order->vendor->name,
                ],
                'status' => $order->status,
                'total' => $order->total,
                'created_at' => $order->created_at,
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->quantity * $item->price,
                    ];
                }),
                'payment' => $order->payment ? [
                    'status' => $order->payment->status,
                    'amount' => $order->payment->amount,
                ] : null,
            ],
            'calculated_total' => $calculatedTotal,
            'total_matches' => abs($calculatedTotal - $order->total) < 0.01,
        ]);
    }

    /**
     * Get all vendors for filtering
     */
    public function getVendors()
    {
        Gate::authorize('admin.view-vendors');

        $vendors = Vendor::with('user')->get()->map(function ($vendor) {
            return [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'user_email' => $vendor->user->email,
            ];
        });

        return $this->apiResponse([
            'vendors' => $vendors,
        ]);
    }

    /**
     * Get all customers for filtering
     */
    public function getCustomers()
    {
        Gate::authorize('admin.view-customers');

        $customers = User::where('role', 'customer')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        return $this->apiResponse([
            'customers' => $customers,
        ]);
    }

    /**
     * Admin dashboard data
     */
    public function dashboard()
    {
        Gate::authorize('admin.access');

        return $this->apiResponse([
            'message' => 'Admin dashboard - use /admin/orders for order management',
        ]);
    }

    /**
     * Show specific order (alias for viewOrderDetails)
     */
    public function showOrder($orderId)
    {
        return $this->viewOrderDetails($orderId);
    }

    /**
     * Get order statistics
     */
    public function getOrderStats()
    {
        Gate::authorize('admin.view-stats');

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total'),
            'orders_by_vendor' => Order::selectRaw('vendor_id, COUNT(*) as order_count, SUM(total) as revenue')
                                      ->where('status', 'completed')
                                      ->with('vendor')
                                      ->groupBy('vendor_id')
                                      ->get()
                                      ->map(function ($stat) {
                                          return [
                                              'vendor' => $stat->vendor->name,
                                              'order_count' => $stat->order_count,
                                              'revenue' => $stat->revenue,
                                          ];
                                      }),
        ];

        return $this->apiResponse([
            'stats' => $stats,
        ]);
    }
}
