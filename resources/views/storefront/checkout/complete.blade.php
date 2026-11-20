@extends('layouts.storefront', [
    'seoTitle' => 'Order update | Hot Products',
    'seoDescription' => 'Your Hot Products order status.',
    'canonical' => route('checkout.complete', $order->reference),
])

@section('content')
<section class="completion-wrap section-block"><span class="completion-icon">{{ $order->status === 'paid' ? '✓' : '…' }}</span><span class="eyebrow">ORDER {{ $order->status === 'paid' ? 'CONFIRMED' : 'STATUS' }}</span><h1>{{ $order->status === 'paid' ? 'That’s a good find.' : 'Payment is not confirmed yet.' }}</h1><p>{{ $order->status === 'paid' ? 'Your payment is confirmed. Keep this order reference handy if you contact support: '.$order->reference.'.' : 'We have not received a completed payment for this order. If you paid, please contact support with reference '.$order->reference.'.' }}</p>
    @if ($order->status === 'paid')@foreach ($order->items as $item)@if ($item->product_type === 'digital' && $item->download_token)<a class="button button-dark" href="{{ route('downloads.show', $item->download_token) }}">Download {{ $item->product_name }} <span aria-hidden="true">↓</span></a><small class="download-note">Download link expires {{ $item->download_expires_at?->format('M j, Y') }} and allows {{ $item->download_limit }} downloads.</small>@elseif ($item->product_type === 'service')<p class="download-note">We’ll use your details to follow up about {{ $item->product_name }}.</p>@endif @endforeach @endif
    <a class="text-link completion-link" href="{{ route('products.index') }}">Back to the shortlist <span aria-hidden="true">↗</span></a>
</section>
@endsection
