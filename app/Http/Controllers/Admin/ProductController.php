<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->with('category')->latest()->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['currency' => config('services.paypal.currency', 'USD')]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $product = new Product;
        $this->saveProduct($request, $product, $validated);

        return redirect()->route('admin.products.index')->with('status', 'Product saved.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validatedData($request, $product);
        $this->saveProduct($request, $product, $validated);

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        if ($product->digital_file_path && ! $this->fileHasOrders($product->digital_file_path)) {
            Storage::disk('local')->delete($product->digital_file_path);
        }
        $product->delete();

        return back()->with('status', 'Product removed.');
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', Rule::unique('products', 'slug')->ignore($product?->id)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'product_type' => ['required', Rule::in(['affiliate', 'digital', 'service'])],
            'short_description' => ['required', 'string', 'max:240'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99', 'required_if:product_type,digital,service'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'affiliate_url' => ['nullable', 'url', 'max:2048', 'required_if:product_type,affiliate'],
            'service_details' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'sort_order' => ['nullable', 'integer', 'min:-32768', 'max:32767'],
            'is_featured' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'digital_file' => ['nullable', 'file', 'max:102400'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['affiliate_url']) && ! in_array(parse_url($validated['affiliate_url'], PHP_URL_SCHEME), ['http', 'https'], true)) {
            abort(422, 'Affiliate links must use http or https.');
        }

        if ($validated['product_type'] === 'digital'
            && ! $request->hasFile('digital_file')
            && (! $product?->digital_file_path || ! Storage::disk('local')->exists($product->digital_file_path))) {
            throw ValidationException::withMessages([
                'digital_file' => 'Upload the file customers will receive after payment.',
            ]);
        }

        return $validated;
    }

    /** @param array<string, mixed> $validated */
    private function saveProduct(Request $request, Product $product, array $validated): void
    {
        $oldImage = $product->image_path;
        $oldFile = $product->digital_file_path;
        $slug = trim((string) ($validated['slug'] ?? '')) ?: Str::slug($validated['name']);
        $baseSlug = $slug ?: 'product';
        $suffix = 2;
        while (Product::query()->where('slug', $slug)->when($product->exists, fn ($query) => $query->where('id', '!=', $product->id))->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        $product->fill([
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'product_type' => $validated['product_type'],
            'short_description' => $validated['short_description'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
            'currency' => $validated['currency'],
            'affiliate_url' => $validated['product_type'] === 'affiliate' ? ($validated['affiliate_url'] ?? null) : null,
            'service_details' => $validated['product_type'] === 'service' ? ($validated['service_details'] ?? null) : null,
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'is_sample' => false,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'published_at' => $validated['status'] === 'published' ? ($product->published_at ?? now()) : null,
        ]);

        if ($request->boolean('remove_image') && $oldImage) {
            Storage::disk('public')->delete($oldImage);
            $product->image_path = null;
        }
        if ($request->hasFile('image')) {
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            $product->image_path = $request->file('image')->storePublicly('products', 'public');
        }
        if ($request->hasFile('digital_file')) {
            if ($oldFile && ! $this->fileHasOrders($oldFile)) {
                Storage::disk('local')->delete($oldFile);
            }
            $product->digital_file_path = $request->file('digital_file')->store('downloads', 'local');
        } elseif ($validated['product_type'] !== 'digital' && $oldFile) {
            if (! $this->fileHasOrders($oldFile)) {
                Storage::disk('local')->delete($oldFile);
            }
            $product->digital_file_path = null;
        }

        $product->save();
    }

    private function categories(): Collection
    {
        return Category::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    private function fileHasOrders(string $path): bool
    {
        return OrderItem::query()->where('download_path', $path)->exists();
    }
}
