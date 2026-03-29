@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <section class="auth-wrap">
        <div class="auth-card stack" style="width:min(100%,460px);">
            <div>
                <div class="eyebrow">Authentication</div>
                <h1>Sign in to continue</h1>
                <p class="muted">Use a seeded customer or admin account, or register through the API if you want a new user.</p>
            </div>

            <div class="flash" id="login-flash"></div>

            <div class="hero-actions" style="margin:0;">
                <button class="button" id="show-login-button" type="button">Login</button>
                <button class="button-secondary" id="show-register-button" type="button">Create Account</button>
            </div>

            <form class="stack" id="login-form">
                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" placeholder="customer1@example.com" required>
                </label>

                <label class="field">
                    <span>Password</span>
                    <input type="password" name="password" placeholder="password123" minlength="8" required>
                </label>

                <button class="button" type="submit">Login</button>
            </form>

            <form class="stack hidden" id="register-form">
                <label class="field">
                    <span>Name</span>
                    <input type="text" name="name" placeholder="Alice Buyer" required>
                </label>

                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" placeholder="alice@example.com" required>
                </label>

                <label class="field">
                    <span>Password</span>
                    <input type="password" name="password" placeholder="password123" minlength="8" required>
                </label>

                <button class="button" type="submit">Create Account</button>
            </form>

            <div class="panel">
                <strong>Seeded accounts</strong>
                <p class="muted">Customer: <code>customer1@example.com</code> / <code>password123</code></p>
                <p class="muted">Admin: <code>admin@example.com</code> / <code>password123</code></p>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const app = window.MultiVendorApp;
            if (app.getToken()) {
                window.location.href = '{{ route('web.products') }}';
                return;
            }

            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const loginButton = document.getElementById('show-login-button');
            const registerButton = document.getElementById('show-register-button');

            function showMode(mode) {
                const isLogin = mode === 'login';
                loginForm.classList.toggle('hidden', !isLogin);
                registerForm.classList.toggle('hidden', isLogin);
                loginButton.className = isLogin ? 'button' : 'button-secondary';
                registerButton.className = isLogin ? 'button-secondary' : 'button';
                app.clearFlash('login-flash');
            }

            loginButton.addEventListener('click', () => showMode('login'));
            registerButton.addEventListener('click', () => showMode('register'));

            loginForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                app.clearFlash('login-flash');
                const formData = new FormData(loginForm);

                try {
                    const payload = await app.api('/login', {
                        method: 'POST',
                        auth: false,
                        body: {
                            email: formData.get('email'),
                            password: formData.get('password'),
                        },
                    });

                    app.setAuth(payload.token, payload.user);
                    window.location.href = '{{ route('web.products') }}';
                } catch (error) {
                    app.showFlash(error.message || 'Unable to login.', 'error', 'login-flash');
                }
            });

            registerForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                app.clearFlash('login-flash');
                const formData = new FormData(registerForm);

                try {
                    const payload = await app.api('/register', {
                        method: 'POST',
                        auth: false,
                        body: {
                            name: formData.get('name'),
                            email: formData.get('email'),
                            password: formData.get('password'),
                        },
                    });

                    app.setAuth(payload.token, payload.user);
                    window.location.href = '{{ route('web.products') }}';
                } catch (error) {
                    app.showFlash(error.message || 'Unable to create account.', 'error', 'login-flash');
                }
            });
        });
    </script>
@endpush
