@extends('admin.layouts.app')
@section('title', isset($recipe) ? 'Edit Recipe' : 'Add Recipe')

@section('content')
<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.recipes.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isset($recipe) ? 'Edit Recipe' : 'Add Recipe' }}</h1>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl">
    <form action="{{ isset($recipe) ? route('admin.recipes.update', $recipe->id) : route('admin.recipes.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
        @csrf
        @if(isset($recipe))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
                <label for="title" class="block text-sm font-medium text-gray-700">Recipe Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $recipe->title ?? '') }}" required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            </div>

            <div class="space-y-1">
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <input type="text" name="category" id="category" value="{{ old('category', $recipe->category ?? '') }}" required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="prep_time" class="block text-sm font-medium text-gray-700">Prep Time (mins)</label>
                    <input type="number" name="prep_time" id="prep_time" value="{{ old('prep_time', $recipe->prep_time ?? 0) }}" min="0" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
                </div>
                <div class="space-y-1">
                    <label for="cook_time" class="block text-sm font-medium text-gray-700">Cook Time (mins)</label>
                    <input type="number" name="cook_time" id="cook_time" value="{{ old('cook_time', $recipe->cook_time ?? 0) }}" min="0" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="servings" class="block text-sm font-medium text-gray-700">Servings</label>
                    <input type="number" name="servings" id="servings" value="{{ old('servings', $recipe->servings ?? 1) }}" min="1" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">
                </div>
                <div class="space-y-1">
                    <label for="difficulty" class="block text-sm font-medium text-gray-700">Difficulty</label>
                    <select name="difficulty" id="difficulty" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-white">
                        <option value="easy" {{ old('difficulty', $recipe->difficulty ?? '') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', $recipe->difficulty ?? '') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty', $recipe->difficulty ?? '') == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="space-y-1">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="3"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">{{ old('description', $recipe->description ?? '') }}</textarea>
        </div>

        <div class="space-y-1">
            <label for="images" class="block text-sm font-medium text-gray-700">Images</label>
            <input type="file" name="images[]" id="images" multiple accept="image/*"
                class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-white file:text-gray-700 file:border file:border-gray-200 hover:file:bg-gray-50">
            @if(isset($recipe) && is_array($recipe->images))
                <div class="flex gap-2 mt-4 flex-wrap">
                    @foreach($recipe->images as $img)
                        <img src="{{ asset('storage/' . $img) }}" class="h-20 w-20 object-cover rounded-lg border border-gray-200">
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $recipe->is_published ?? true) ? 'checked' : '' }}
                class="h-4 w-4 text-[#00473B] focus:ring-[#00473B] border-gray-300 rounded">
            <label for="is_published" class="ml-2 block text-sm text-gray-900">
                Published (Visible to users)
            </label>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-[#00473B] text-white font-medium rounded-lg hover:bg-[#00382e] transition-colors shadow-sm">
                {{ isset($recipe) ? 'Update Recipe' : 'Save Recipe' }}
            </button>
        </div>
    </form>
</div>
@endsection
