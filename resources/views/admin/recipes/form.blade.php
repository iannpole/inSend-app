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

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-gray-700">Instructions (Steps)</label>
                <button type="button" onclick="addInstruction()" class="text-sm text-[#00473B] hover:text-[#00382e] font-medium">+ Add Step</button>
            </div>
            <div id="instructions-container" class="space-y-3">
                @if(isset($recipe) && is_array($recipe->instructions) && count($recipe->instructions) > 0)
                    @foreach($recipe->instructions as $index => $step)
                        <div class="flex gap-2 items-start instruction-item">
                            <span class="mt-2 text-sm text-gray-500 font-medium w-6 step-number">{{ $index + 1 }}.</span>
                            <textarea name="instructions[]" rows="2" required class="flex-1 px-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm">{{ $step }}</textarea>
                            <button type="button" onclick="this.parentElement.remove(); updateInstructionNumbers();" class="mt-2 text-red-500 hover:text-red-700 p-1">✕</button>
                        </div>
                    @endforeach
                @else
                    <div class="flex gap-2 items-start instruction-item">
                        <span class="mt-2 text-sm text-gray-500 font-medium w-6 step-number">1.</span>
                        <textarea name="instructions[]" rows="2" required class="flex-1 px-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm"></textarea>
                        <button type="button" onclick="this.parentElement.remove(); updateInstructionNumbers();" class="mt-2 text-red-500 hover:text-red-700 p-1">✕</button>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-1">
            <label for="images" class="block text-sm font-medium text-gray-700">Images</label>
            <input type="file" name="images[]" id="images" multiple accept="image/*"
                class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-white file:text-gray-700 file:border file:border-gray-200 hover:file:bg-gray-50">
            @if(isset($recipe) && is_array($recipe->images) && count($recipe->images) > 0)
                <div class="flex gap-3 mt-4 flex-wrap" id="existing-images-container">
                    @foreach($recipe->images as $img)
                        <div class="relative group" id="img-container-{{ md5($img) }}">
                            <img src="{{ \Illuminate\Support\Str::startsWith($img, 'http') ? $img : asset('storage/' . $img) }}" class="h-24 w-24 object-cover rounded-xl border border-gray-200" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Image&color=00473B&background=E6F2ED';">
                            <button type="button" onclick="removeImage('{{ $img }}', 'img-container-{{ md5($img) }}')" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-md opacity-0 group-hover:opacity-100 transition-all cursor-pointer">
                                ✕
                            </button>
                        </div>
                    @endforeach
                </div>
                <div id="deleted-images-inputs"></div>
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

@push('scripts')
<script>
    function removeImage(imagePath, containerId) {
        // Remove the image element from the view
        document.getElementById(containerId).remove();
        
        // Add a hidden input to submit the deleted image path
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'deleted_images[]';
        input.value = imagePath;
        document.getElementById('deleted-images-inputs').appendChild(input);
    }

    function updateInstructionNumbers() {
        const items = document.querySelectorAll('.instruction-item');
        items.forEach((item, index) => {
            const numSpan = item.querySelector('.step-number');
            if (numSpan) {
                numSpan.textContent = (index + 1) + '.';
            }
        });
    }

    function addInstruction() {
        const container = document.getElementById('instructions-container');
        const itemCount = container.querySelectorAll('.instruction-item').length;
        
        const html = `
            <div class="flex gap-2 items-start instruction-item">
                <span class="mt-2 text-sm text-gray-500 font-medium w-6 step-number">${itemCount + 1}.</span>
                <textarea name="instructions[]" rows="2" required class="flex-1 px-4 py-2 rounded-lg border border-gray-200 focus:border-[#00473B] focus:ring focus:ring-[#00473B] focus:ring-opacity-20 outline-none transition-all text-sm"></textarea>
                <button type="button" onclick="this.parentElement.remove(); updateInstructionNumbers();" class="mt-2 text-red-500 hover:text-red-700 p-1">✕</button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
    }
</script>
@endpush
@endsection
