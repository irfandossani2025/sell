@extends('layouts.admin', ['title' => 'Orders'])

@section('content')
<div class="admin-page-heading"><div><span class="eyebrow">DIRECT CUSTOMER ORDERS</span><h1>Orders</h1><p>Payments confirmed by PayPal show as paid here.</p></div></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Customer</th><th>Item / service note</th><th>Total</th><th>Status</th><th>Reference</th><th>Date</th></tr></thead><tbody>@forelse ($orders as $order)<tr><td><strong>{{ $order->customer_name }}</strong><small>{{ $order->customer_email }}</small></td><td>@foreach ($order->items as $item)<strong>{{ $item->product_name }}</strong>@endforeach @if ($order->service_notes)<small>{{ \Illuminate\Support\Str::limit($order->service_notes, 100) }}</small>@endif</td><td>{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td><td><span class="status-pill status-{{ $order->status }}">{{ $order->status }}</span></td><td><small>{{ $order->reference }}</small></td><td>{{ $order->created_at->format('M j, Y') }}</td></tr>@empty<tr><td colspan="6"><div class="admin-empty">There are no orders yet. Paid digital goods and services will appear here.</div></td></tr>@endforelse</tbody></table></div>
<div class="pagination-wrap">{{ $orders->links() }}</div>
@endsection
