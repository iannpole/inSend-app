@extends('admin.layouts.app')
@section('title', 'Order Details')

@section('content')
<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.orders.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Order #{{ substr($order->id, -8) }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('d F Y, H:i') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-6">
        <!-- Order Items -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
            </div>
            <div class="p-6">
                <ul class="space-y-4">
                    @foreach($order->items as $item)
                        @php
                            $itemName = $item['product_name_snapshot'] ?? $item['name'] ?? 'Unknown Product';
                            $itemQty = $item['quantity'] ?? $item['qty'] ?? 0;
                            $itemPrice = $item['price_per_unit'] ?? $item['price'] ?? 0;
                            $itemTotal = $item['total_price'] ?? $item['subtotal'] ?? ($itemQty * $itemPrice);
                        @endphp
                        <li class="flex justify-between items-start pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                            <div>
                               <p class="font-medium text-gray-900">{{ $itemName }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ $itemQty }} x Rp {{ number_format($itemPrice, 0, ',', '.') }}
                                </p>
                            </div>
                            <span class="font-medium text-gray-900">Rp {{ number_format($itemTotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                        <span>Total Price</span>
                        <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Status Update -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Order Status</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-white">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Admin Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">{{ $order->notes }}</textarea>
                    </div>
                    <button type="submit" class="w-full bg-[#00473B] text-white font-medium py-2.5 rounded-lg hover:bg-[#00382e] transition-colors shadow-sm">
                        Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Customer Info</h3>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <p class="text-gray-500 font-medium">Name</p>
                    <p class="text-gray-900 mt-1">{{ $order->user->name ?? 'Guest' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Email</p>
                    <p class="text-gray-900 mt-1">{{ $order->user->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Phone</p>
                    <p class="text-gray-900 mt-1">{{ $order->user->phone ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Shipping Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Shipping Info</h3>
            </div>
            <div class="p-6 text-sm text-gray-900 leading-relaxed">
                @if(is_array($order->shipping_address))
                    <p>{{ $order->shipping_address['street'] ?? '-' }}</p>
                    <p>{{ $order->shipping_address['city'] ?? '-' }}, {{ $order->shipping_address['province'] ?? '-' }}</p>
                    <p>{{ $order->shipping_address['postal_code'] ?? '-' }}</p>
                @else
                    <p>-</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
