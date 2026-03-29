<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Add product to cart with stock validation
     */
    public function addToCart(int $productId, int $quantity): array
    {
        $product = Product::findOrFail($productId);

        // Check stock availability
        if ($quantity > $product->stock) {
            return [
                'success' => false,
                'message' => 'Requested quantity exceeds available stock',
                'available_stock' => $product->stock,
            ];
        }

        // Get or create cart for user
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        // Check if product already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
                           ->where('product_id', $product->id)
                           ->first();

        if ($cartItem) {
            // Update existing cart item
            $newQuantity = $cartItem->quantity + $quantity;

            if ($newQuantity > $product->stock) {
                return [
                    'success' => false,
                    'message' => 'Total quantity in cart would exceed available stock',
                    'available_stock' => $product->stock,
                    'current_in_cart' => $cartItem->quantity,
                ];
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Product added to cart successfully',
        ];
    }

    /**
     * Get cart items grouped by vendor
     */
    public function getCart(): array
    {
        $cart = Cart::where('user_id', Auth::id())->with('cartItems.product.vendor')->first();

        if (!$cart) {
            return [
                'cart' => [],
                'total_items' => 0,
            ];
        }

        // Group cart items by vendor
        $groupedItems = $cart->cartItems->groupBy(function ($item) {
            return $item->product->vendor->name;
        });

        $groupedCart = [];
        $totalItems = 0;

        foreach ($groupedItems as $vendorName => $items) {
            $groupedCart[$vendorName] = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'stock' => $item->product->stock,
                        'vendor' => [
                            'id' => $item->product->vendor->id,
                            'name' => $item->product->vendor->name,
                        ],
                    ],
                ];
            });
            $totalItems += $items->sum('quantity');
        }

        return [
            'cart' => $groupedCart,
            'total_items' => $totalItems,
        ];
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(int $cartItemId, int $quantity): array
    {
        $cartItem = CartItem::where('id', $cartItemId)
                           ->whereHas('cart', function ($query) {
                               $query->where('user_id', Auth::id());
                           })
                           ->with('product')
                           ->firstOrFail();

        // Check stock availability
        if ($quantity > $cartItem->product->stock) {
            return [
                'success' => false,
                'message' => 'Requested quantity exceeds available stock',
                'available_stock' => $cartItem->product->stock,
            ];
        }

        $cartItem->update(['quantity' => $quantity]);

        return [
            'success' => true,
            'message' => 'Cart item quantity updated successfully',
        ];
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(int $cartItemId): array
    {
        $cartItem = CartItem::where('id', $cartItemId)
                           ->whereHas('cart', function ($query) {
                               $query->where('user_id', Auth::id());
                           })
                           ->firstOrFail();

        $cartItem->delete();

        return [
            'success' => true,
            'message' => 'Item removed from cart successfully',
        ];
    }
}
