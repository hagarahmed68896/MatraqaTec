@extends('layouts.admin')

@section('title', __('Edit Service') . ' #' . $item->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Edit Service') }} #{{ $item->id }}</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-bold mt-1">{{ __('Update service details and manage sub-services') }}</p>
        </div>
        <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
            {{ __('Back to List') }}
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.services.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ 
        subServices: {{ $item->children->map(function($child) {
            return [
                'id' => $child->id,
                'name_ar' => $child->name_ar,
                'name_en' => $child->name_en,
                'image_url' => $child->image ? asset($child->image) : null,
                'is_existing' => true
            ];
        })->toJson() }},
        addSubService() {
            this.subServices.push({
                id: null,
                temp_id: Date.now(),
                name_ar: '',
                name_en: '',
                image_url: null,
                is_existing: false
            });
        },
        removeSubService(index) {
            this.subServices.splice(index, 1);
        }
    }">
        @csrf
        @method('PUT')

        <!-- Main Info -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm space-y-6">
            <h3 class="text-lg font-black text-slate-800 dark:text-white border-b border-slate-100 dark:border-white/5 pb-4">{{ __('Basic Information') }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name AR -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Name (Arabic)') }}</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $item->name_ar) }}" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-primary text-slate-800 dark:text-white text-sm font-bold" required>
                    @error('name_ar') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>

                <!-- Name EN -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Name (English)') }}</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $item->name_en) }}" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-primary text-slate-800 dark:text-white text-sm font-bold" required>
                    @error('name_en') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>

                <!-- Description AR -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Description (Arabic)') }}</label>
                    <textarea name="description_ar" rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-primary text-slate-800 dark:text-white text-sm font-bold">{{ old('description_ar', $item->description_ar) }}</textarea>
                </div>

                <!-- Description EN -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Description (English)') }}</label>
                    <textarea name="description_en" rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-primary text-slate-800 dark:text-white text-sm font-bold">{{ old('description_en', $item->description_en) }}</textarea>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Price') }} ({{ __('Optional') }})</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="price" value="{{ old('price', $item->price) }}" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-primary text-slate-800 dark:text-white text-sm font-bold">
                        <span class="absolute right-4 top-3.5 text-xs font-black text-slate-400">{{ __('SAR') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm space-y-6">
            <h3 class="text-lg font-black text-slate-800 dark:text-white border-b border-slate-100 dark:border-white/5 pb-4">{{ __('Media') }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Main Image -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Main Image') }}</label>
                    <div class="relative w-full h-48 rounded-xl bg-slate-50 dark:bg-white/5 border-2 border-dashed border-slate-200 dark:border-white/10 flex flex-col items-center justify-center text-center cursor-pointer hover:border-primary transition-colors group overflow-hidden">
                        <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" accept="image/*" onchange="previewImage(this, 'mainImagePreview')">
                        <img id="mainImagePreview" src="{{ $item->image ? asset($item->image) : '' }}" class="absolute inset-0 w-full h-full object-cover {{ $item->image ? '' : 'opacity-0' }} transition-opacity z-0">
                        <div class="z-0 pointer-events-none group-hover:scale-110 transition-transform duration-300 {{ $item->image ? 'opacity-0 group-hover:opacity-100' : '' }}">
                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-500 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ __('Click to upload') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Icon -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Icon') }}</label>
                    <div class="relative w-full h-48 rounded-xl bg-slate-50 dark:bg-white/5 border-2 border-dashed border-slate-200 dark:border-white/10 flex flex-col items-center justify-center text-center cursor-pointer hover:border-primary transition-colors group overflow-hidden">
                        <input type="file" name="icon" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" accept="image/*" onchange="previewImage(this, 'iconPreview')">
                        <img id="iconPreview" src="{{ $item->icon ? asset($item->icon) : '' }}" class="absolute inset-0 w-full h-full object-contain p-4 {{ $item->icon ? '' : 'opacity-0' }} transition-opacity z-0">
                        <div class="z-0 pointer-events-none group-hover:scale-110 transition-transform duration-300 {{ $item->icon ? 'opacity-0 group-hover:opacity-100' : '' }}">
                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-500 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ __('Click to upload') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sub Services Repeater -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/5 pb-4">
                <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Sub Services') }}</h3>
                <button type="button" @click="addSubService()" class="px-3 py-1.5 bg-primary/10 text-primary text-xs font-black rounded-lg hover:bg-primary/20 transition-all">
                    + {{ __('Add Sub Service') }}
                </button>
            </div>
            
            <template x-if="subServices.length === 0">
                <div class="text-center py-8 text-slate-400 dark:text-slate-500 text-sm font-bold italic">
                    {{ __('No sub-services added yet.') }}
                </div>
            </template>

            <div class="space-y-4">
                <template x-for="(sub, index) in subServices" :key="sub.id || sub.temp_id">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 relative group">
                        <button type="button" @click="removeSubService(index)" class="absolute top-2 right-2 p-1 text-slate-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        
                        <input type="hidden" :name="`children[${index}][id]`" x-model="sub.id">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">{{ __('Name (Arabic)') }}</label>
                                <input type="text" :name="`children[${index}][name_ar]`" x-model="sub.name_ar" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#1A1A31] border-none text-slate-800 dark:text-white text-xs font-bold shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">{{ __('Name (English)') }}</label>
                                <input type="text" :name="`children[${index}][name_en]`" x-model="sub.name_en" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#1A1A31] border-none text-slate-800 dark:text-white text-xs font-bold shadow-sm" required>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">{{ __('Upload Image') }}</label>
                                    <input type="file" :name="`children[${index}][image]`" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                </div>
                                <template x-if="sub.image_url">
                                    <img :src="sub.image_url" class="w-10 h-10 rounded-lg object-cover bg-white shadow-sm border border-slate-100">
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex justify-end pt-6">
            <button type="submit" class="px-8 py-3 bg-primary hover:bg-primary-dark text-white font-black rounded-xl shadow-xl shadow-primary/20 transition-all transform hover:-translate-y-1">
                {{ __('Update Service') }}
            </button>
        </div>
    </form>
</div>

<script>
    function previewImage(input, imgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(imgId);
                img.src = e.target.result;
                img.classList.remove('opacity-0');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection