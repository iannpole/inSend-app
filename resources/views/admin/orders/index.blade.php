@extends('admin.layouts.app')
@section('title', 'Orders')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
        <p class="text-gray-500 text-sm mt-1">Manage customer orders and their statuses.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="w-full max-w-md relative flex gap-2">
            <select name="status" class="px-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-white" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 font-medium">Order ID</th>
                    <th class="px-6 py-4 font-medium">Customer</th>
                    <th class="px-6 py-4 font-medium">Total Price</th>
                    <th class="px-6 py-4 font-medium">Payment</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium">Date</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-[#00473B] hover:underline">
                            #{{ substr($order->id, -8) }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $order->user->name ?? 'Guest' }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 uppercase">{{ $order->payment_method ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'shipped' => 'bg-indigo-100 text-indigo-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            $color = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }} capitalize">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition-colors">
                            View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-sm">No orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $orders->links('pagination::tailwind') }}
    </div>
</div>
@endsection
