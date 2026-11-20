@extends('layouts.storefront', [
    'seoTitle' => 'Shop the shortlist | Hot Products',
    'seoDescription' => 'Browse useful everyday products, digital downloads and services picked by Hot Products.',
])

@section('content')
<section class="page-hero page-hero-shop"><div><span class="eyebrow">A WELL-CURATED LITTLE CORNER</span><h1>The <em>shortlist.</em></h1><p>Useful finds, interesting ideas and a few things you didn’t know you needed yet.</p></div><div class="page-hero-stamp">GOOD<br>STUFF<br><span>↘</span></div></section>
<section class="section-block shop-section">
    <form class="filter-bar" method="get" action="{{ route('products.index') }}"><label class="search-field"><span aria-hidden="true">⌕</span><input type="search" name="q" value="{{ $search }}" placeholder="Search the shortlist" aria-label="Search products"></label><label><span class="sr-only">Category</span><select name="category"><option value="">Every category</option>@foreach ($categories as $item)<option value="{{ $item->slug }}" @selected($category === $item->slug)>{{ $item->name }}</option>@endforeach</select></label><label><span class="sr-only">Type</span><select name="type"><option value="">Everything</option><option value="affiliate" @selected($type === 'affiliate')>Partner finds</option><option value="digital" @selected($type === 'digital')>Digital goods</option><option value="service" @selected($type === 'service')>Services</option></select></label><button class="button button-dark button-small" type="submit">Find it <span aria-hidden="true">↗</span></button></form>
    @if ($products->isNotEmpty())<div class="product-grid">@foreach ($products as $product)@include('storefront.partials.product-card', ['product' => $product])@endforeach</div><div class="pagination-wrap">{{ $products->links() }}</div>@else<div class="empty-collection"><span class="empty-spark" aria-hidden="true">✳</span><span class="eyebrow">NOTHING ON THIS PAGE JUST YET</span><h3>Try another search.</h3><p>We’re always adding new finds. Clear a filter or check back soon.</p><a class="button button-dark" href="{{ route('products.index') }}">Clear filters</a></div>@endif
</section>
@endsection
