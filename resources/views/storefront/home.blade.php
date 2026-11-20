@extends('layouts.storefront', [
    'seoTitle' => 'Hot Products — good finds, chosen with care',
    'seoDescription' => 'A fresh shortlist of clever everyday products, useful digital downloads and services worth knowing about.',
    'canonical' => route('home'),
    'schemaData' => [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'Hot Products',
        'url' => route('home'),
        'description' => 'Thoughtfully curated products, downloads and services.',
    ],
])

@section('content')
<section class="hero-wrap">
    <div class="hero-copy">
        <div class="eyebrow"><span class="eyebrow-line"></span> A LITTLE LESS SCROLLING. A LOT MORE GOOD STUFF.</div>
        <h1>Good finds.<br><em>Found for you.</em></h1>
        <p>Useful things, interesting ideas and services that earn a spot in your day. Handpicked, never endless.</p>
        <div class="hero-actions"><a class="button button-dark" href="{{ route('products.index') }}">Explore the shortlist <span aria-hidden="true">↗</span></a><a class="text-link" href="#categories">Browse by vibe <span aria-hidden="true">↓</span></a></div>
        <div class="hero-proof"><span class="proof-stars" aria-label="Five star picks">★★★★★</span><span>A growing collection of things worth sharing</span></div>
    </div>
    <div class="hero-art" aria-label="A colourful collection of useful everyday finds">
        <div class="hero-art-grid"></div>
        <span class="orbit orbit-one"></span><span class="orbit orbit-two"></span>
        <div class="hero-circle"><span class="hero-circle-small">THE GOOD<br>STUFF CLUB</span><span class="hero-circle-mark">✳</span><span class="hero-circle-bottom">CURATED · EVERYDAY</span></div>
        <div class="floating-note note-one"><span class="note-icon">↗</span><span><b>Picked with purpose</b><small>Not just another list</small></span></div>
        <div class="floating-note note-two"><span class="note-check">✓</span><span><b>Worth a closer look</b><small>Curiosity starts here</small></span></div>
        <span class="hero-art-label">HP / 001</span>
    </div>
</section>

<section class="trust-strip" aria-label="What you can find here"><span>FOR YOUR HOME</span><i></i><span>FOR YOUR WORK</span><i></i><span>FOR YOUR NEXT IDEA</span><i></i><span>FOR A BETTER EVERYDAY</span></section>

<section class="section-block categories-section" id="categories">
    <div class="section-heading"><div><span class="eyebrow">FIND YOUR KIND OF GOOD</span><h2>Browse by <em>interest.</em></h2></div><a class="text-link" href="{{ route('products.index') }}">All categories <span aria-hidden="true">↗</span></a></div>
    <div class="category-grid">
        @foreach ($categories as $category)
            <a class="category-tile category-tone-{{ $loop->iteration % 4 }}" href="{{ route('products.index', ['category' => $category->slug]) }}">
                <span class="category-number">0{{ $loop->iteration }}</span><span class="category-icon" aria-hidden="true">{{ ['✳', '◈', '⌘', '↗'][$loop->index % 4] }}</span>
                <span class="category-title">{{ $category->name }}</span><span class="category-count">{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('find', $category->products_count) }}</span><span class="category-arrow" aria-hidden="true">↗</span>
            </a>
        @endforeach
    </div>
</section>

@if ($featuredProducts->isNotEmpty())
<section class="section-block featured-section">
    <div class="section-heading"><div><span class="eyebrow">THE ONES THAT CAUGHT OUR EYE</span><h2>Current <em>favourites.</em></h2></div><a class="text-link" href="{{ route('products.index') }}">See every find <span aria-hidden="true">↗</span></a></div>
    <div class="product-grid product-grid-featured">@foreach ($featuredProducts as $product)@include('storefront.partials.product-card', ['product' => $product])@endforeach</div>
</section>
@endif

<section class="banner-section">
    <div class="banner-mark" aria-hidden="true">✳</div><div><span class="eyebrow">A GOOD PLACE TO START</span><h2>Curiosity looks<br><em>good on you.</em></h2></div><a class="button button-lime" href="{{ route('products.index') }}">See what’s new <span aria-hidden="true">↗</span></a>
