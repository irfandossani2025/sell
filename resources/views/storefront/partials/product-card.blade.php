<article class="product-card">
    <a class="product-visual product-visual-{{ $product->product_type }}" href="{{ route('products.show', $product->slug) }}" aria-label="View {{ $product->name }}">
        @if ($product->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <span class="visual-spark" aria-hidden="true">✳</span>
            <span class="visual-caption">{{ $product->category?->name ?? ucfirst($product->product_type) }}</span>
            <span class="visual-title">{{ $product->name }}</span>
        @endif
        @if ($product->is_featured)<span class="product-tag">EDITOR’S PICK</span>@endif
    </a>
    <div class="product-card-copy">
        <div class="product-meta"><span>{{ $product->category?->name ?? ucfirst($product->product_type) }}</span><span class="product-type">{{ $product->product_type === 'affiliate' ? 'Partner pick' : ucfirst($product->product_type) }}</span></div>
        <h3><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <p>{{ $product->short_description }}</p>
        <div class="product-card-bottom">
            @if ($product->price)<span class="product-price">{{ $product->currency }} {{ number_format((float) $product->price, 2) }}</span>@else<span class="product-price product-price-muted">Explore the find</span>@endif
            <a class="circle-arrow" href="{{ route('products.show', $product->slug) }}" aria-label="See {{ $product->name }}">↗</a>
        </div>
    </div>
</article>
