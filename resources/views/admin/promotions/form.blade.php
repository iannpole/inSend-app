@extends('admin.layouts.app')
@section('title', isset($promotion) ? 'Edit Promotion' : 'Add Promotion')

@section('content')
<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.promotions.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isset($promotion) ? 'Edit Promotion' : 'Add Promotion' }}</h1>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl">
    <form action="{{ isset($promotion) ? route('admin.promotions.update', $promotion->id) : route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
        @csrf
        @if(isset($promotion))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
                <label for="code" class="block text-sm font-medium text-gray-700">Voucher Code (e.g. WELCOME50)</label>
                <input type="text" name="code" id="code" value="{{ old('code', $promotion->code ?? '') }}" required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm uppercase">
            </div>

            <div class="space-y-1">
                <label for="name" class="block text-sm font-medium text-gray-700">Promotion Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $promotion->name ?? '') }}" required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            </div>

            <div class="space-y-1">
                <label for="type" class="block text-sm font-medium text-gray-700">Discount Type</label>
                <select name="type" id="type" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-white">
                    <option value="percentage" {{ old('type', $promotion->type ?? '') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ old('type', $promotion->type ?? '') == 'fixed' ? 'selected' : '' }}>Fixed Amount (Rp)</option>
                    <option value="free_shipping" {{ old('type', $promotion->type ?? '') == 'free_shipping' ? 'selected' : '' }}>Free Shipping</option>
                </select>
            </div>
            
            <div class="space-y-1">
                <label for="value" class="block text-sm font-medium text-gray-700">Value (e.g. 50 for 50%)</label>
                <input type="number" name="value" id="value" value="{{ old('value', $promotion->value ?? 0) }}" min="0" required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            </div>

            <div class="space-y-1">
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date (Optional)</label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', isset($promotion->start_date) ? $promotion->start_date->format('Y-m-d') : '') }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            </div>
            
            <div class="space-y-1">
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date (Optional)</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', isset($promotion->end_date) ? $promotion->end_date->format('Y-m-d') : '') }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            </div>
            
            <div class="space-y-1">
                <label for="min_order" class="block text-sm font-medium text-gray-700">Minimum Order (Optional)</label>
                <input type="number" name="min_order" id="min_order" value="{{ old('min_order', $promotion->min_order ?? 0) }}" min="0"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            </div>
            
            <div class="space-y-1">
                <label for="usage_limit" class="block text-sm font-medium text-gray-700">Usage Limit (Optional)</label>
                <input type="number" name="usage_limit" id="usage_limit" value="{{ old('usage_limit', $promotion->usage_limit ?? '') }}" min="1"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            </div>
        </div>

        <div class="space-y-1">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="2"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">{{ old('description', $promotion->description ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
                <label for="bg_color_start" class="block text-sm font-medium text-gray-700">Banner Background Start Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="bg_color_start" id="bg_color_start" value="{{ old('bg_color_start', $promotion->bg_color_start ?? '#FF8904') }}"
                        class="h-10 w-14 p-1 rounded border border-gray-200 cursor-pointer">
                    <span class="text-sm text-gray-500">Left color</span>
                </div>
            </div>
            
            <div class="space-y-1">
                <label for="bg_color_end" class="block text-sm font-medium text-gray-700">Banner Background End Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="bg_color_end" id="bg_color_end" value="{{ old('bg_color_end', $promotion->bg_color_end ?? '#FFB156') }}"
                        class="h-10 w-14 p-1 rounded border border-gray-200 cursor-pointer">
                    <span class="text-sm text-gray-500">Right color</span>
                </div>
            </div>
        </div>

        <div class="space-y-1">
            <label for="image" class="block text-sm font-medium text-gray-700">Banner Image</label>
            <input type="file" name="image" id="image" accept="image/*"
                class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-white file:text-gray-700 file:border file:border-gray-200 hover:file:bg-gray-50">
            @if(isset($promotion) && $promotion->image_url)
                <div class="mt-4">
                    <img src="{{ asset('storage/' . $promotion->image_url) }}" class="h-32 object-cover rounded-lg border border-gray-200 shadow-sm">
                </div>
            @endif
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $promotion->is_active ?? true) ? 'checked' : '' }}
                class="h-4 w-4 text-[#00473B] focus:ring-[#00473B] border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active (Can be used by customers)
            </label>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-[#00473B] text-white font-medium rounded-lg hover:bg-[#00382e] transition-colors shadow-sm">
                {{ isset($promotion) ? 'Update Promotion' : 'Save Promotion' }}
            </button>
        </div>
    </form>
</div>
@endsection
