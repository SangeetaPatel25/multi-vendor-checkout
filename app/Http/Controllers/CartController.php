<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\RemoveFromCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->middleware('auth:sanctum');
        $this->cartService = $cartService;
    }

    /**
     * Add a product to the cart
     */
    public function addToCart(AddToCartRequest $request)
    {
        $result = $this->cartService->addToCart(
            $request->product_id,
            $request->quantity
        );

        $statusCode = $result['success'] ? 200 : 400;

        return $this->apiResponse($result, $statusCode);
    }

    /**
     * View cart items grouped by vendor
     */
    public function viewCart()
    {
        $result = $this->cartService->getCart();

        return $this->apiResponse($result);
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(UpdateCartRequest $request)
    {
        $result = $this->cartService->updateQuantity(
            $request->cart_item_id,
            $request->quantity
        );

        $statusCode = $result['success'] ? 200 : 400;

        return $this->apiResponse($result, $statusCode);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(RemoveFromCartRequest $request)
    {
        $result = $this->cartService->removeFromCart($request->cart_item_id);

        return $this->apiResponse($result);
    }
}
