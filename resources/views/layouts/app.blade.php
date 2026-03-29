<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Multi-Vendor Checkout')</title>
    <style>
        :root { --bg:#f5f1e8; --surface:#fffdf7; --surface-alt:#f0e8da; --text:#1f2a2a; --muted:#5f6c6b; --accent:#0d6b5f; --accent-dark:#084c44; --line:#d8ccb8; --danger:#b44b3f; --shadow:0 16px 40px rgba(31,42,42,.08); --radius:18px; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:Georgia,"Times New Roman",serif; background:radial-gradient(circle at top left, rgba(13,107,95,.14), transparent 30%), radial-gradient(circle at bottom right, rgba(180,75,63,.12), transparent 24%), var(--bg); color:var(--text); min-height:100vh; }
        a { color:inherit; text-decoration:none; }
        button,input { font:inherit; }
        .shell { max-width:1180px; margin:0 auto; padding:24px; }
        .navbar { position:sticky; top:0; z-index:20; backdrop-filter:blur(14px); background:rgba(245,241,232,.92); border-bottom:1px solid rgba(216,204,184,.8); }
        .nav-inner { display:flex; align-items:center; justify-content:space-between; gap:16px; max-width:1180px; margin:0 auto; padding:18px 24px; }
        .brand { display:flex; flex-direction:column; gap:2px; }
        .brand-mark { font-size:.8rem; letter-spacing:.2em; text-transform:uppercase; color:var(--muted); }
        .brand-name { font-size:1.2rem; font-weight:700; }
        .nav-links,.nav-actions,.hero-actions,.stack,.order-list,.totals { display:flex; gap:12px; }
        .nav-actions,.nav-links { align-items:center; }
        .stack,.order-list,.totals { flex-direction:column; }
        .nav-link,.ghost-link { padding:10px 14px; border-radius:999px; transition:.2s ease; }
        .nav-link:hover,.ghost-link:hover { background:rgba(13,107,95,.08); }
        .cart-pill { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:999px; background:var(--surface); border:1px solid var(--line); }
        .cart-count { display:inline-flex; align-items:center; justify-content:center; min-width:24px; height:24px; padding:0 8px; border-radius:999px; background:var(--accent); color:#fff; font-size:.85rem; font-weight:700; }
        .button,.button-secondary,.button-danger { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:none; border-radius:999px; padding:12px 18px; cursor:pointer; transition:transform .15s ease, background .2s ease, opacity .2s ease; }
        .button { background:var(--accent); color:#fff; }
        .button-secondary { background:var(--surface-alt); color:var(--text); }
        .button-danger { background:var(--danger); color:#fff; }
        .button:hover,.button-secondary:hover,.button-danger:hover { transform:translateY(-1px); }
        .button[disabled] { cursor:not-allowed; opacity:.6; transform:none; }
        .page-header { display:flex; align-items:end; justify-content:space-between; gap:24px; margin-bottom:28px; }
        .page-header h1 { margin:0; font-size:clamp(2rem,5vw,3.3rem); line-height:1; }
        .page-header p { max-width:620px; margin:0; color:var(--muted); font-size:1.05rem; }
        .hero-card,.panel,.product-card,.auth-card,.summary-card,.vendor-group { background:var(--surface); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow); }
        .hero-card { padding:36px; }
        .panel,.summary-card,.auth-card { padding:24px; }
        .grid { display:grid; gap:20px; }
        .products-grid { grid-template-columns:repeat(auto-fit, minmax(240px,1fr)); }
        .product-card { padding:22px; display:flex; flex-direction:column; gap:18px; }
        .eyebrow { font-size:.8rem; letter-spacing:.14em; text-transform:uppercase; color:var(--muted); }
        .product-card h3,.vendor-group h2,.summary-card h2,.auth-card h1 { margin:0; }
        .price { font-size:1.6rem; font-weight:700; }
        .muted { color:var(--muted); }
        .flash { display:none; margin-bottom:18px; padding:14px 16px; border-radius:14px; border:1px solid transparent; }
        .flash.show { display:block; }
        .flash.success { background:rgba(13,107,95,.11); border-color:rgba(13,107,95,.25); color:var(--accent-dark); }
        .flash.error { background:rgba(180,75,63,.1); border-color:rgba(180,75,63,.24); color:#7a2f27; }
        .auth-wrap { min-height:calc(100vh - 140px); display:flex; align-items:center; justify-content:center; }
        .field { display:flex; flex-direction:column; gap:8px; }
        .field input,.quantity-controls input,.product-quantity input { width:100%; padding:14px 16px; border-radius:14px; border:1px solid var(--line); background:#fff; }
        .field input:focus,.quantity-controls input:focus,.product-quantity input:focus { outline:2px solid rgba(13,107,95,.2); border-color:var(--accent); }
        .vendor-group { padding:22px; }
        .cart-item { display:grid; grid-template-columns:1.8fr .8fr .8fr 1fr; gap:12px; align-items:center; padding:16px 0; border-top:1px solid rgba(216,204,184,.65); }
        .cart-item:first-of-type { border-top:none; }
        .quantity-controls { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .quantity-controls input { width:84px; padding:10px 12px; border-radius:12px; }
        .product-quantity { display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .product-quantity label { font-size:.9rem; color:var(--muted); }
        .product-quantity input { width:88px; padding:10px 12px; border-radius:12px; }
        .split-layout { display:grid; grid-template-columns:minmax(0,2fr) minmax(300px,1fr); gap:24px; }
        .total-row { display:flex; justify-content:space-between; gap:16px; }
        .order-pill { padding:16px; border-radius:14px; background:var(--surface-alt); }
        .empty-state { padding:40px 24px; text-align:center; color:var(--muted); border-radius:var(--radius); border:1px dashed var(--line); background:rgba(255,253,247,.65); }
        .success-mark { width:74px; height:74px; margin:0 auto 18px; border-radius:50%; background:rgba(13,107,95,.12); color:var(--accent); display:grid; place-items:center; font-size:2rem; font-weight:700; }
        .hidden { display:none !important; }
        @media (max-width:860px) {
            .nav-inner { flex-direction:column; align-items:stretch; }
            .nav-links,.nav-actions,.hero-actions { flex-wrap:wrap; }
            .page-header,.split-layout,.cart-item { display:block; }
            .page-header { margin-bottom:20px; }
            .cart-item { padding:18px 0; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-inner">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark">Marketplace Demo</span>
                <span class="brand-name">Multi-Vendor Checkout</span>
            </a>
            <div class="nav-links hidden" id="nav-main-links">
                <a class="nav-link" href="{{ route('web.products') }}">Products</a>
                <a class="cart-pill" href="{{ route('web.cart') }}">
                    <span>Cart</span>
                    <span class="cart-count" id="nav-cart-count">0</span>
                </a>
                <a class="nav-link hidden" href="{{ route('web.admin.dashboard') }}" id="nav-admin-link">Admin</a>
            </div>
            <div class="nav-actions">
                <span class="ghost-link hidden" id="nav-user-greeting"></span>
                <a class="ghost-link hidden" href="{{ route('web.account.orders') }}" id="nav-user-label">Account</a>
                <a class="button-secondary" href="{{ route('login') }}" id="nav-login-link">Login</a>
                <button class="button-danger hidden" id="nav-logout-button" type="button">Logout</button>
            </div>
        </div>
    </nav>

    <main class="shell">
        <div class="flash" id="global-flash"></div>
        @yield('content')
    </main>

    <script>
        window.MultiVendorApp = (() => {
            const tokenKey = 'mvc_token';
            const userKey = 'mvc_user';
            const checkoutKey = 'mvc_checkout_orders';
            const paymentKey = 'mvc_payment_result';
            const apiBase = '/api';

            const parseStored = (key) => {
                const raw = localStorage.getItem(key);
                if (!raw) return null;
                try { return JSON.parse(raw); } catch (error) { localStorage.removeItem(key); return null; }
            };

            const getToken = () => localStorage.getItem(tokenKey);
            const getUser = () => parseStored(userKey);
            const setAuth = (token, user) => { localStorage.setItem(tokenKey, token); localStorage.setItem(userKey, JSON.stringify(user)); };
            const clearAuth = () => { localStorage.removeItem(tokenKey); localStorage.removeItem(userKey); };
            const setCheckoutOrders = (payload) => localStorage.setItem(checkoutKey, JSON.stringify(payload));
            const getCheckoutOrders = () => parseStored(checkoutKey);
            const clearCheckoutOrders = () => localStorage.removeItem(checkoutKey);
            const setPaymentResult = (payload) => localStorage.setItem(paymentKey, JSON.stringify(payload));
            const getPaymentResult = () => parseStored(paymentKey);
            const clearPaymentResult = () => localStorage.removeItem(paymentKey);

            function showFlash(message, type = 'success', targetId = 'global-flash') {
                const flash = document.getElementById(targetId);
                if (!flash) return;
                flash.textContent = message;
                flash.className = `flash show ${type}`;
            }

            function clearFlash(targetId = 'global-flash') {
                const flash = document.getElementById(targetId);
                if (!flash) return;
                flash.textContent = '';
                flash.className = 'flash';
            }

            async function api(path, options = {}) {
                const token = getToken();
                const requestOptions = {
                    method: options.method || 'GET',
                    headers: { Accept: 'application/json', ...(options.headers || {}) },
                };

                if (options.body !== undefined) {
                    requestOptions.headers['Content-Type'] = 'application/json';
                    requestOptions.body = JSON.stringify(options.body);
                }

                if (options.auth !== false && token) {
                    requestOptions.headers.Authorization = `Bearer ${token}`;
                }

                const response = await fetch(`${apiBase}${path}`, requestOptions);
                const payload = await response.json().catch(() => ({ status_code: response.status, success: false, message: 'Unexpected response.', errors: {} }));

                if (!response.ok) {
                    if (response.status === 401) clearAuth();
                    throw payload;
                }

                return payload;
            }

            const formatCurrency = (value) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(value || 0));

            async function refreshCartCount() {
                const countEl = document.getElementById('nav-cart-count');
                if (!countEl) return;
                const user = getUser();
                if (!getToken() || user?.role === 'admin') { countEl.textContent = '0'; return; }
                try {
                    const payload = await api('/cart');
                    countEl.textContent = payload.total_items ?? 0;
                } catch (error) {
                    countEl.textContent = '0';
                }
            }

            async function setupNav() {
                const loginLink = document.getElementById('nav-login-link');
                const logoutButton = document.getElementById('nav-logout-button');
                const userGreeting = document.getElementById('nav-user-greeting');
                const userLabel = document.getElementById('nav-user-label');
                const mainLinks = document.getElementById('nav-main-links');
                const cartLink = document.querySelector('.cart-pill');
                const adminLink = document.getElementById('nav-admin-link');
                const user = getUser();

                if (user) {
                    userGreeting.textContent = user.role === 'admin'
                        ? (user.name || user.email || 'Admin')
                        : `Hi, ${user.name || user.email || 'Customer'}`;
                    userGreeting.classList.remove('hidden');
                    userLabel.textContent = user.role === 'admin'
                        ? (user.name || user.email || 'Admin')
                        : 'Your Orders';
                    userLabel.classList.remove('hidden');
                    loginLink.classList.add('hidden');
                    logoutButton.classList.remove('hidden');
                    mainLinks?.classList.remove('hidden');
                    adminLink?.classList.toggle('hidden', user.role !== 'admin');
                    cartLink?.classList.toggle('hidden', user.role === 'admin');
                    userLabel.setAttribute('href', user.role === 'admin' ? '{{ route('web.admin.dashboard') }}' : '{{ route('web.account.orders') }}');
                } else {
                    userGreeting.textContent = '';
                    userGreeting.classList.add('hidden');
                    userLabel.textContent = '';
                    userLabel.classList.add('hidden');
                    loginLink.classList.remove('hidden');
                    logoutButton.classList.add('hidden');
                    mainLinks?.classList.add('hidden');
                    adminLink?.classList.add('hidden');
                    cartLink?.classList.remove('hidden');
                    userLabel.setAttribute('href', '{{ route('web.account.orders') }}');
                }

                logoutButton?.addEventListener('click', async () => {
                    try {
                        if (getToken()) await api('/logout', { method: 'POST' });
                    } catch (error) {
                    } finally {
                        clearAuth();
                        clearCheckoutOrders();
                        clearPaymentResult();
                        window.location.href = '{{ route('login') }}';
                    }
                });

                await refreshCartCount();
            }

            function requireAuth(redirectUrl = '{{ route('login') }}') {
                if (!getToken()) {
                    window.location.href = redirectUrl;
                    return false;
                }
                return true;
            }

            return { api, clearAuth, clearCheckoutOrders, clearFlash, clearPaymentResult, formatCurrency, getCheckoutOrders, getPaymentResult, getToken, getUser, refreshCartCount, requireAuth, setAuth, setCheckoutOrders, setPaymentResult, setupNav, showFlash };
        })();

        document.addEventListener('DOMContentLoaded', () => {
            window.MultiVendorApp.setupNav();
        });
    </script>
    @stack('scripts')
</body>
</html>
