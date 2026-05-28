@extends('admin.layouts.app')
@section('title', 'Edit Blog Post')

@section('content')
<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.blog.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
        <p class="text-sm text-gray-500 mt-1">Update existing blog article.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <form action="{{ route('admin.blog.update', $post->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">Post Title</label>
                <input type="text" name="title" id="title" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all" value="{{ old('title', $post->title) }}" required>
                @error('title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-semibold text-gray-900 mb-2">Content (Markdown / HTML)</label>
                <textarea name="content" id="content" rows="15" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all font-mono text-sm" required>{{ old('content', $post->content) }}</textarea>
                @error('content') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Excerpt -->
            <div>
                <label for="excerpt" class="block text-sm font-semibold text-gray-900 mb-2">Excerpt (Short Description)</label>
                <textarea name="excerpt" id="excerpt" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all">{{ old('excerpt', $post->excerpt) }}</textarea>
                @error('excerpt') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Image & Category -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="image" class="block text-sm font-semibold text-gray-900 mb-2">Featured Image</label>
                    @if($post->image)
                        <div class="mb-3">
                            <img src="{{ Storage::url($post->image) }}" class="w-32 h-20 object-cover rounded-lg border border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="image" id="image" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00473B] outline-none transition-all text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#00473B]/10 file:text-[#00473B] hover:file:bg-[#00473B]/20">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep existing image</p>
                    @error('image') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="category" class="block text-sm font-semibold text-gray-900 mb-2">Category</label>
                    <input type="text" name="category" id="category" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all" value="{{ old('category', $post->category) }}" required>
                    @error('category') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-sm font-semibold text-gray-900 mb-2">Tags (Comma separated)</label>
                <input type="text" name="tags" id="tags" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all" value="{{ old('tags', is_array($post->tags) ? implode(', ', $post->tags) : $post->tags) }}">
                @error('tags') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Publish Options -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="is_published" value="1" class="sr-only" {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
                        <div class="block bg-gray-200 w-12 h-7 rounded-full transition-colors"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition-transform"></div>
                    </div>
                    <div class="ml-3 text-sm font-medium text-gray-900">
                        Published
                    </div>
                </label>
                
                <div class="flex gap-3">
                    <a href="{{ route('admin.blog.index') }}" class="px-6 py-3 rounded-xl font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 rounded-xl font-semibold text-white bg-[#00473B] hover:bg-[#00382e] transition-colors shadow-sm">
                        Update Post
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Toggle Switch Styles */
    input:checked ~ .block { background-color: #00473B; }
    input:checked ~ .dot { transform: translateX(100%); }
</style>
@endsection
