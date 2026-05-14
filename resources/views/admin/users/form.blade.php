@extends('admin.layouts.app')
@section('title', 'Edit User')

@section('content')
<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.users.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden max-w-2xl">
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="p-6 md:p-8 space-y-6">
        @csrf
        @method('PUT')

        <div class="space-y-1">
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
        </div>

        <div class="space-y-1">
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
        </div>

        <div class="space-y-1">
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone ?? '') }}"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
        </div>

        <div class="space-y-1">
            <label for="role" class="block text-sm font-medium text-gray-700">User Role</label>
            <select name="role" id="role" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-white">
                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-[#00473B] text-white font-medium rounded-lg hover:bg-[#00382e] transition-colors shadow-sm">
                Update User
            </button>
        </div>
    </form>
</div>
@endsection