</section>

<section class="section-block latest-section">
    <div class="section-heading"><div><span class="eyebrow">FRESH FROM THE SHORTLIST</span><h2>New and <em>noteworthy.</em></h2></div><a class="text-link" href="{{ route('products.index') }}">Explore everything <span aria-hidden="true">↗</span></a></div>
    @if ($latestProducts->isNotEmpty())
        <div class="product-grid">@foreach ($latestProducts as $product)@include('storefront.partials.product-card', ['product' => $product])@endforeach</div>
    @else
        <div class="empty-collection"><span class="empty-spark" aria-hidden="true">✳</span><span class="eyebrow">FIRST FINDS ARE ON THE WAY</span><h3>We’re making room for the good stuff.</h3><p>Our shortlist is getting its first picks together. Come back soon for products, digital goods and helpful services.</p></div>
    @endif
</section>

<section class="community-section" id="reviews">
    <div class="community-intro"><span class="eyebrow">THE COMMUNITY CORNER</span><h2>Have you been<br>here before?</h2><p>Tell us what you think. Your note helps make this little corner of the internet better for everyone.</p><a class="text-link" href="#review-form">Leave a review <span aria-hidden="true">↓</span></a></div>
    <div class="review-side">
        @forelse ($reviews as $review)
            <article class="review-card"><div class="review-stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div><p>“{{ $review->body }}”</p><div class="review-author"><span class="avatar-dot">{{ mb_substr($review->reviewer_name, 0, 1) }}</span><span><b>{{ $review->reviewer_name }}</b><small>{{ $review->approved_at?->format('M Y') }}</small></span></div></article>
        @empty
            <article class="review-card review-card-empty"><span class="empty-spark" aria-hidden="true">✳</span><p>Be the first to share what you think of Hot Products.</p></article>
        @endforelse
    </div>
</section>

<section class="review-form-section" id="review-form">
    <div class="form-intro"><span class="eyebrow">YOUR WORDS, YOUR WAY</span><h2>Leave a little<br><em>love note.</em></h2><p>Reviews appear after a quick check by the site owner. Thanks for helping us keep this place kind and useful.</p></div>
    <form class="review-form" method="post" action="{{ route('reviews.store') }}">
        @csrf
        @if (session('review_success'))<div class="form-success" role="status">Thanks for sharing. Your review is waiting for approval.</div>@endif
        @if ($errors->any())<div class="form-error" role="alert">Please check the highlighted details and try again.</div>@endif
        <div class="form-row"><label>Your name<input type="text" name="name" maxlength="120" value="{{ old('name') }}" required autocomplete="name"></label><label>Email <span>(optional)</span><input type="email" name="email" maxlength="255" value="{{ old('email') }}" autocomplete="email"></label></div>
        @error('name')<small class="field-error">{{ $message }}</small>@enderror @error('email')<small class="field-error">{{ $message }}</small>@enderror
        <label>How was your visit?<select name="rating" required><option value="">Choose a rating</option>@foreach ([5 => '★★★★★ — Loved it', 4 => '★★★★☆ — Really liked it', 3 => '★★★☆☆ — It was good', 2 => '★★☆☆☆ — Could be better', 1 => '★☆☆☆☆ — Not for me'] as $value => $label)<option value="{{ $value }}" @selected(old('rating') == $value)>{{ $label }}</option>@endforeach</select></label>
        @error('rating')<small class="field-error">{{ $message }}</small>@enderror
        <label>Give your review a title <span>(optional)</span><input type="text" name="title" maxlength="160" value="{{ old('title') }}"></label>
        @error('title')<small class="field-error">{{ $message }}</small>@enderror
        <label>Your review<textarea name="review" rows="4" minlength="12" maxlength="1500" required>{{ old('review') }}</textarea></label>
        @error('review')<small class="field-error">{{ $message }}</small>@enderror
        <label class="honeypot" aria-hidden="true">Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        <button class="button button-dark" type="submit">Send for review <span aria-hidden="true">↗</span></button><small class="form-note">We’ll review your note before it appears publicly.</small>
    </form>
</section>
@endsection
