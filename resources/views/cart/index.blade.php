@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <section class="split-layout">
        <div class="stack">
            <div class="page-header">
                <div>
                    <div class="eyebrow">Cart</div>
                    <h1>Your vendor-grouped basket</h1>
                </div>
                <p>Each vendor section below will become its own order when you checkout.</p>
            </div>

            <div class="flash" id="cart-flash"></div>
            <div id="cart-groups" class="stack">
                <div class="empty-state">Loading cart...</div>
            </div>
        </div>

        <aside class="summary-card">
            <div class="eyebrow">Summary</div>
            <h2>Ready to checkout?</h2>
            <div class="totals">
                <div class="total-row">
                    <span class="muted">Items</span>
                    <strong id="cart-total-items">0</strong>
                </div>
            </div>
            <p class="muted">Checkout will create pending orders, then the payment page will confirm them.</p>
            <button class="button" id="checkout-button" type="button" disabled>Checkout</button>
        </aside>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const app = window.MultiVendorApp;

            if (!app.requireAuth()) {
                return;
            }

            const groupsContainer = document.getElementById('cart-groups');
            const totalItemsEl = document.getElementById('cart-total-items');
            const checkoutButton = document.getElementById('checkout-button');

            async function loadCart() {
                app.clearFlash('cart-flash');

                try {
                    const payload = await app.api('/cart');
                    const cart = payload.cart || {};
                    const vendors = Object.entries(cart);
                    totalItemsEl.textContent = payload.total_items ?? 0;
                    checkoutButton.disabled = !payload.total_items;

                    if (!vendors.length) {
                        groupsContainer.innerHTML = '<div class="empty-state">Your cart is empty. Add a few products to continue.</div>';
                        return;
                    }

                    groupsContainer.innerHTML = vendors.map(([vendor, items]) => `
                        <section class="vendor-group">
                            <div class="eyebrow">Vendor</div>
                            <h2>${vendor}</h2>
                            ${items.map((item) => `
                                <div class="cart-item">
                                    <div>
                                        <strong>${item.product.name}</strong>
                                        <div class="muted">${app.formatCurrency(item.product.price)} each</div>
                                    </div>
                                    <div>
                                        <span class="muted">Subtotal</span>
                                        <div><strong>${app.formatCurrency(item.product.price * item.quantity)}</strong></div>
                                    </div>
                                    <div class="quantity-controls">
                                        <input
                                            type="number"
                                            min="1"
                                            value="${item.quantity}"
                                            data-quantity-input="${item.id}"
                                            data-original-quantity="${item.quantity}"
                                        >
                                    </div>
                                    <div style="display:flex;justify-content:flex-end;">
                                        <button class="button-danger" data-remove-id="${item.id}">Remove</button>
                                    </div>
                                </div>
                            `).join('')}
                        </section>
                    `).join('');

                    groupsContainer.querySelectorAll('[data-quantity-input]').forEach((input) => {
                        input.addEventListener('change', async () => {
                            const itemId = Number(input.dataset.quantityInput);
                            const quantity = Math.max(1, Number(input.value || 1));

                            if (String(quantity) === input.dataset.originalQuantity) {
                                return;
                            }

                            input.disabled = true;

                            try {
                                const payload = await app.api('/cart/update', {
                                    method: 'PUT',
                                    body: {
                                        cart_item_id: itemId,
                                        quantity,
                                    },
                                });

                                app.showFlash(payload.message || 'Cart updated.', 'success', 'cart-flash');
                                app.refreshCartCount();
                                loadCart();
                            } catch (error) {
                                input.value = input.dataset.originalQuantity;
                                app.showFlash(error.message || 'Unable to update cart item.', 'error', 'cart-flash');
                            } finally {
                                input.disabled = false;
                            }
                        });
                    });

                    groupsContainer.querySelectorAll('[data-remove-id]').forEach((button) => {
                        button.addEventListener('click', async () => {
                            try {
                                const payload = await app.api('/cart/remove', {
                                    method: 'DELETE',
                                    body: {
                                        cart_item_id: Number(button.dataset.removeId),
                                    },
                                });

                                app.showFlash(payload.message || 'Item removed.', 'success', 'cart-flash');
                                app.refreshCartCount();
                                loadCart();
                            } catch (error) {
                                app.showFlash(error.message || 'Unable to remove cart item.', 'error', 'cart-flash');
                            }
                        });
                    });
                } catch (error) {
                    groupsContainer.innerHTML = '<div class="empty-state">Unable to load cart right now.</div>';
                    checkoutButton.disabled = true;
                    app.showFlash(error.message || 'Unable to load cart.', 'error', 'cart-flash');
                }
            }

            checkoutButton.addEventListener('click', async () => {
                try {
                    const payload = await app.api('/checkout', { method: 'POST' });
                    app.setCheckoutOrders(payload);
                    app.refreshCartCount();
                    window.location.href = '{{ route('web.checkout.payment') }}';
                } catch (error) {
                    app.showFlash(error.message || 'Checkout failed.', 'error', 'cart-flash');
                }
            });

            await loadCart();
        });
    </script>
@endpush
