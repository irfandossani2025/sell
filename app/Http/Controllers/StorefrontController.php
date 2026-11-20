<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\SiteEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        $this->recordEvent('page_view');

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('status', 'published')])
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::query()
            ->published()
            ->featured()
            ->with('category')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->limit(4)
            ->get();

        $latestProducts = Product::query()
            ->published()
            ->with('category')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->limit(8)
            ->get();

        $reviews = Review::query()->approved()->latest('approved_at')->limit(3)->get();

        return view('storefront.home', compact('categories', 'featuredProducts', 'latestProducts', 'reviews'));
    }

    public function products(Request $request): View
    {
        $this->recordEvent('page_view');
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $type = (string) $request->query('type', '');

        $products = Product::query()
            ->published()
            ->with('category')
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search): void {
                $needle = '%'.mb_strtolower($search).'%';
                $nested->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(short_description) LIKE ?', [$needle]);
            }))
            ->when($category !== '', fn ($query) => $query->whereHas('category', fn ($nested) => $nested->where('slug', $category)))
            ->when(in_array($type, ['affiliate', 'digital', 'service'], true), fn ($query) => $query->where('product_type', $type))
            ->orderBy('sort_order')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('storefront.products.index', compact('products', 'categories', 'search', 'category', 'type'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === 'published', 404);
        $product->load('category');
        $this->recordEvent('product_view', $product);

        $relatedProducts = Product::query()
            ->published()
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
            ->with('category')
            ->limit(3)
            ->get();

        return view('storefront.products.show', compact('product', 'relatedProducts'));
    }

    public function affiliateRedirect(Product $product): RedirectResponse
    {
        abort_unless($product->status === 'published' && $product->product_type === 'affiliate' && filled($product->affiliate_url), 404);
        $this->recordEvent('affiliate_click', $product);

        return redirect()->away($product->affiliate_url, 302, ['Referrer-Policy' => 'no-referrer']);
    }

    public function robots(): \Illuminate\Http\Response
    {
        return Response::make("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /checkout\nSitemap: ".route('sitemap')."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): \Illuminate\Http\Response
    {
        $products = Product::query()->published()->orderBy('updated_at')->get();

        return response()->view('storefront.sitemap', compact('products'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function page(string $page): View
    {
        abort_unless(in_array($page, ['affiliate-disclosure', 'privacy', 'terms'], true), 404);

        return view('storefront.pages.'.$page);
    }

    private function recordEvent(string $eventType, ?Product $product = null): void
    {
        SiteEvent::query()->create([
            'product_id' => $product?->id,
            'event_type' => $eventType,
            'occurred_at' => now(),
        ]);
    }
}
