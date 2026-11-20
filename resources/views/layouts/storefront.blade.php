<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoTitle ?? 'Hot Products — smart finds for everyday life' }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'A thoughtfully curated shortlist of useful products, digital goods and services.' }}">
    <link rel="canonical" href="{{ $canonical ?? request()->url() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ $seoTitle ?? 'Hot Products — smart finds for everyday life' }}">
    <meta property="og:description" content="{{ $seoDescription ?? 'A thoughtfully curated shortlist of useful products, digital goods and services.' }}">
    <meta property="og:url" content="{{ $canonical ?? request()->url() }}">
    <meta name="theme-color" content="#18201b">
    @if (! empty($schemaData))
        <script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="site-shell">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="top-note"><span class="top-note-dot"></span> THE SHORTLIST FOR BETTER EVERYDAY FINDS</div>
    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Hot Products home">
                <span class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                <span>hot<span class="brand-light">products</span><small>.techit</small></span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle>
                <span></span><span></span><span class="sr-only">Open navigation</span>
            </button>
            <nav class="primary-nav" id="primary-navigation" data-menu>
                <a href="{{ route('products.index') }}" @if (request()->routeIs('products.*')) aria-current="page" @endif>Shop all</a>
                <a href="{{ route('home') }}#categories">Categories</a>
                <a href="{{ route('home') }}#reviews">Community</a>
            </nav>
            <a class="header-cta" href="{{ route('products.index') }}">Find your next fave <span aria-hidden="true">↗</span></a>
        </div>
    </header>

    @if (session('status'))
        <div class="flash-note" role="status">{{ session('status') }}</div>
    @endif

    <main id="main-content">@yield('content')</main>

    <footer class="site-footer">
        <div class="footer-main">
            <div class="footer-brand-column">
                <a class="brand brand-footer" href="{{ route('home') }}">
                    <span class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span>hot<span class="brand-light">products</span><small>.techit</small></span>
                </a>
                <p>Good finds, chosen with a little more care.</p>
            </div>
            <div class="footer-links">
                <span class="footer-label">GOOD TO KNOW</span>
                <a href="{{ route('pages.show', 'affiliate-disclosure') }}">Affiliate disclosure</a>
                <a href="{{ route('pages.show', 'privacy') }}">Privacy</a>
                <a href="{{ route('pages.show', 'terms') }}">Terms</a>
            </div>
            <div class="footer-links">
                <span class="footer-label">NEED A HAND?</span>
                <a href="mailto:support@techittechnologies.com">Contact support</a>
                <a href="{{ route('home') }}#review-form">Leave a review</a>
            </div>
        </div>
        <div class="footer-bottom"><span>© {{ now()->year }} Hot Products</span><span>Useful things. Better choices.</span></div>
    </footer>
    @stack('scripts')
</body>
</html>
