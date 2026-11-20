<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    public function isConfigured(): bool
    {
        return filled(config('services.paypal.client_id'))
            && filled(config('services.paypal.client_secret'));
    }

    public function clientId(): ?string
    {
        return config('services.paypal.client_id');
    }

    public function currency(): string
    {
        return (string) config('services.paypal.currency', 'USD');
    }

    /** @return array<string, mixed> */
    public function createOrder(Order $order): array
    {
        $item = $order->items()->with('product')->firstOrFail();
        $cancelUrl = $item->product
            ? route('checkout.show', $item->product->slug)
            : route('products.index');

        $response = $this->api()->withHeaders([
            'PayPal-Request-Id' => 'create-'.$order->reference,
        ])->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->reference,
                'custom_id' => $order->reference,
                'amount' => [
                    'currency_code' => $order->currency,
                    'value' => number_format((float) $order->total, 2, '.', ''),
                ],
            ]],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'brand_name' => (string) config('app.name'),
                        'landing_page' => 'BILLING',
                        'user_action' => 'PAY_NOW',
                        'shipping_preference' => 'NO_SHIPPING',
                        'return_url' => route('checkout.complete', $order->reference),
                        'cancel_url' => $cancelUrl,
                    ],
                ],
            ],
        ])->throw();

        return $response->json();
    }

    /** @return array<string, mixed> */
    public function captureOrder(Order $order): array
    {
        return $this->api()->withHeaders([
            'PayPal-Request-Id' => 'capture-'.$order->reference,
        ])->post('/v2/checkout/orders/'.urlencode((string) $order->paypal_order_id).'/capture')->throw()->json();
    }

    private function api(): PendingRequest
    {
        $baseUrl = config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $token = Http::asForm()
            ->withBasicAuth(
                (string) config('services.paypal.client_id'),
                (string) config('services.paypal.client_secret'),
            )
            ->acceptJson()
            ->timeout(15)
            ->post($baseUrl.'/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw()
            ->json('access_token');

        return Http::baseUrl($baseUrl)
            ->withToken((string) $token)
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }
}
