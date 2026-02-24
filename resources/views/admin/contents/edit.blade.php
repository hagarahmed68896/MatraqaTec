@extends('layouts.admin')

@section('title', __('Update Content Data'))
@section('page_title', __('Update Content Data'))

@section('content')
@php
    $initialItems = old('items') 
        ? collect(old('items'))->map(function($oldItem) use ($item) {
            $existingItem = isset($oldItem['id']) ? $item->items->find($oldItem['id']) : null;
            return [
                'id' => $oldItem['id'] ?? null,
                'title_ar' => $oldItem['title_ar'] ?? '',
                'title_en' => $oldItem['title_en'] ?? '',
                'description_ar' => $oldItem['description_ar'] ?? '',
                'description_en' => $oldItem['description_en'] ?? '',
                'button_text_ar' => $oldItem['button_text_ar'] ?? '',
                'button_text_en' => $oldItem['button_text_en'] ?? '',
                'show_button' => !empty($oldItem['button_text_ar']),
                'imagePreview' => $oldItem['image'] ?? ($existingItem ? $existingItem->full_image_url : null),
            ];
        })
        : $item->items->map(function($i) {
            return [
                'id' => $i->id,
                'title_ar' => $i->title_ar ?? '',
                'title_en' => $i->title_en ?? '',
                'description_ar' => $i->description_ar ?? '',
                'description_en' => $i->description_en ?? '',
                'button_text_ar' => $i->button_text_ar ?? '',
                'button_text_en' => $i->button_text_en ?? '',
                'show_button' => !empty($i->button_text_ar),
                'imagePreview' => $i->full_image_url,
            ];
        });
@endphp

