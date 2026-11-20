@extends('layouts.storefront', [
    'seoTitle' => $product->seo_title ?: $product->name.' | Hot Products',
    'seoDescription' => $product->seo_description ?: $product->short_description,
    'canonical' => route('products.show', $product->slug),
    'schemaData' => [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $product->short_description,
        'category' => $product->category?->name,
        'image' => $product->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) : null,
    ],
])

@section('content')
<section class="product-detail section-block">
    <div class="detail-visual product-visual-{{ $product->product_type }}">
        @if ($product->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">@else<span class="visual-spark" aria-hidden="true">✳</span><span class="visual-caption">{{ $product->category?->name ?? ucfirst($product->product_type) }}</span><span class="visual-title">{{ $product->name }}</span>@endif
        <span class="detail-index">HP / {{ str_pad((string) $product->id, 3, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="detail-copy"><a class="back-link" href="{{ route('products.index') }}">← Back to the shortlist</a><div class="product-meta"><span>{{ $product->category?->name ?? ucfirst($product->product_type) }}</span><span class="product-type">{{ $product->product_type === 'affiliate' ? 'Partner pick' : ucfirst($product->product_type) }}</span></div><h1>{{ $product->name }}</h1><p class="detail-summary">{{ $product->short_description }}</p>
        @if ($product->price)<div class="detail-price">{{ $product->currency }} {{ number_format((float) $product->price, 2) }}</div>@endif
        @if ($product->description)<div class="detail-description">{!! nl2br(e($product->description)) !!}</div>@endif
        @if ($product->product_type === 'affiliate')<p class="affiliate-note">This is a partner link. If you buy through it, we may earn a small commission at no extra cost to you.</p><a class="button button-dark button-wide" href="{{ route('affiliate.redirect', $product->slug) }}" rel="sponsored nofollow noopener" target="_blank">See the partner’s offer <span aria-hidden="true">↗</span></a>
        @else<a class="button button-dark button-wide" href="{{ route('checkout.show', $product->slug) }}">{{ $product->product_type === 'digital' ? 'Get this download' : 'Book this service' }} <span aria-hidden="true">↗</span></a><small class="detail-small">Secure checkout is handled by PayPal when payments are enabled.</small>@endif
        @if ($product->service_details)<div class="service-detail"><span class="eyebrow">WHAT TO EXPECT</span><p>{{ $product->service_details }}</p></div>@endif
    </div>
</section>
@if ($relatedProducts->isNotEmpty())<section class="section-block related-section"><div class="section-heading"><div><span class="eyebrow">MORE TO EXPLORE</span><h2>You might also <em>like.</em></h2></div></div><div class="product-grid">@foreach ($relatedProducts as $related)@include('storefront.partials.product-card', ['product' => $related])@endforeach</div></section>@endif
@endsection
