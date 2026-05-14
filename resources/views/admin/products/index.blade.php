@extends('admin.layouts.app')
@section('title', 'Products')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Products</h1>
        <p class="text-gray-500 text-sm mt-1">Manage your store products.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-[#00473B] text-white rounded-lg hover:bg-[#00382e] transition-colors font-medium text-sm shadow-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Product
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <form action="{{ route('admin.products.index') }}" method="GET" class="w-full max-w-md relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 font-medium">Name</th>
                    <th class="px-6 py-4 font-medium">Category</th>
                    <th class="px-6 py-4 font-medium">Price</th>
                    <th class="px-6 py-4 font-medium">Stock</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition-colors">
                    {{-- Name + Image --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if(is_array($product->images) && count($product->images) > 0)
                                <img src="{{ Str::startsWith($product->images[0], 'http') ? $product->images[0] : asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover mr-3 border border-gray-200">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center mr-3 border border-gray-200 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <span class="text-sm font-medium text-gray-900">{{ $product->name }}</span>
                        </div>
                    </td>

                    {{-- Category --}}
                    <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ str_replace(['-', '_'], ' ', $product->category_slug) }}</td>

                    {{-- Price with Discount --}}
                    <td class="px-6 py-4">
                        @if($product->is_discounted)
                            {{-- Discounted Price Display --}}
                            <div class="flex flex-col gap-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 tracking-wide">
                                        -{{ $product->discount_percentage }}%
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ $product->formatted_sale_price }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-400 line-through">
                                    {{ $product->formatted_base_price }}
                                </span>
                                @if($product->campaign_name)
                                    <span class="text-[10px] text-orange-600 font-medium truncate max-w-[140px]" title="{{ $product->campaign_name }}">
                                        🏷️ {{ $product->campaign_name }}
                                    </span>
                                @endif
                            </div>
                        @else
                            {{-- Normal Price --}}
                            <span class="text-sm font-medium text-gray-900">
                                {{ $product->formatted_base_price }}
                            </span>
                        @endif
                        <span class="text-xs text-gray-500 font-normal"> / {{ $product->unit }}</span>
                    </td>

                    {{-- Stock --}}
                    <td class="px-6 py-4">
                        @if($product->stock_quantity <= 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out of Stock</span>
                        @elseif($product->stock_quantity <= 10)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ $product->stock_quantity }} left</span>
                        @else
                            <span class="text-sm text-gray-600">{{ $product->stock_quantity }}</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-6 py-4">
                        @if($product->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </a>
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="confirmDelete(event, this, 'product');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $products->links('pagination::tailwind') }}
    </div>
</div>
@endsection
