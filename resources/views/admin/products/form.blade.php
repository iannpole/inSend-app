@extends('admin.layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.products.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isset($product) ? 'Edit Product' : 'Add Product' }}</h1>
    </div>
</div>

@php
    $di = $product->discount_info ?? null;
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl">
    <form action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-8">
        @csrf
        @if(isset($product))
            @method('PUT')
        @endif

        {{-- ─── Basic Info ─────────────────────────────────── --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Product Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="category_slug" class="block text-sm font-medium text-gray-700">Category</label>
                    <input type="text" name="category_slug" id="category_slug" value="{{ old('category_slug', $product->category_slug ?? '') }}" required placeholder="e.g. sayuran-segar"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
                    @error('category_slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="base_price" class="block text-sm font-medium text-gray-700">Base Price (Rp)</label>
                    <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $product->base_price ?? '') }}" min="0" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
                    @error('base_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" min="0" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
                        @error('stock_quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label for="unit" class="block text-sm font-medium text-gray-700">Unit</label>
                        <input type="text" name="unit" id="unit" value="{{ old('unit', $product->unit ?? '') }}" placeholder="pcs, kg, ikat" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
                        @error('unit') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Description ────────────────────────────────── --}}
        <div class="space-y-1">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="4"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        {{-- ─── Discount Settings ──────────────────────────── --}}
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800">Discount Settings</h2>
                        <p class="text-xs text-gray-500">Configure promotional pricing</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="discount_active" id="discount_active" value="1"
                        {{ old('discount_active', $di['is_active'] ?? false) ? 'checked' : '' }}
                        class="sr-only peer"
                        onchange="toggleDiscountPanel()">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                </label>
            </div>

            <div id="discount-panel" class="px-6 py-5 space-y-5 transition-all duration-300" style="{{ old('discount_active', $di['is_active'] ?? false) ? '' : 'display:none;' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1">
                        <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Discount Percentage (%)</label>
                        <input type="number" name="discount_percentage" id="discount_percentage"
                            value="{{ old('discount_percentage', $di['percentage'] ?? 0) }}"
                            min="0" max="100" step="1"
                            oninput="calcFromPercentage()"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-orange-400 focus:ring focus:ring-orange-200 focus:ring-opacity-50 outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1">
                        <label for="discount_fixed" class="block text-sm font-medium text-gray-700">Discount Amount (Rp)</label>
                        <input type="number" name="discount_fixed" id="discount_fixed"
                            value="{{ old('discount_fixed', $di['fixed_amount'] ?? 0) }}"
                            min="0"
                            oninput="calcFromFixed()"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-orange-400 focus:ring focus:ring-orange-200 focus:ring-opacity-50 outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1">
                        <label for="discount_start" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="discount_start" id="discount_start"
                            value="{{ old('discount_start', isset($di['start_date']) ? \Carbon\Carbon::parse($di['start_date'])->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-orange-400 focus:ring focus:ring-orange-200 focus:ring-opacity-50 outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1">
                        <label for="discount_end" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="discount_end" id="discount_end"
                            value="{{ old('discount_end', isset($di['end_date']) ? \Carbon\Carbon::parse($di['end_date'])->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-orange-400 focus:ring focus:ring-orange-200 focus:ring-opacity-50 outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="campaign_name" class="block text-sm font-medium text-gray-700">Campaign Name</label>
                    <input type="text" name="campaign_name" id="campaign_name"
                        value="{{ old('campaign_name', $di['campaign_name'] ?? '') }}"
                        placeholder="e.g. Promo Sayur Sehat"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-orange-400 focus:ring focus:ring-orange-200 focus:ring-opacity-50 outline-none transition-all text-sm">
                </div>

                {{-- Live Price Preview --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Price Preview</p>
                    <div class="flex items-baseline gap-3">
                        <span id="preview-sale-price" class="text-xl font-bold text-[#00473B]">Rp 0</span>
                        <span id="preview-base-price" class="text-sm text-gray-400 line-through">Rp 0</span>
                        <span id="preview-badge" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700 hidden">-0%</span>
                    </div>
                    <p id="preview-savings" class="text-xs text-green-600 mt-1 hidden">Hemat Rp 0</p>
                </div>
            </div>
        </div>

        {{-- ─── Images ─────────────────────────────────────── --}}
        <div class="space-y-1">
            <label for="images" class="block text-sm font-medium text-gray-700">Images</label>
            <input type="file" name="images[]" id="images" multiple accept="image/*"
                class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-white file:text-gray-700 file:border file:border-gray-200 hover:file:bg-gray-50">
            @if(isset($product) && is_array($product->images) && count($product->images) > 0)
                <div class="flex gap-3 mt-4 flex-wrap" id="existing-images-container">
                    @foreach($product->images as $img)
                        <div class="relative group" id="img-container-{{ md5($img) }}">
                            <img src="{{ Str::startsWith($img, 'http') ? $img : asset('storage/' . $img) }}" class="h-24 w-24 object-cover rounded-xl border border-gray-200" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Image&color=00473B&background=E6F2ED';">
                            <button type="button" onclick="removeImage('{{ $img }}', 'img-container-{{ md5($img) }}')" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-md opacity-0 group-hover:opacity-100 transition-all cursor-pointer">
                                ✕
                            </button>
                        </div>
                    @endforeach
                </div>
                <div id="deleted-images-inputs"></div>
            @endif
        </div>

        {{-- ─── Active Toggle ──────────────────────────────── --}}
        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                class="h-4 w-4 text-[#00473B] focus:ring-[#00473B] border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active Product (Visible to customers)
            </label>
        </div>

        {{-- ─── Submit ─────────────────────────────────────── --}}
        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-[#00473B] text-white font-medium rounded-lg hover:bg-[#00382e] transition-colors shadow-sm">
                {{ isset($product) ? 'Update Product' : 'Save Product' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function toggleDiscountPanel() {
        const panel = document.getElementById('discount-panel');
        const cb = document.getElementById('discount_active');
        panel.style.display = cb.checked ? '' : 'none';
        if (cb.checked) updatePreview();
    }

    function removeImage(imagePath, containerId) {
        // Remove the image element from the view
        document.getElementById(containerId).remove();
        
        // Add a hidden input to submit the deleted image path
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'deleted_images[]';
        input.value = imagePath;
        document.getElementById('deleted-images-inputs').appendChild(input);
    }

    function calcFromPercentage() {
        const base = parseFloat(document.getElementById('base_price').value) || 0;
        const pct = parseFloat(document.getElementById('discount_percentage').value) || 0;
        const fixed = Math.round(base * (pct / 100));
        document.getElementById('discount_fixed').value = fixed;
        updatePreview();
    }

    function calcFromFixed() {
        const base = parseFloat(document.getElementById('base_price').value) || 0;
        const fixed = parseFloat(document.getElementById('discount_fixed').value) || 0;
        const pct = base > 0 ? Math.round((fixed / base) * 100) : 0;
        document.getElementById('discount_percentage').value = pct;
        updatePreview();
    }

    function updatePreview() {
        const base = parseFloat(document.getElementById('base_price').value) || 0;
        const pct = parseFloat(document.getElementById('discount_percentage').value) || 0;
        const fixed = parseFloat(document.getElementById('discount_fixed').value) || 0;
        const sale = Math.max(0, base - fixed);

        document.getElementById('preview-base-price').textContent = 'Rp ' + base.toLocaleString('id-ID');
        document.getElementById('preview-sale-price').textContent = 'Rp ' + sale.toLocaleString('id-ID');

        const badge = document.getElementById('preview-badge');
        const savings = document.getElementById('preview-savings');

        if (pct > 0) {
            badge.textContent = '-' + pct + '%';
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }

        if (fixed > 0) {
            savings.textContent = 'Hemat Rp ' + fixed.toLocaleString('id-ID');
            savings.classList.remove('hidden');
        } else {
            savings.classList.add('hidden');
        }
    }

    // Listen for base_price changes to recalculate
    document.getElementById('base_price').addEventListener('input', function() {
        if (document.getElementById('discount_active').checked) {
            calcFromPercentage();
        }
    });

    // Init preview on load if discount is active
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('discount_active').checked) {
            updatePreview();
        }
    });
</script>
@endpush
@endsection
