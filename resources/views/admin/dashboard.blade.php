@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <section class="stack">
        <div class="page-header">
            <div>
                <div class="eyebrow">Admin Portal</div>
                <h1>Operational overview</h1>
            </div>
            <p>Track platform totals, manage inventory, and see who purchased each product from one admin workspace.</p>
        </div>

        <div class="flash" id="admin-flash"></div>

        <div class="grid products-grid" id="admin-stats-cards">
            <div class="panel">Loading admin stats...</div>
        </div>

        <section class="split-layout">
            <div class="panel stack">
                <div>
                    <div class="eyebrow">Catalog</div>
                    <h2>All products</h2>
                </div>
                <div id="admin-products" class="stack">
                    <div class="empty-state">Loading products...</div>
                </div>
            </div>

            <aside class="summary-card stack">
                <div>
                    <div class="eyebrow">Add Product</div>
                    <h2>Create inventory</h2>
                </div>
                <form class="stack" id="admin-product-form">
                    <label class="field">
                        <span>Product name</span>
                        <input type="text" name="name" placeholder="New marketplace item" required>
                    </label>
                    <label class="field">
                        <span>Price</span>
                        <input type="number" name="price" step="0.01" min="0.01" placeholder="49.99" required>
                    </label>
                    <label class="field">
                        <span>Stock</span>
                        <input type="number" name="stock" min="0" placeholder="25" required>
                    </label>
                    <label class="field">
                        <span>Vendor</span>
                        <select name="vendor_id" id="admin-vendor-select" style="width:100%;padding:14px 16px;border-radius:14px;border:1px solid var(--line);background:#fff;" required>
                            <option value="">Select a vendor</option>
                        </select>
                    </label>
                    <button class="button" type="submit">Add Product</button>
                </form>
            </aside>
        </section>

        <section class="panel stack">
            <div>
                <div class="eyebrow">Product Buyers</div>
                <h2>Who purchased what</h2>
            </div>
            <div id="admin-product-buyers" class="stack">
                <div class="empty-state">Loading product buyer insights...</div>
            </div>
        </section>

        <section class="panel stack">
            <div>
                <div class="eyebrow">Latest Orders</div>
                <h2>Recent marketplace activity</h2>
            </div>
            <div id="admin-orders" class="stack">
                <div class="empty-state">Loading orders...</div>
            </div>
        </section>
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

            if (!user || user.role !== 'admin') {
                window.location.href = '{{ route('web.products') }}';
                return;
            }

            const statsWrap = document.getElementById('admin-stats-cards');
            const productsWrap = document.getElementById('admin-products');
            const buyersWrap = document.getElementById('admin-product-buyers');
            const ordersWrap = document.getElementById('admin-orders');
            const vendorSelect = document.getElementById('admin-vendor-select');
            const productForm = document.getElementById('admin-product-form');

            function renderProducts(products) {
                if (!products.length) {
                    productsWrap.innerHTML = '<div class="empty-state">No products have been added yet.</div>';
                    return;
                }

                productsWrap.innerHTML = products.map((product) => `
                    <div class="order-pill">
                        <strong>${product.name}</strong>
                        <div class="muted">${product.vendor.name}</div>
                        <div class="muted">Price: ${app.formatCurrency(product.price)} | Stock: ${product.stock}</div>
                    </div>
                `).join('');
            }

            try {
                const [statsPayload, buyersPayload, ordersPayload, vendorsPayload, productsPayload] = await Promise.all([
                    app.api('/admin/stats'),
                    app.api('/admin/product-buyers'),
                    app.api('/admin/orders'),
                    app.api('/admin/vendors'),
                    app.api('/admin/products'),
                ]);

                const stats = statsPayload.stats;
                statsWrap.innerHTML = `
                    <div class="panel"><div class="eyebrow">Orders</div><h2>${stats.total_orders}</h2><p class="muted">Total marketplace orders</p></div>
                    <div class="panel"><div class="eyebrow">Pending</div><h2>${stats.pending_orders}</h2><p class="muted">Orders awaiting payment</p></div>
                    <div class="panel"><div class="eyebrow">Completed</div><h2>${stats.completed_orders}</h2><p class="muted">Successfully paid orders</p></div>
                    <div class="panel"><div class="eyebrow">Revenue</div><h2>${app.formatCurrency(stats.total_revenue)}</h2><p class="muted">Completed-order revenue</p></div>
                `;

                renderProducts(productsPayload.products || []);
                vendorSelect.innerHTML = '<option value="">Select a vendor</option>' + (vendorsPayload.vendors || []).map((vendor) => `
                    <option value="${vendor.id}">${vendor.name}</option>
                `).join('');

                if (buyersPayload.product_buyers?.length) {
                    buyersWrap.innerHTML = buyersPayload.product_buyers.map((entry) => `
                        <div class="vendor-group">
                            <div class="eyebrow">${entry.product.vendor}</div>
                            <h2>${entry.product.name}</h2>
                            <p class="muted">Units sold: ${entry.units_sold}</p>
                            <div class="stack">
                                ${entry.buyers.map((buyer) => `
                                    <div class="order-pill">
                                        <strong>${buyer.name}</strong>
                                        <div class="muted">${buyer.email}</div>
                                        <div class="muted">Orders: ${buyer.orders_count} | Quantity bought: ${buyer.quantity_bought}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('');
                } else {
                    buyersWrap.innerHTML = '<div class="empty-state">No completed purchases yet.</div>';
                }

                const orders = ordersPayload.orders?.data || [];
                if (orders.length) {
                    ordersWrap.innerHTML = orders.map((order) => `
                        <div class="order-pill">
                            <strong>Order #${order.id}</strong>
                            <div class="muted">Customer: ${order.user.name} (${order.user.email})</div>
                            <div class="muted">Vendor: ${order.vendor.name}</div>
                            <div class="muted">Status: ${order.status} | Total: ${app.formatCurrency(order.total)}</div>
                        </div>
                    `).join('');
                } else {
                    ordersWrap.innerHTML = '<div class="empty-state">No orders available.</div>';
                }

                productForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    app.clearFlash('admin-flash');
                    const formData = new FormData(productForm);

                    try {
                        const payload = await app.api('/admin/products', {
                            method: 'POST',
                            body: {
                                name: formData.get('name'),
                                price: Number(formData.get('price')),
                                stock: Number(formData.get('stock')),
                                vendor_id: Number(formData.get('vendor_id')),
                            },
                        });

                        app.showFlash(payload.message || 'Product created successfully.', 'success', 'admin-flash');
                        productForm.reset();
                        renderProducts([payload.product, ...(productsPayload.products || [])]);
                        productsPayload.products = [payload.product, ...(productsPayload.products || [])];
                    } catch (error) {
                        app.showFlash(error.message || 'Unable to create product.', 'error', 'admin-flash');
                    }
                });
            } catch (error) {
                app.showFlash(error.message || 'Unable to load admin dashboard.', 'error', 'admin-flash');
                statsWrap.innerHTML = '<div class="panel">Unable to load stats.</div>';
                productsWrap.innerHTML = '<div class="empty-state">Unable to load products.</div>';
                buyersWrap.innerHTML = '<div class="empty-state">Unable to load product buyer insights.</div>';
                ordersWrap.innerHTML = '<div class="empty-state">Unable to load orders.</div>';
            }
        });
    </script>
@endpush
