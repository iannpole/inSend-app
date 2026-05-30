@extends('admin.layouts.app')
@section('title', 'Promotions')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Promotions & Vouchers</h1>
        <p class="text-gray-500 text-sm mt-1">Manage your discount vouchers and promo banners.</p>
    </div>
    <a href="{{ route('admin.promotions.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-[#00473B] text-white rounded-lg hover:bg-[#00382e] transition-colors font-medium text-sm shadow-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Promotion
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <form action="{{ route('admin.promotions.index') }}" method="GET" class="w-full max-w-md relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or code..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 font-medium">Banner/Code</th>
                    <th class="px-6 py-4 font-medium">Type</th>
                    <th class="px-6 py-4 font-medium">Value</th>
                    <th class="px-6 py-4 font-medium">Validity</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($promotions as $promo)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($promo->image_url)
                                <img src="{{ asset('storage/' . $promo->image_url) }}" alt="{{ $promo->name }}" class="w-10 h-10 rounded-lg object-cover mr-3 border border-gray-200">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center mr-3 border border-gray-200 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div>
                                <span class="text-sm font-bold text-gray-900 block">{{ $promo->code }}</span>
                                <span class="text-xs text-gray-500">{{ $promo->name }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 capitalize">
                        {{ str_replace('_', ' ', $promo->type) }}
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                        {{ $promo->display_value }}
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">
                        @if($promo->start_date || $promo->end_date)
                            {{ $promo->start_date ? $promo->start_date->format('d M Y') : 'Mulai sekarang' }} <br> 
                            - {{ $promo->end_date ? $promo->end_date->format('d M Y') : 'Tanpa batas' }}
                        @else
                            Selamanya
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($promo->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.promotions.edit', $promo->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </a>
                        <form action="{{ route('admin.promotions.destroy', $promo->id) }}" method="POST" class="inline-block" onsubmit="confirmDelete(event, this, 'promotion');">
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
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">No promotions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $promotions->links('pagination::tailwind') }}
    </div>
</div>
@endsection
