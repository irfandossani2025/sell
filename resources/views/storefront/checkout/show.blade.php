@extends('layouts.storefront', [
    'seoTitle' => 'Checkout | '.$product->name.' | Hot Products',
    'seoDescription' => 'Secure checkout for '.$product->name.'.',
    'canonical' => route('checkout.show', $product->slug),
])

@section('content')
<section class="checkout-wrap section-block">
    <div class="checkout-info"><a class="back-link" href="{{ route('products.show', $product->slug) }}">← Back to product</a><span class="eyebrow">A GOOD CHOICE</span><h1>Almost <em>yours.</em></h1><p>Tell us where to send your receipt and, for services, any details that help us get started.</p><div class="checkout-product"><span class="checkout-product-symbol">{{ $product->product_type === 'digital' ? '↓' : '↗' }}</span><div><b>{{ $product->name }}</b><small>{{ ucfirst($product->product_type) }} · {{ $product->currency }} {{ number_format((float) $product->price, 2) }}</small></div></div></div>
    <div class="checkout-panel">
        @if (! $checkoutReady)
            <span class="eyebrow">CHECKOUT SETUP IN PROGRESS</span><h2>We’re getting payments ready.</h2><p>PayPal checkout will appear here once the account is connected and the product currency is set.</p><a class="text-link" href="mailto:support@techittechnologies.com">Ask us about this item <span aria-hidden="true">↗</span></a>
        @else
            <form id="checkout-form" class="checkout-form">
                @csrf
                <label>Your name<input type="text" name="name" maxlength="160" required autocomplete="name"></label>
                <label>Email for your receipt<input type="email" name="email" maxlength="255" required autocomplete="email"></label>
                @if ($product->product_type === 'service')<label>Anything we should know? <span>(optional)</span><textarea name="service_notes" rows="4" maxlength="1000"></textarea></label>@endif
                <div id="paypal-button-container"></div><p class="payment-note">You’ll complete payment securely with PayPal. We never see your PayPal password.</p><div class="form-error" id="checkout-error" role="alert" hidden></div>
            </form>
            <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode($paypal->clientId()) }}&currency={{ urlencode($paypal->currency()) }}&intent=capture"></script>
            @push('scripts')
                <script>
                    const checkoutForm = document.querySelector('#checkout-form');
                    const checkoutError = document.querySelector('#checkout-error');
                    let checkoutReference = null;
                    const checkoutCsrf = document.querySelector('meta[name="csrf-token"]').content;
                    const checkoutPayload = () => Object.fromEntries(new FormData(checkoutForm).entries());
                    const checkoutPost = async (url, payload) => {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': checkoutCsrf },
                            body: JSON.stringify(payload),
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Checkout could not continue.');
                        return data;
                    };
                    paypal.Buttons({
                        createOrder: async () => {
                            checkoutError.hidden = true;
                            if (!checkoutForm.reportValidity()) throw new Error('Please complete your details first.');
                            const data = await checkoutPost(@js(route('checkout.paypal-order', $product->slug)), checkoutPayload());
                            checkoutReference = data.reference;
                            return data.id;
                        },
                        onApprove: async (data) => {
                            try {
                                const result = await checkoutPost(@js(route('checkout.paypal-capture')), { paypal_order_id: data.orderID, reference: checkoutReference });
                                window.location.assign(@js(route('checkout.complete', '__REFERENCE__')).replace('__REFERENCE__', result.reference));
                            } catch (error) {
                                checkoutError.textContent = error.message;
                                checkoutError.hidden = false;
                            }
                        },
                        onError: (error) => {
                            checkoutError.textContent = error.message || 'PayPal could not complete checkout. Please try again.';
                            checkoutError.hidden = false;
                        },
                    }).render('#paypal-button-container');
                </script>
            @endpush
        @endif
    </div>
</section>
@endsection
