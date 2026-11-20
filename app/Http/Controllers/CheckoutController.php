<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PayPalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CheckoutController extends Controller
{
    public function show(Product $product, PayPalService $paypal): View
    {
        abort_unless($product->status === 'published' && in_array($product->product_type, ['digital', 'service'], true), 404);
        $this->ensureDigitalFileExists($product);

        return view('storefront.checkout.show', [
            'product' => $product,
            'paypal' => $paypal,
            'checkoutReady' => $paypal->isConfigured()
                && $product->currency === $paypal->currency()
                && is_numeric($product->price)
                && (float) $product->price > 0,
        ]);
    }

    public function createPayPalOrder(Request $request, Product $product, PayPalService $paypal): JsonResponse
    {
        abort_unless($product->status === 'published' && in_array($product->product_type, ['digital', 'service'], true), 404);
        $this->ensureDigitalFileExists($product);
        abort_unless($paypal->isConfigured(), 503, 'PayPal checkout is not configured yet.');
        abort_unless($product->currency === $paypal->currency() && is_numeric($product->price) && (float) $product->price > 0, 422, 'This product is not configured for the current PayPal currency.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255'],
            'service_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = DB::transaction(function () use ($product, $validated): Order {
            $order = Order::query()->create([
                'reference' => (string) Str::uuid(),
                'customer_name' => $validated['name'],
                'customer_email' => $validated['email'],
                'status' => 'pending',
                'currency' => $product->currency,
                'total' => $product->price,
                'service_notes' => $validated['service_notes'] ?? null,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_type' => $product->product_type,
                'unit_price' => $product->price,
                'currency' => $product->currency,
                'quantity' => 1,
                'download_path' => $product->product_type === 'digital' ? $product->digital_file_path : null,
                'download_token' => $product->product_type === 'digital' ? (string) Str::uuid() : null,
                'download_expires_at' => null,
            ]);

            return $order;
        });

        try {
            $paypalOrder = $paypal->createOrder($order);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'PayPal could not start checkout. Please try again in a moment.'], 502);
        }

        $order->update(['paypal_order_id' => $paypalOrder['id'] ?? null]);

        return response()->json([
            'id' => $paypalOrder['id'] ?? null,
            'reference' => $order->reference,
        ]);
    }

    public function capturePayPalOrder(Request $request, PayPalService $paypal): JsonResponse
    {
        $validated = $request->validate([
            'paypal_order_id' => ['required', 'string', 'max:80'],
            'reference' => ['required', 'uuid'],
        ]);

        $order = Order::query()
            ->where('reference', $validated['reference'])
            ->where('paypal_order_id', $validated['paypal_order_id'])
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['reference' => $order->reference]);
        }

        try {
            $capture = $paypal->captureOrder($order);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'PayPal could not confirm this payment. Please try again.'], 502);
        }

        $captureDetails = data_get($capture, 'purchase_units.0.payments.captures.0');
        $capturedAmount = data_get($captureDetails, 'amount.value');
        $capturedCurrency = data_get($captureDetails, 'amount.currency_code');

        abort_unless(
            data_get($capture, 'status') === 'COMPLETED'
                && data_get($captureDetails, 'status') === 'COMPLETED'
                && $capturedCurrency === $order->currency
                && is_numeric($capturedAmount)
                && number_format((float) $capturedAmount, 2, '.', '') === number_format((float) $order->total, 2, '.', ''),
            422,
            'PayPal did not confirm the expected payment amount.',
        );

        DB::transaction(function () use ($order, $capture, $captureDetails): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status === 'paid') {
                return;
            }

            $lockedOrder->update([
                'status' => 'paid',
                'paypal_capture_id' => data_get($captureDetails, 'id'),
                'payer_email' => data_get($capture, 'payer.email_address'),
                'paid_at' => now(),
            ]);
            $lockedOrder->items()
                ->where('product_type', 'digital')
                ->update(['download_expires_at' => now()->addDays(14)]);
        });

        return response()->json(['reference' => $order->reference]);
    }

    public function complete(string $reference): View
    {
        $order = Order::query()->with('items')->where('reference', $reference)->firstOrFail();

        return view('storefront.checkout.complete', compact('order'));
    }

    public function download(string $token): StreamedResponse
    {
        $itemId = OrderItem::query()->where('download_token', $token)->value('id');
        abort_if($itemId === null, 404);

        $item = DB::transaction(function () use ($itemId): OrderItem {
            $item = OrderItem::query()->with('order')->whereKey($itemId)->lockForUpdate()->firstOrFail();
            abort_unless($item->order?->status === 'paid', 404);
            abort_unless($item->product_type === 'digital' && $item->download_path, 404);
            abort_if($item->download_expires_at?->isPast() ?? true, 410);
            abort_if($item->download_count >= $item->download_limit, 410);
            abort_unless(Storage::disk('local')->exists($item->download_path), 404);

            $item->increment('download_count');

            return $item;
        });

        return Storage::disk('local')->download($item->download_path, basename($item->download_path));
    }

    private function ensureDigitalFileExists(Product $product): void
    {
        if ($product->product_type === 'digital') {
            abort_unless($product->digital_file_path && Storage::disk('local')->exists($product->digital_file_path), 404);
        }
    }
}
