@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <section>
        <div class="page-header">
            <div>
                <div class="eyebrow">Catalog</div>
                <h1>Shop across vendors</h1>
            </div>
            <p>
                This page uses the existing API to fetch marketplace products, then adds items to a single
                customer cart while preserving vendor grouping for checkout.
            </p>
        </div>

        <div class="flash" id="products-flash"></div>
        <div class="grid products-grid" id="products-grid">
            <div class="empty-state">Loading products...</div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const app = window.MultiVendorApp;
            const grid = document.getElementById('products-grid');
            const pageTitle = document.querySelector('.page-header h1');
            const pageDescription = document.querySelector('.page-header p');
            const currentUser = app.getUser();
            const isAdmin = currentUser?.role === 'admin';
            let cartQuantities = {};

            async function loadCartQuantities() {
                cartQuantities = {};

                if (!app.getToken()) {
                    return;
                }

                try {
                    const payload = await app.api('/cart');
                    const groups = Object.values(payload.cart || {});

                    groups.forEach((items) => {
                        items.forEach((item) => {
                            cartQuantities[item.product.id] = item.quantity;
                        });
                    });
                } catch (error) {
                    cartQuantities = {};
                }
            }

            function renderProducts(products) {
                if (!products.length) {
                    grid.innerHTML = '<div class="empty-state">No products available yet.</div>';
                    return;
                }

                if (isAdmin) {
                    pageTitle.textContent = 'Inventory across vendors';
                    pageDescription.textContent = 'Review the shared marketplace catalog here. Use the Admin portal to add new products and manage inventory.';

                    grid.innerHTML = products.map((product) => `
                        <article class="product-card">
                            <div class="stack">
                                <div class="eyebrow">${product.vendor.name}</div>
                                <h3>${product.name}</h3>
                                <div class="price">${app.formatCurrency(product.price)}</div>
                                <div class="muted">Stock available: ${product.stock}</div>
                            </div>
                            <a class="button-secondary" href="{{ route('web.admin.dashboard') }}">
                                Manage In Admin
                            </a>
                        </article>
                    `).join('');

                    return;
                }

                grid.innerHTML = products.map((product) => `
                    <article class="product-card">
                        <div class="stack">
                            <div class="eyebrow">${product.vendor.name}</div>
                            <h3>${product.name}</h3>
                            <div class="price">${app.formatCurrency(product.price)}</div>
                            <div class="muted">Stock available: ${product.stock}</div>
                        </div>
                        <div class="product-quantity">
                            <label for="quantity-${product.id}">Quantity</label>
                            <input
                                id="quantity-${product.id}"
                                type="number"
                                min="0"
                                max="${product.stock}"
                                value="${cartQuantities[product.id] ?? 0}"
                                data-quantity-input="${product.id}"
                            >
                        </div>
                        <button class="button add-to-cart" data-product-id="${product.id}">
                            Add To Cart
                        </button>
                    </article>
                `).join('');

                grid.querySelectorAll('.add-to-cart').forEach((button) => {
                    button.addEventListener('click', async () => {
                        if (!app.getToken()) {
                            window.location.href = '{{ route('login') }}';
                            return;
                        }

                        const quantityInput = grid.querySelector(`[data-quantity-input="${button.dataset.productId}"]`);
                        const quantity = Number(quantityInput?.value || 0);

                        if (quantity < 1) {
                            app.showFlash('Choose at least 1 item before adding to cart.', 'error', 'products-flash');
                            return;
                        }

                        try {
                            const payload = await app.api('/cart/add', {
                                method: 'POST',
                                body: {
                                    product_id: Number(button.dataset.productId),
                                    quantity,
                                },
                            });

                            app.showFlash(payload.message || 'Added to cart.', 'success', 'products-flash');
                            cartQuantities[Number(button.dataset.productId)] = (cartQuantities[Number(button.dataset.productId)] || 0) + quantity;
                            quantityInput.value = String(cartQuantities[Number(button.dataset.productId)]);
                            app.refreshCartCount();
                        } catch (error) {
                            app.showFlash(error.message || 'Unable to add item to cart.', 'error', 'products-flash');
                        }
                    });
                });
            }

            try {
                if (!isAdmin) {
                    await loadCartQuantities();
                }
                const payload = await app.api('/products', { auth: false });
                renderProducts(payload.products || []);
            } catch (error) {
                grid.innerHTML = '<div class="empty-state">Unable to load products right now.</div>';
                app.showFlash(error.message || 'Unable to load products.', 'error', 'products-flash');
            }
        });
    </script>
@endpush
