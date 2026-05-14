<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - inSend</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased h-screen flex overflow-hidden">

    <!-- Left Side: Image (Visible on lg screens) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-[#00473B]">
        <!-- Abstract gradient / pattern instead of external image for reliability, or use a reliable unsplash -->
        <img src="/storage/assets/fotopetanilogin.jpg" alt="Workspace" class="absolute inset-0 w-full h-full object-cover opacity-60 mix-blend-overlay">
        
        <!-- Logo -->
        <div class="absolute top-10 left-12 flex items-center gap-3">
            <span class="text-2xl font-bold text-white tracking-tight">InSend</span>
        </div>
        
        <!-- Quote -->
        <div class="absolute bottom-12 left-12 right-12 text-white">
            <h2 class="text-[40px] font-bold leading-tight mb-6 tracking-tight">"Bantu Petani dari hal-hal kecil"</h2>
            <div>
                <p class="font-bold text-lg">sapardi</p>
                <p class="text-sm text-gray-300">Petani Padi</p>
            </div>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 md:p-24 overflow-y-auto bg-white">
        <div class="w-full max-w-sm">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Welcome back to inSend</h1>
                <p class="text-gray-500 mt-2 text-sm">Dengan InSend memberikan manfaat bagi petani dan juga UMKM lokal.</p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl text-sm border border-red-100">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 transition-all outline-none">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 transition-all outline-none">
                </div>

                <!-- Small addition for 'Forgot password?' layout matching -->
                <div class="flex items-center justify-between pb-2">
                    <a href="#" class="text-sm font-semibold text-[#00473B] hover:underline">Forgot password?</a>
                </div>

                <button type="submit" class="w-full bg-[#00473B] hover:bg-[#00382e] text-white font-medium py-2.5 rounded-xl transition-colors shadow-sm">
                    Sign In
                </button>
            </form>
        </div>
    </div>

</body>
</html>
