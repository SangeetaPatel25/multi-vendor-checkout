@extends('layouts.app')

@section('title', 'Marketplace Home')

@section('content')
    <section class="hero-card">
        <div class="page-header">
            <div>
                <h1>Marketplace checkout without the clutter.</h1>
            </div>
            <p>
                Browse products from multiple vendors, add them to one cart, and complete a single checkout
                that becomes vendor-specific orders behind the scenes.
            </p>
        </div>

        <div class="grid products-grid">
            <div class="panel">
                <div class="eyebrow">Customer Flow</div>
                <h2>Products to payment</h2>
                <p class="muted">Login, build a mixed-vendor cart, checkout, and simulate payment confirmation.</p>
            </div>

            <div class="panel">
                <div class="eyebrow">Order Split</div>
                <h2>Vendor-aware fulfillment</h2>
                <p class="muted">One customer checkout produces multiple vendor-specific orders and payments.</p>
            </div>

            <div class="panel">
                <div class="eyebrow">Operations</div>
                <h2>Background side effects</h2>
                <p class="muted">Events, listeners, logging, and scheduled cleanup are already wired into the backend.</p>
            </div>
        </div>

        <div class="hero-actions" style="margin-top:24px;">
            <a class="button" href="{{ route('web.products') }}">Browse Products</a>
            <a class="button-secondary" href="{{ route('login') }}">Customer Login</a>
            <a class="button-secondary" href="{{ route('web.cart') }}">Open Cart</a>
        </div>
    </section>
@endsection
