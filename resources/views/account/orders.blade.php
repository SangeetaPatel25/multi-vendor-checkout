@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
    <section class="stack">
        <div class="page-header">
            <div>
                <div class="eyebrow">Account</div>
                <h1>Your purchased products</h1>
            </div>
            <p>Review completed marketplace purchases, grouped by order and vendor, directly from your account.</p>
        </div>

        <div class="flash" id="orders-flash"></div>
        <div id="orders-list" class="stack">
            <div class="empty-state">Loading your order history...</div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const app = window.MultiVendorApp;
            const user = app.getUser();

            if (!app.requireAuth()) {
                return;
            }

            if (user?.role === 'admin') {
                window.location.href = '{{ route('web.admin.dashboard') }}';
                return;
            }

            const list = document.getElementById('orders-list');

            try {
                const payload = await app.api('/orders');
                const orders = payload.orders || [];

                if (!orders.length) {
                    list.innerHTML = '<div class="empty-state">You have not completed any purchases yet.</div>';
                    return;
                }

                list.innerHTML = orders.map((order) => `
                    <section class="vendor-group">
                        <div class="eyebrow">${order.vendor.name}</div>
                        <h2>Order #${order.id}</h2>
                        <div class="muted">Status: ${order.status} | Payment: ${order.payment_status || 'n/a'}</div>
                        <div class="muted">Placed: ${new Date(order.created_at).toLocaleString()}</div>
                        <div class="muted" style="margin-top:8px;">Total: <strong>${app.formatCurrency(order.total)}</strong></div>
                        <div class="stack" style="margin-top:16px;">
                            ${order.items.map((item) => `
                                <div class="order-pill">
                                    <strong>${item.product_name}</strong>
                                    <div class="muted">Quantity: ${item.quantity}</div>
                                    <div class="muted">Price: ${app.formatCurrency(item.price)}</div>
                                    <div class="muted">Subtotal: ${app.formatCurrency(item.subtotal)}</div>
                                </div>
                            `).join('')}
                        </div>
                    </section>
                `).join('');
            } catch (error) {
                list.innerHTML = '<div class="empty-state">Unable to load your purchased products right now.</div>';
                app.showFlash(error.message || 'Unable to load order history.', 'error', 'orders-flash');
            }
        });
    </script>
@endpush
