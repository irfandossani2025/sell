{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>
    <url><loc>{{ route('products.index') }}</loc><changefreq>daily</changefreq><priority>0.9</priority></url>
    @foreach ($products as $product)
        <url><loc>{{ route('products.show', $product->slug) }}</loc><lastmod>{{ $product->updated_at->toAtomString() }}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>
    @endforeach
    @foreach (['affiliate-disclosure', 'privacy', 'terms'] as $page)
        <url><loc>{{ route('pages.show', $page) }}</loc><changefreq>yearly</changefreq><priority>0.3</priority></url>
    @endforeach
</urlset>
