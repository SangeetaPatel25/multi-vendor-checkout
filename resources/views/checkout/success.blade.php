@extends('layouts.app')

@section('title', 'Order Success')

@section('content')
    <section class="auth-wrap">
        <div class="summary-card stack" style="width:min(100%,720px); text-align:center;">
            <div class="success-mark">✓</div>
            <div class="eyebrow">Success</div>
            <h1>Order placed successfully</h1>
            <p class="muted">Your vendor orders have been paid and recorded. You can keep shopping or review order history through the API.</p>

            <div id="success-orders" class="order-list"></div>

            <div class="hero-actions" style="justify-content:center;">
                <a class="button" href="{{ route('web.products') }}">Continue Shopping</a>
                <a class="button-secondary" href="{{ route('web.account.orders') }}">View My Orders</a>
                <a class="button-secondary" href="{{ route('web.cart') }}">Open Cart</a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const app = window.MultiVendorApp;
            const ordersWrap = document.getElementById('success-orders');
            const paymentResult = app.getPaymentResult();

            if (!paymentResult?.orders?.length) {
                ordersWrap.innerHTML = '<div class="empty-state">No recent payment result found. Complete checkout to see this summary.</div>';
                return;
            }

            ordersWrap.innerHTML = paymentResult.orders.map((order) => `
                <div class="order-pill" style="text-align:left;">
                    <strong>Order #${order.order_id}</strong>
                    <div class="muted">Status: ${order.status}</div>
                    <div class="muted">Payment: ${order.payment_status}</div>
                </div>
            `).join('');
        });
    </script>
@endpush
