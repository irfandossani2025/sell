<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} | Hot Products Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell">
    <header class="admin-topbar">
        <a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></span><span>hot<span class="brand-light">products</span></span></a>
        <div class="admin-topbar-actions"><a href="{{ route('home') }}" target="_blank" rel="noopener">View website ↗</a><form method="post" action="{{ route('admin.logout') }}">@csrf<button type="submit">Sign out</button></form></div>
    </header>
    <div class="admin-layout">
        <aside class="admin-sidebar" aria-label="Admin navigation">
            <span class="admin-sidebar-label">YOUR WORKSPACE</span>
            <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif><span>◫</span> Overview</a>
            <a href="{{ route('admin.products.index') }}" @if (request()->routeIs('admin.products.*')) aria-current="page" @endif><span>✳</span> Products</a>
            <a href="{{ route('admin.reviews.index') }}" @if (request()->routeIs('admin.reviews.*')) aria-current="page" @endif><span>☆</span> Reviews</a>
            <a href="{{ route('admin.orders.index') }}" @if (request()->routeIs('admin.orders.*')) aria-current="page" @endif><span>↗</span> Orders</a>
        </aside>
        <main class="admin-content">
            @if (session('status'))<div class="admin-flash" role="status">{{ session('status') }}</div>@endif
            @yield('content')
        </main>
    </div>
</body>
</html>
