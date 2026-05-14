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
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.orders.*') ? 'bg-[#00473B]/10 text-[#00473B] font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.orders.*') ? 'text-[#00473B]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                Orders
                            </div>
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

        <!-- Download App Card -->
        <div class="p-4 mt-auto">
            <div class="bg-gradient-to-br from-[#00473B] to-[#0A2E26] rounded-2xl p-5 text-white relative overflow-hidden group cursor-pointer shadow-md">
                <!-- Abstract waves -->
                <div class="absolute inset-0 opacity-20">
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full">
                        <path d="M0,50 Q25,25 50,50 T100,50 v50 h-100 z" fill="#fff" />
                    </svg>
                </div>
                <div class="relative z-10">
                    <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <h4 class="font-semibold text-sm mb-1 leading-tight">Download our<br>Mobile App</h4>
                    <p class="text-[10px] text-gray-300 mb-3">Get easy in another way</p>
                    <button class="w-full bg-[#115E3B] hover:bg-[#0E4D30] text-white text-xs font-medium py-2 rounded-lg transition-colors shadow-inner border border-white/10">
                        Download
                    </button>
                </div>
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
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" class="block w-full pl-10 pr-12 py-2.5 border-0 rounded-full bg-white shadow-sm text-sm placeholder-gray-400 focus:ring-2 focus:ring-[#00473B] focus:border-transparent transition-shadow" placeholder="Search task, order, or product...">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <kbd class="hidden sm:inline-block border border-gray-200 rounded px-1.5 text-[10px] font-sans font-medium text-gray-400">⌘F</kbd>
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
                    <button class="relative w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-2 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
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
    </script>

    @stack('scripts')
</body>
</html>
