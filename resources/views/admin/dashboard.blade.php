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
        <button class="inline-flex items-center justify-center px-5 py-2.5 rounded-full border border-gray-300 text-gray-700 bg-white text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm">
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
                <!-- Mock Bar Chart -->
                <div class="w-full flex flex-col items-center gap-2">
                    <div class="w-full bg-gray-100 rounded-full h-24 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOCIgaGVpZ2h0PSI4IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0wIDBsOCA4TTAgOGw4LThNMCA0bDggMCIgc3Ryb2tlPSIjZTVlN2ViIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiLz48L3N2Zz4=')] opacity-50"></div>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">S</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                    <div class="w-full bg-[#115E3B] rounded-full h-32"></div>
                    <span class="text-xs text-gray-400 font-medium">M</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                    <div class="w-full bg-[#41B06E] rounded-full h-40 relative flex justify-center">
                        <div class="absolute -top-6 bg-white border border-gray-100 shadow-sm text-[10px] font-bold px-2 py-0.5 rounded-full text-gray-700">74%</div>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">T</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                    <div class="w-full bg-[#0A4027] rounded-full h-48"></div>
                    <span class="text-xs text-gray-400 font-medium">W</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                    <div class="w-full bg-gray-100 rounded-full h-28 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOCIgaGVpZ2h0PSI4IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0wIDBsOCA4TTAgOGw4LThNMCA0bDggMCIgc3Ryb2tlPSIjZTVlN2ViIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiLz48L3N2Zz4=')] opacity-50"></div>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">T</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                    <div class="w-full bg-gray-100 rounded-full h-20 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOCIgaGVpZ2h0PSI4IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0wIDBsOCA4TTAgOGw4LThNMCA0bDggMCIgc3Ryb2tlPSIjZTVlN2ViIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiLz48L3N2Zz4=')] opacity-50"></div>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">F</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                    <div class="w-full bg-gray-100 rounded-full h-24 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOCIgaGVpZ2h0PSI4IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0wIDBsOCA4TTAgOGw4LThNMCA0bDggMCIgc3Ryb2tlPSIjZTVlN2ViIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiLz48L3N2Zz4=')] opacity-50"></div>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">S</span>
                </div>
            </div>
        </div>

        <!-- 2 Column sub-grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Team Collaboration (Latest Users) -->
            <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-sm flex flex-col h-[280px]">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900 tracking-tight">New Users</h3>
                    <button class="text-xs font-semibold text-gray-500 border border-gray-200 px-3 py-1.5 rounded-full hover:bg-gray-50">
                        + Add Member
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto space-y-4 pr-2">
                    <!-- User 1 -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-xl">👨🏼‍🦱</div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 leading-tight">Alexandra Deff</h4>
                                <p class="text-[10px] text-gray-400">Registered recently</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-green-50 text-green-600 text-[10px] font-bold rounded-md border border-green-100">Completed</span>
                    </div>
                    <!-- User 2 -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-xl">🧔🏻‍♂️</div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 leading-tight">Edwin Adenike</h4>
                                <p class="text-[10px] text-gray-400">Registered today</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-yellow-50 text-yellow-600 text-[10px] font-bold rounded-md border border-yellow-100">In Progress</span>
                    </div>
                    <!-- User 3 -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-xl">👱🏼‍♂️</div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 leading-tight">Isaac Oluwatemilorun</h4>
                                <p class="text-[10px] text-gray-400">Profile incomplete</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded-md border border-red-100">Pending</span>
                    </div>
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
                        <span class="text-3xl font-bold text-gray-900">41%</span>
                        <span class="text-[10px] text-gray-400 font-medium">Orders Delivered</span>
                    </div>
                </div>
                <div class="flex justify-center gap-4 mt-2">
                    <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-[#115E3B]"></div><span class="text-[10px] text-gray-500 font-medium">Completed</span></div>
                    <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-[#41B06E]"></div><span class="text-[10px] text-gray-500 font-medium">In Progress</span></div>
                    <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-gray-200"></div><span class="text-[10px] text-gray-500 font-medium">Pending</span></div>
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

        <!-- Time Tracker (Dark Card) -->
        <div class="bg-gradient-to-br from-[#0B2519] to-[#04110A] rounded-[24px] p-6 text-white relative shadow-md overflow-hidden">
            <!-- Wavy background mock -->
            <div class="absolute bottom-0 left-0 w-full h-1/2 opacity-30">
                 <svg viewBox="0 0 100 50" preserveAspectRatio="none" class="w-full h-full text-[#41B06E]">
                    <path d="M0,50 C20,20 40,60 60,30 C80,0 100,40 100,50 Z" fill="currentColor" />
                 </svg>
            </div>
            
            <div class="relative z-10 flex flex-col items-center justify-center py-2">
                <p class="text-sm text-gray-300 font-medium mb-1 w-full text-left">Time Tracker</p>
                <div class="text-4xl font-light tracking-wider my-4">
                    01:24:08
                </div>
                <div class="flex gap-4">
                    <button class="w-10 h-10 rounded-full bg-white text-[#0B2519] flex items-center justify-center hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                    </button>
                    <button class="w-10 h-10 rounded-full bg-red-500 text-white flex items-center justify-center hover:scale-105 transition-transform shadow-[0_0_15px_rgba(239,68,68,0.5)]">
                        <div class="w-3.5 h-3.5 bg-white rounded-sm"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
