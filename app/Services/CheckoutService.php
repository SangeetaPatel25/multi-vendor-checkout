<?php

namespace App\Services;

use App\Events\OrderPlaced;
use App\Events\PaymentSucceeded;
use App\Exceptions\Business\EmptyCartException;
use App\Exceptions\Business\InsufficientStockException;
use App\Exceptions\Business\OrderNotFoundForCustomerException;
use App\Exceptions\Business\PaymentStateException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * Process checkout with stock validation and vendor splitting
     */
    public function processCheckout(User $user): array
    {
        $cart = Cart::where('user_id', $user->id)->with('cartItems.product.vendor')->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            throw new EmptyCartException();
        }

        foreach ($cart->cartItems as $cartItem) {
            if ($cartItem->quantity > $cartItem->product->stock) {
                throw new InsufficientStockException(
                    "Insufficient stock for {$cartItem->product->name}. Requested: {$cartItem->quantity}, available: {$cartItem->product->stock}."
                );
            }
        }

        // Group cart items by vendor
        $vendorGroups = $cart->cartItems->groupBy(function ($item) {
            return $item->product->vendor->id;
        });

        $createdOrders = [];

        DB::transaction(function () use ($user, $vendorGroups, $cart, &$createdOrders) {
            foreach ($vendorGroups as $vendorId => $items) {
                $vendor = $items->first()->product->vendor;

                // Calculate total for this vendor
                $total = $items->sum(function ($item) {
                    return $item->quantity * $item->product->price;
                });

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'vendor_id' => $vendorId,
                    'status' => 'pending',
                    'total' => $total,
                ]);

                // Create order items and atomically reserve stock.
                foreach ($items as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product->id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price,
                    ]);

                    $stockReserved = Product::whereKey($cartItem->product->id)
                        ->where('stock', '>=', $cartItem->quantity)
                        ->decrement('stock', $cartItem->quantity);

                    if ($stockReserved === 0) {
                        throw new InsufficientStockException(
                            "Inventory changed for {$cartItem->product->name} while checkout was processing."
                        );
                    }
                }

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $total,
                    'status' => 'pending',
                ]);

                OrderPlaced::dispatch($order);

                $createdOrders[] = [
                    'order_id' => $order->id,
                    'vendor_name' => $vendor->name,
                    'total' => $total,
                    'items_count' => $items->count(),
                    'status' => 'pending',
                    'payment_status' => 'pending',
                ];
            }

            $cart->cartItems()->delete();
        });

        return [
            'success' => true,
            'message' => 'Orders created and awaiting payment confirmation',
            'orders' => $createdOrders,
        ];
    }

    /**
     * Get user's order history
     */
    public function getOrderHistory(User $user)
    {
        return Order::where('user_id', $user->id)
                   ->with('vendor', 'orderItems.product', 'payment')
                   ->orderBy('created_at', 'desc')
                   ->get()
                   ->map(function ($order) {
                       return [
                           'id' => $order->id,
                           'vendor' => [
                               'id' => $order->vendor->id,
                               'name' => $order->vendor->name,
                           ],
                           'status' => $order->status,
                           'total' => $order->total,
                           'created_at' => $order->created_at,
                           'payment_status' => $order->payment?->status,
                           'items' => $order->orderItems->map(function ($item) {
                               return [
                                   'product_name' => $item->product->name,
                                   'quantity' => $item->quantity,
                                   'price' => $item->price,
                                   'subtotal' => $item->quantity * $item->price,
                               ];
                           }),
                       ];
                   });
    }

    /**
     * Mark a set of pending orders as paid for the authenticated customer.
     */
    public function markOrdersAsPaid(User $user, array $orderIds): array
    {
        $orderIds = array_values(array_unique($orderIds));

        $paidOrders = [];

        DB::transaction(function () use ($orderIds, $user, &$paidOrders) {
            $orders = Order::where('user_id', $user->id)
                ->whereIn('id', $orderIds)
                ->with(['payment' => fn ($query) => $query->lockForUpdate()])
                ->lockForUpdate()
                ->get();

            if ($orders->count() !== count($orderIds)) {
                throw new OrderNotFoundForCustomerException();
            }

            foreach ($orders as $order) {
                $payment = $order->payment;

                if (!$payment || $order->status !== 'pending' || $payment->status !== 'pending') {
                    throw new PaymentStateException("Order {$order->id} is not awaiting payment.");
                }

                $order->update(['status' => 'completed']);
                $payment->update(['status' => 'paid']);

                PaymentSucceeded::dispatch($payment->fresh());

                $paidOrders[] = [
                    'order_id' => $order->id,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                ];
            }
        });

        return [
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'orders' => $paidOrders,
        ];
    }

    /**
     * Cancel stale pending orders and release inventory.
     */
    public function cancelExpiredPendingOrders(int $minutes = 30): int
    {
        $expiredOrderIds = Order::query()
            ->where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->whereHas('payment', fn ($query) => $query->where('status', 'pending'))
            ->pluck('id');

        $cancelledCount = 0;

        foreach ($expiredOrderIds as $orderId) {
            DB::transaction(function () use ($orderId, &$cancelledCount) {
                $order = Order::whereKey($orderId)
                    ->with(['orderItems', 'payment'])
                    ->lockForUpdate()
                    ->first();

                if (!$order || $order->status !== 'pending' || $order->payment?->status !== 'pending') {
                    return;
                }

                foreach ($order->orderItems as $orderItem) {
                    Product::whereKey($orderItem->product_id)->increment('stock', $orderItem->quantity);
                }

                $order->update(['status' => 'cancelled']);
                $order->payment->update(['status' => 'failed']);
                $cancelledCount++;
            });
        }

        return $cancelledCount;
    }
}
