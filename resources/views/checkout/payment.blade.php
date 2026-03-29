@extends('layouts.app')

@section('title', 'Payment')

@section('content')
    <section class="split-layout">
        <div class="summary-card stack">
            <div class="page-header">
                <div>
                    <div class="eyebrow">Payment</div>
                    <h1>Confirm your marketplace payment</h1>
                </div>
                <p>Orders have been created in a pending state. Confirm payment to mark them as paid.</p>
            </div>

            <div class="flash" id="payment-flash"></div>
            <div id="payment-orders" class="order-list"></div>
        </div>

        <aside class="summary-card stack">
            <div class="eyebrow">Action</div>
            <h2>One click confirmation</h2>
            <div class="total-row">
                <span class="muted">Orders</span>
                <strong id="payment-order-count">0</strong>
            </div>
            <div class="total-row">
                <span class="muted">Total amount</span>
                <strong id="payment-total-amount">$0.00</strong>
            </div>
            <button class="button" id="pay-now-button" type="button">Pay Now</button>
            <a class="button-secondary" href="{{ route('web.cart') }}">Back to Cart</a>
        </aside>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const app = window.MultiVendorApp;

            if (!app.requireAuth()) {
                return;
            }

            const payload = app.getCheckoutOrders();
            const ordersWrap = document.getElementById('payment-orders');
            const countEl = document.getElementById('payment-order-count');
            const totalEl = document.getElementById('payment-total-amount');
            const payNowButton = document.getElementById('pay-now-button');

            if (!payload?.orders?.length) {
                ordersWrap.innerHTML = '<div class="empty-state">No pending checkout was found. Start from the cart page.</div>';
                payNowButton.disabled = true;
                return;
            }

            const totalAmount = payload.orders.reduce((sum, order) => sum + Number(order.total || 0), 0);
            countEl.textContent = payload.orders.length;
            totalEl.textContent = app.formatCurrency(totalAmount);

            ordersWrap.innerHTML = payload.orders.map((order) => `
                <div class="order-pill">
                    <strong>${order.vendor_name}</strong>
                    <div class="muted">Order #${order.order_id}</div>
                    <div class="muted">${order.items_count} item lines</div>
                    <div style="margin-top:8px;"><strong>${app.formatCurrency(order.total)}</strong></div>
                </div>
            `).join('');

            payNowButton.addEventListener('click', async () => {
                try {
                    const paymentPayload = await app.api('/payment/success', {
                        method: 'POST',
                        body: {
                            order_ids: payload.orders.map((order) => order.order_id),
                        },
                    });

                    app.setPaymentResult(paymentPayload);
                    app.clearCheckoutOrders();
                    window.location.href = '{{ route('web.checkout.success') }}';
                } catch (error) {
                    app.showFlash(error.message || 'Payment confirmation failed.', 'error', 'payment-flash');
                }
            });
        });
    </script>
@endpush