<div class="max-w-4xl mx-auto pb-20" dir="rtl" x-data="contentManager({{ json_encode($initialItems) }}, {{ $item->is_visible ? 1 : 0 }})">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-10">
        <a href="{{ route('admin.contents.index') }}" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white shadow-sm border border-slate-100 dark:border-white/5 hover:scale-105 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path></svg>
        </a>
        <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Update Content Data') }}</h2>
    </div>

    @if ($errors->any())
        <div class="mb-8 p-6 rounded-3xl bg-red-500/10 border border-red-500/20">
            <div class="flex items-center gap-3 mb-4 text-red-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h3 class="text-lg font-black">{{ __('Error') }}</h3>
            </div>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-sm font-bold text-red-500/80">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.contents.update', $item->id) }}" method="POST" class="space-y-8" @submit.prevent="submitForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="items_json" x-ref="itemsJsonField" value="">
        
        {{-- Section 1: Content Metadata --}}
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-50 dark:border-white/5 space-y-8">
            <h3 class="text-lg font-black text-[#1A1A31] dark:text-slate-300">{{ __('Content Data') }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400">{{ __('Title (Arabic)') }}</label>
                    <input type="text" name="title_ar" value="{{ old('title_ar', $item->title_ar) }}" required placeholder="{{ __('Enter title') }}" class="w-full px-8 py-5 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all @error('title_ar') border-red-500 @enderror">
                    @error('title_ar') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-3" dir="ltr">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400 ">{{ __('Title (English)') }}</label>
                    <input type="text" name="title_en" value="{{ old('title_en', $item->title_en) }}" placeholder="Enter title" class="w-full px-8 py-5 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all font-sans @error('title_en') border-red-500 @enderror">
                    @error('title_en') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-6">
                <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400">{{ __('Status') }}</label>
                <div class="grid grid-cols-2 gap-4 p-2 bg-[#F8F9FE] dark:bg-white/5 rounded-[2rem] border border-slate-100 dark:border-white/5">
                    <label class="relative flex items-center justify-center gap-3 px-8 py-4 rounded-[1.5rem] cursor-pointer transition-all duration-300" :class="is_visible == 1 ? 'bg-white dark:bg-[#1A1A31] shadow-lg shadow-black/5' : 'opacity-50'">
                        <input type="radio" name="is_visible" value="1" x-model="is_visible" class="sr-only">
                        <div class="w-5 h-5 rounded-full border-2 border-slate-200 dark:border-white/10 flex items-center justify-center transition-all" :class="is_visible == 1 ? 'border-[#1A1A31] dark:border-white' : ''">
                            <div class="w-2.5 h-2.5 rounded-full transition-all" :class="is_visible == 1 ? 'bg-[#1A1A31] dark:bg-white' : ''"></div>
                        </div>
                        <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Active') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center gap-3 px-8 py-4 rounded-[1.5rem] cursor-pointer transition-all duration-300" :class="is_visible == 0 ? 'bg-white dark:bg-[#1A1A31] shadow-lg shadow-black/5' : 'opacity-50'">
                        <input type="radio" name="is_visible" value="0" x-model="is_visible" class="sr-only">
                        <div class="w-5 h-5 rounded-full border-2 border-slate-200 dark:border-white/10 flex items-center justify-center transition-all" :class="is_visible == 0 ? 'border-[#1A1A31] dark:border-white' : ''">
                            <div class="w-2.5 h-2.5 rounded-full transition-all" :class="is_visible == 0 ? 'bg-[#1A1A31] dark:bg-white' : ''"></div>
                        </div>
                        <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Inactive') }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Section 2: Banner Item Builder --}}
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-50 dark:border-white/5 space-y-10">
            <h3 class="text-lg font-black text-[#1A1A31] dark:text-slate-300">{{ __('Content Builder') }}</h3>

            {{-- Image Upload --}}
            <div class="space-y-4 mb-4 mt-8">
                <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400">{{ __('Banner Image') }}</label>
                <input type="file" id="item_image" class="hidden" accept="image/*" @change="handleImageUpload">
                
                <label for="item_image" class="relative flex flex-col items-center justify-center w-full h-[320px] rounded-[3rem] border-2 border-dashed cursor-pointer overflow-hidden transition-all" :class="errors.image ? 'border-red-500 bg-red-500/5' : 'border-slate-200 dark:border-white/10 hover:border-[#1A1A31] dark:hover:border-white bg-white dark:bg-[#1A1A31]'">
                    
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center gap-3 text-slate-400" x-show="!currentItem.imagePreview">
                        <svg class="w-16 h-16 text-[#1A1A31] dark:text-white opacity-20" viewBox="0 0 24 24" fill="none">
                            <path d="M10 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V9C22 7.89543 21.1046 7 20 7H12L10 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 11V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 13L12 10L15 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-base font-black text-[#1A1A31] dark:text-white">{{ __('Choose file or drag and drop here') }}</span>
                        <span class="text-xs font-bold text-slate-400">{{ __('JPG, JPEG, PNG, max 5MB') }}</span>
                    </div>

                    {{-- Preview State --}}
                    <div class="absolute inset-0 group" x-show="currentItem.imagePreview">
                        <img :src="currentItem.imagePreview" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                            <span class="px-6 py-2 bg-white text-[#1A1A31] rounded-xl text-sm font-black">{{ __('Change Photo') }}</span>
                        </div>
                    </div>
                </label>
                <p x-show="errors.image" class="text-xs text-red-500 mt-1 font-bold" x-text="errors.image"></p>
            </div>

            {{-- Titles --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-4 mt-4">
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400">{{ __('Title (Arabic)') }}</label>
                    <input type="text" x-model="currentItem.title_ar" @input="errors.title_ar = ''" placeholder="{{ __('Enter title') }}" class="w-full px-8 py-5 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all" :class="errors.title_ar ? 'border-red-500' : ''">
                    <p x-show="errors.title_ar" class="text-xs text-red-500 mt-1 font-bold" x-text="errors.title_ar" x-cloak></p>
                </div>
                <div class="space-y-3" dir="ltr">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400 ">{{ __('Title (English)') }}</label>
                    <input type="text" x-model="currentItem.title_en" placeholder="Enter title" class="w-full px-8 py-5 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-left font-sans">
                </div>
            </div>

            {{-- Descriptions --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400">{{ __('Description (Arabic)') }}</label>
                    <textarea x-model="currentItem.description_ar" rows="4" placeholder="{{ __('Enter description') }}" class="w-full px-8 py-5 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all resize-none"></textarea>
                </div>
                <div class="space-y-3" dir="ltr">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400 ">{{ __('Description (English)') }}</label>
                    <textarea x-model="currentItem.description_en" rows="4" placeholder="Enter description" class="w-full px-8 py-5 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-left resize-none font-sans"></textarea>
                </div>
            </div>

            {{-- Button Config --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400">{{ __('Button Text (Arabic)') }}</label>
                    <div class="flex gap-4">
                        <button type="button" @click="currentItem.show_button = !currentItem.show_button" class="px-6 py-4 rounded-2xl border-2 transition-all flex items-center gap-2" :class="currentItem.show_button ? 'bg-[#1A1A31] text-white border-[#1A1A31]' : 'border-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="currentItem.show_button ? 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.049m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18'"></path></svg>
                            <span class="text-xs font-black">{{ __('Button') }}</span>
                        </button>
                        <input type="text" x-model="currentItem.button_text_ar" placeholder="{{ __('Enter button text') }}" class="flex-1 px-8 py-5 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all">
                    </div>
                </div>
                <div class="space-y-3" dir="ltr">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-400 ">{{ __('Button Text (English)') }}</label>
                    <input type="text" x-model="currentItem.button_text_en" placeholder="Enter text" class="w-full px-8 py-5 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-left font-sans">
                </div>
            </div>

            <div class="pt-6 border-t border-slate-50 dark:border-white/5">
                <button type="button" @click="addItem()" class="w-full py-5 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black shadow-xl shadow-[#1A1A31]/10 dark:shadow-white/5 hover:scale-[0.99] transition-all">
                    {{ __('Add Content Item') }}
                </button>
            </div>
        </div>

        {{-- Section 3: View Content (Preview List) --}}
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-50 dark:border-white/5 space-y-8" x-show="items.length > 0" x-cloak>
            <h3 class="text-lg font-black text-[#1A1A31] dark:text-slate-300">{{ __('View Content') }}</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="(item, index) in items" :key="index">
                    <div class="relative group rounded-3xl overflow-hidden bg-slate-50 dark:bg-white/5 h-48 shadow-sm border border-slate-100 dark:border-white/5 transition-all hover:shadow-xl">
                        {{-- Image Display Fix --}}
                        <div class="absolute inset-0 w-full h-full bg-slate-100 dark:bg-white/5 flex items-center justify-center">
                            <svg x-show="!item.imagePreview" class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <img :src="item.imagePreview" class="absolute inset-0 w-full h-full object-cover" x-show="item.imagePreview">
                        
                        {{-- Standard Banner Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent p-5 flex flex-col justify-end text-right">
                            <h4 class="text-base font-black text-white leading-tight drop-shadow-md" x-text="item.title_ar"></h4>
                            <p class="text-[10px] font-bold text-white/70 line-clamp-1 mt-1" x-text="item.description_ar"></p>
                            <div class="mt-3 flex items-center justify-between">
                                <span x-show="item.show_button" class="px-4 py-1.5 bg-white text-[#1A1A31] rounded-xl text-[9px] font-black shadow-lg" x-text="item.button_text_ar || '{{ __('Order Now') }}'"></span>
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    <button type="button" @click="editItem(index)" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/20 backdrop-blur-md text-white hover:bg-white hover:text-[#1A1A31] transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button type="button" @click="removeItem(index)" class="w-8 h-8 flex items-center justify-center rounded-xl bg-red-500 backdrop-blur-md text-white hover:bg-red-600 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.895-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Final Actions --}}
        <div class="flex items-center justify-end gap-4 p-4 bg-white/80 dark:bg-[#1A1A31]/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-2xl sticky bottom-4 z-50">
            <a href="{{ route('admin.contents.index') }}" class="px-8 py-5 bg-gray-200 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black transition-all hover:bg-red-50 hover:text-red-500">
                {{ __('Cancel') }}
            </a>
            <button type="submit" class="px-8 py-5 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black shadow-xl shadow-[#1A1A31]/10 dark:shadow-white/5 hover:scale-[1.02] transition-all">
                {{ __('Save') }}
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('contentManager', (initialItems, isVisible) => ({
        is_visible: isVisible,
        items: initialItems,
        currentItem: {
            title_ar: '',
            title_en: '',
            description_ar: '',
            description_en: '',
            button_text_ar: '',
            button_text_en: '',
            show_button: true,
            imagePreview: null
        },
        errors: {
            title_ar: '',
            image: ''
        },
        
        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.errors.image = '';
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.currentItem.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        
        addItem() {
            // Reset errors
            this.errors.title_ar = '';
            this.errors.image = '';

            // Robust validation check
            let hasError = false;
            if (!this.currentItem.title_ar) {
                this.errors.title_ar = '{{ __('يرجى إدخال العنوان العربي') }}';
                hasError = true;
            }
            if (!this.currentItem.imagePreview) {
                this.errors.image = '{{ __('يرجى اختيار صورة للبانر') }}';
                hasError = true;
            }

            if (hasError) return;

            // Create a fresh copy of currentItem
            this.items.push({...this.currentItem});
            
            // Reset currentItem
            this.currentItem = {
                title_ar: '',
                title_en: '',
                description_ar: '',
                description_en: '',
                button_text_ar: '',
                button_text_en: '',
                show_button: true,
                imagePreview: null
            };
            
            // Clear file input
            const fileInput = document.getElementById('item_image');
            if (fileInput) fileInput.value = '';
        },
        
        removeItem(index) {
            this.items.splice(index, 1);
        },
        
        editItem(index) {
            this.currentItem = {...this.items[index]};
            this.items.splice(index, 1);
        },

        submitForm(event) {
            const form = event.target;
            this.$refs.itemsJsonField.value = JSON.stringify(this.items);
            form.submit();
        }
    }));
});
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection