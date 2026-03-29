<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\PaymentSuccessRequest;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService)
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Process checkout
     */
    public function checkout(CheckoutRequest $request)
    {
        return $this->apiResponse(
            $this->checkoutService->processCheckout($request->user())
        );
    }

    /**
     * Get user's order history
     */
    public function orderHistory(Request $request)
    {
        return $this->apiResponse([
            'orders' => $this->checkoutService->getOrderHistory($request->user()),
        ]);
    }

    /**
     * Handle payment success callback (for future payment gateway integration)
     */
    public function paymentSuccess(PaymentSuccessRequest $request)
    {
        return $this->apiResponse(
            $this->checkoutService->markOrdersAsPaid(
                $request->user(),
                $request->validated('order_ids')
            )
        );
    }

    /**
     * Show checkout success page (for web UI)
     */
    public function success()
    {
        return $this->apiResponse([
            'message' => 'Orders created and awaiting payment confirmation.',
        ]);
    }
}
