@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
<!-- Dashboard Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
    <div>
        <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-tight">Dashboard</h1>
        <p class="text-gray-400 text-sm mt-1">Overview, prioritize, and accomplish your tasks with ease.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-[#115E3B] text-white text-sm font-medium hover:bg-[#0A4027] transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Add Product
        </a>
        <button onclick="document.getElementById('export-modal').classList.remove('hidden')" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full border border-gray-300 text-gray-700 bg-white text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm">
            Export Data
        </button>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Products (Green Card) -->
    <div class="bg-gradient-to-br from-[#115E3B] to-[#0D4B2F] rounded-[24px] p-6 text-white relative shadow-sm border border-[#0D4B2F]/20 flex flex-col justify-between overflow-hidden">
        <div class="flex justify-between items-start mb-6 z-10">
            <h3 class="text-white/90 font-medium">Total Products</h3>
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            </div>
        </div>
        <div class="z-10">
            <h2 class="text-4xl font-bold mb-3">{{ $stats['products'] }}</h2>
            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-[#1B744A] text-green-100">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                5+ Increased from last month
            </div>
        </div>
    </div>

    <!-- Total Recipes -->
    <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-6">
            <h3 class="text-gray-900 font-semibold">Total Recipes</h3>
            <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            </div>
        </div>
        <div>
            <h2 class="text-4xl font-bold text-gray-900 mb-3">{{ $stats['recipes'] }}</h2>
            <div class="inline-flex items-center text-[10px] font-medium text-gray-400">
                <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded mr-1">6+</span>
                Increased from last month
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-6">
            <h3 class="text-gray-900 font-semibold">Total Orders</h3>
            <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            </div>
        </div>
        <div>
            <h2 class="text-4xl font-bold text-gray-900 mb-3">{{ $stats['orders'] }}</h2>
            <div class="inline-flex items-center text-[10px] font-medium text-gray-400">
                <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded mr-1">12+</span>
                Increased from last month
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-6">
            <h3 class="text-gray-900 font-semibold">Total Users</h3>
            <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            </div>
        </div>
        <div>
            <h2 class="text-4xl font-bold text-gray-900 mb-3">{{ $stats['users'] }}</h2>
            <p class="text-[11px] font-medium text-gray-400">Active community members</p>
        </div>
    </div>
</div>

<!-- Main Layout Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- Left Column (Analytics & Collab) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Project Analytics -->
        <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900 tracking-tight">Sales Analytics</h3>
            </div>
            <div class="h-48 flex items-end justify-between gap-2 px-2">
                @foreach($salesAnalytics as $data)
                    @php
                        $percentage = $maxSales > 0 ? round(($data['total'] / $maxSales) * 100) : 0;
                        $height = max($percentage, 5); // Minimum 5% height
                    @endphp
                    <div class="w-full flex flex-col items-center gap-2">
                        <div class="w-full bg-[#115E3B] rounded-full relative group transition-all duration-300" style="height: {{ $height }}%">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Rp {{ number_format($data['total'], 0, ',', '.') }}
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 font-medium">{{ substr($data['day'], 0, 1) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
          

            <!-- Project Progress (Status Chart) -->
            <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm flex flex-col h-[280px]">
                <h3 class="text-lg font-bold text-gray-900 tracking-tight mb-4">Order Progress</h3>
                <div class="flex-1 flex flex-col items-center justify-center relative">
                    <!-- Semi-circle chart mock -->
                    <div class="relative w-40 h-20 overflow-hidden mb-2">
                        <div class="absolute top-0 left-0 w-40 h-40 rounded-full border-[16px] border-gray-100"></div>
                        <div class="absolute top-0 left-0 w-40 h-40 rounded-full border-[16px] border-[#115E3B] border-r-transparent border-b-transparent transform rotate-45"></div>
                        <div class="absolute top-0 left-0 w-40 h-40 rounded-full border-[16px] border-[#41B06E] border-l-transparent border-t-transparent border-b-transparent transform -rotate-45"></div>
                    </div>
                    <div class="absolute bottom-10 flex flex-col items-center">
                        <span class="text-3xl font-bold text-gray-900">{{ $orderProgress['completed'] }}%</span>
                        <span class="text-[10px] text-gray-400 font-medium">Orders Delivered</span>
                    </div>
                </div>
                <div class="flex justify-center gap-4 mt-2">
                    <div class="flex items-center gap-1.5" title="{{ $orderProgress['completed'] }}%"><div class="w-2.5 h-2.5 rounded-full bg-[#115E3B]"></div><span class="text-[10px] text-gray-500 font-medium">Completed</span></div>
                    <div class="flex items-center gap-1.5" title="{{ $orderProgress['in_progress'] }}%"><div class="w-2.5 h-2.5 rounded-full bg-[#41B06E]"></div><span class="text-[10px] text-gray-500 font-medium">In Progress</span></div>
                    <div class="flex items-center gap-1.5" title="{{ $orderProgress['pending'] }}%"><div class="w-2.5 h-2.5 rounded-full bg-gray-200"></div><span class="text-[10px] text-gray-500 font-medium">Pending</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Reminders / Quick Action -->
        <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 tracking-tight mb-4">Quick Actions</h3>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-1">Review new recipes</h4>
                <p class="text-xs text-gray-500 mb-4">Pending approval: 5 items</p>
                <button class="w-full bg-[#115E3B] hover:bg-[#0A4027] text-white text-sm font-semibold py-2.5 rounded-xl transition-colors flex justify-center items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Start Review
                </button>
            </div>
        </div>

        <!-- Recent Orders List -->
        <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm flex flex-col h-[380px]">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900 tracking-tight">Recent Orders</h3>
                <button class="text-xs font-semibold text-gray-500 border border-gray-200 px-3 py-1.5 rounded-full hover:bg-gray-50">
                    + New
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-2 space-y-4">
                @forelse($recentOrders as $order)
                    @php
                        $iconColors = [
                            'pending' => 'text-orange-500 bg-orange-100',
                            'processing' => 'text-blue-500 bg-blue-100',
                            'shipped' => 'text-indigo-500 bg-indigo-100',
                            'delivered' => 'text-green-500 bg-green-100',
                            'cancelled' => 'text-red-500 bg-red-100',
                        ];
                        $iconColor = $iconColors[$order->status] ?? 'text-gray-500 bg-gray-100';
                        
                        $icons = [
                            'pending' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                            'processing' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                            'shipped' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
                            'delivered' => 'M5 13l4 4L19 7',
                            'cancelled' => 'M6 18L18 6M6 6l12 12'
                        ];
                        $iconPath = $icons[$order->status] ?? 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4';
                    @endphp
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="flex items-start gap-3 group">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $iconColor }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 leading-tight group-hover:text-[#115E3B] transition-colors">
                                Order #{{ substr($order->id, -6) }}
                            </h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->user->name ?? 'Guest' }} • Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <p class="text-sm">No recent orders</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity Logs -->
         
        <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm flex flex-col h-[380px]">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900 tracking-tight">Recent Activity</h3>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-2 space-y-4">
                @forelse($activities as $activity)
                    @php
                        $iconColors = [
                            'create' => 'text-green-500 bg-green-50',
                            'update' => 'text-blue-500 bg-blue-50',
                            'delete' => 'text-red-500 bg-red-50',
                        ];
                        $iconColor = $iconColors[$activity->action] ?? 'text-gray-500 bg-gray-50';
                        
                        $icons = [
                            'create' => 'M12 4v16m8-8H4', // Plus
                            'update' => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z', // Edit
                            'delete' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' // Trash
                        ];
                        $iconPath = $icons[$activity->action] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                    @endphp
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $iconColor }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-900">
                                <span class="font-semibold">{{ $activity->user_name }}</span> 
                                {{ $activity->action }}d {{ $activity->model_type }} 
                                <span class="font-medium text-gray-700">{{ $activity->model_name }}</span>
                            </p>
                            @if($activity->details)
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $activity->details }}</p>
                            @endif
                            <p class="text-[10px] text-gray-400 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <p class="text-sm">No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="export-modal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative">
        <button onclick="document.getElementById('export-modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <h2 class="text-xl font-bold text-gray-900 mb-4">Export Orders (XLSX)</h2>
        <form action="{{ route('admin.export.orders') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-[#115E3B] focus:border-[#115E3B]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-[#115E3B] focus:border-[#115E3B]">
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="document.getElementById('export-modal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" onclick="setTimeout(() => document.getElementById('export-modal').classList.add('hidden'), 500)" class="flex-1 px-4 py-2 bg-[#115E3B] text-white rounded-xl font-semibold hover:bg-[#0A4027] transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download XLSX
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
