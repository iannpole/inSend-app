<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - inSend</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 4px; }
        .sidebar-scroll:hover::-webkit-scrollbar-thumb { background: #D1D5DB; }
    </style>
</head>
<body class="bg-[#F4F6F8] text-gray-800 antialiased h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-[260px] bg-white border-r border-gray-100 flex flex-col hidden md:flex m-4 rounded-3xl shadow-sm z-10 h-[calc(100vh-2rem)]">
        <div class="h-20 flex items-center px-8">
            <div class="flex items-center gap-3">
                <span class="text-xl font-bold text-gray-900">InSend</span>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto sidebar-scroll px-4 py-2 flex flex-col gap-6">
            <div>
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Menu</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.products.*') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.products.*') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.recipes.index') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.recipes.*') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.recipes.*') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Recipes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.blog.index') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.blog.*') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.blog.*') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                            Blog
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.orders.*') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.orders.*') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                Orders
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.promotions.index') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.promotions.*') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.promotions.*') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                            Promotions
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reviews.index') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.reviews.*') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.reviews.*') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                            Reviews
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.users.*') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Users
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">General</p>
                <ul class="space-y-1">
                    <li>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-2.5 rounded-xl transition-all text-gray-500 hover:bg-gray-50 hover:text-gray-900">
                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden h-screen">
        <!-- Topbar -->
        <header class="h-24 flex items-center justify-between px-8 pt-4">
            <div class="flex items-center md:hidden">
                <span class="text-xl font-bold text-[#00473B]">inSend</span>
            </div>
            
            <!-- Search -->
            <div class="hidden md:flex items-center flex-1 max-w-md">
                <div class="relative w-full" id="global-search-container">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="global-search-input" class="block w-full pl-10 pr-12 py-2.5 border-0 rounded-full bg-white shadow-sm text-sm placeholder-gray-400 focus:ring-2 focus:ring-[#00473B] focus:border-transparent transition-shadow" placeholder="Search task, order, or product..." autocomplete="off">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <kbd class="hidden sm:inline-block border border-gray-200 rounded px-1.5 text-[10px] font-sans font-medium text-gray-400">⌘F</kbd>
                    </div>
                    
                    <!-- Search Results Dropdown -->
                    <div id="search-results" class="absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-100 hidden z-50 max-h-96 overflow-y-auto">
                        <!-- Results will be injected here -->
                    </div>
                </div>
            </div>

            <div class="flex-1 md:hidden"></div>

            <!-- Right Actions -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <button class="w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </button>
                    <div class="relative" id="notification-dropdown-container">
                        <button id="notification-btn" class="relative w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @if(isset($globalActivities) && $globalActivities->count() > 0)
                            <span class="absolute top-2 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                            @endif
                        </button>

                        <!-- Notification Dropdown -->
                        <div id="notification-dropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 hidden z-50 overflow-hidden">
                            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="font-bold text-gray-900 text-sm">Recent Activity</h3>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @if(isset($globalActivities) && $globalActivities->count() > 0)
                                    @foreach($globalActivities as $activity)
                                        @php
                                            $iconColors = [
                                                'create' => 'text-green-500 bg-green-50',
                                                'update' => 'text-blue-500 bg-blue-50',
                                                'delete' => 'text-red-500 bg-red-50',
                                            ];
                                            $iconColor = $iconColors[$activity->action] ?? 'text-gray-500 bg-gray-50';
                                            $icons = [
                                                'create' => 'M12 4v16m8-8H4',
                                                'update' => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
                                                'delete' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'
                                            ];
                                            $iconPath = $icons[$activity->action] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                                        @endphp
                                        <div class="p-3 border-b border-gray-50 hover:bg-gray-50 transition-colors flex gap-3">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $iconColor }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-900 leading-snug">
                                                    <span class="font-semibold">{{ $activity->user_name }}</span> {{ $activity->action }}d {{ $activity->model_type }} <span class="font-medium">{{ $activity->model_name }}</span>
                                                </p>
                                                <p class="text-[10px] text-gray-400 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="p-6 text-center text-gray-400 text-sm">
                                        No recent activity
                                    </div>
                                @endif
                            </div>
                            <div class="p-2 border-t border-gray-100 bg-gray-50 text-center">
                                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#115E3B] hover:underline">View all in Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-8 w-px bg-gray-200 mx-1"></div>

                <div class="flex items-center gap-3 bg-white pl-1.5 pr-4 py-1.5 rounded-full border border-gray-100 shadow-sm cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-orange-100 overflow-hidden">
                        <!-- Placeholder Avatar -->
                        <svg class="w-full h-full text-orange-500 mt-1" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold text-gray-900 leading-none mb-0.5">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-gray-500 leading-none">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto px-8 pb-8 pt-4">
            <!-- Success Toast will be handled by SweetAlert2 -->

            @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl shadow-sm">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-semibold">There were some errors with your submission</span>
                    </div>
                    <ul class="list-disc pl-7 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global delete confirmation
        function confirmDelete(event, form, itemName = 'item') {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete this ${itemName}. This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-[24px] shadow-sm border border-gray-100 p-6',
                    title: 'text-xl font-bold text-gray-900 mb-2',
                    htmlContainer: 'text-sm text-gray-500 m-0',
                    actions: 'mt-6 gap-3',
                    confirmButton: 'bg-red-500 hover:bg-red-600 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors',
                    cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-5 py-2.5 rounded-xl transition-colors'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Global success toast
        @if(session('success'))
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl shadow-md border border-gray-100 mt-4 mr-4',
                    title: 'text-sm font-semibold text-gray-900'
                }
            });

            Toast.fire({
                icon: 'success',
                iconColor: '#115E3B',
                title: "{{ session('success') }}"
            });
        @endif

        // Global Search Logic
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('global-search-input');
            const searchResults = document.getElementById('search-results');
            let debounceTimer;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const query = this.value.trim();
                    
                    if (query.length < 2) {
                        searchResults.classList.add('hidden');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`/admin/search?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                searchResults.innerHTML = '';
                                if (data.length === 0) {
                                    searchResults.innerHTML = '<div class="p-4 text-sm text-gray-500 text-center">No results found</div>';
                                } else {
                                    data.forEach(item => {
                                        searchResults.innerHTML += `
                                            <a href="${item.url}" class="flex items-center gap-3 p-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors group">
                                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-lg">${item.icon}</div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-[#115E3B] transition-colors">${item.title}</h4>
                                                    <p class="text-[10px] text-gray-400">${item.type}</p>
                                                </div>
                                            </a>
                                        `;
                                    });
                                }
                                searchResults.classList.remove('hidden');
                            });
                    }, 300);
                });

                // Close on click outside
                document.addEventListener('click', function(e) {
                    if (!document.getElementById('global-search-container').contains(e.target)) {
                        searchResults.classList.add('hidden');
                    }
                });
            }

            // Notification Dropdown Toggle
            const notifBtn = document.getElementById('notification-btn');
            const notifDropdown = document.getElementById('notification-dropdown');
            const notifContainer = document.getElementById('notification-dropdown-container');

            if (notifBtn && notifDropdown) {
                notifBtn.addEventListener('click', function() {
                    notifDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', function(e) {
                    if (!notifContainer.contains(e.target)) {
                        notifDropdown.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
