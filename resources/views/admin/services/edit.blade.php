@extends('layouts.admin')

@section('title', __('Edit Service Data'))

@section('content')
<div class="max-w-5xl mx-auto" dir="rtl">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.services.index') }}" class="w-10 h-10 flex items-center justify-center bg-white dark:bg-white/5 rounded-xl shadow-sm hover:bg-slate-50 dark:hover:bg-white/10 transition-colors">
            <svg class="w-6 h-6 text-slate-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
        <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Edit Service Data') }}</h2>
    </div>

    <form action="{{ route('admin.services.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8" 
        x-data="{ 
            subServices: @js($item->children->map(fn($child) => [
                'id' => $child->id,
                'temp_id' => $child->id,
                'name_ar' => $child->name_ar,
                'name_en' => $child->name_en,
                'image_url' => $child->image_url,
                'is_deleted' => false
            ])),
            newSubService: {
                name_ar: '',
                name_en: '',
                image: null,
                preview: null
            },
            addSubService() {
                if(!this.newSubService.name_ar || !this.newSubService.name_en) return;
                
                this.subServices.push({
                    id: null,
                    temp_id: Date.now(),
                    name_ar: this.newSubService.name_ar,
                    name_en: this.newSubService.name_en,
                    image_url: this.newSubService.preview,
                    file: this.newSubService.image,
                    is_deleted: false
                });
                
                // Reset form
                this.newSubService = { name_ar: '', name_en: '', image: null, preview: null };
                this.$refs.subImageInput.value = '';
            },
            removeSubService(index) {
                if(this.subServices[index].id) {
                    this.subServices[index].is_deleted = true;
                } else {
                    this.subServices.splice(index, 1);
                }
            },
            handleNewSubImage(e) {
                const file = e.target.files[0];
                if (file) {
                    this.newSubService.image = file;
                    const reader = new FileReader();
                    reader.onload = (e) => { this.newSubService.preview = e.target.result; };
                    reader.readAsDataURL(file);
                }
            }
        }">
        @csrf
        @method('PUT')

        {{-- Main Service Card --}}
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-50 dark:border-white/5 space-y-10">
            <h3 class="text-xl font-black text-[#1A1A31] dark:text-white">{{ __('Main Service Data') }}</h3>
            
            {{-- Images Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 mb-4 gap-8 mb-4">
                {{-- Service Photo --}}
                <div class="space-y-4">
                    <label class="block mt-2 text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Service Photo') }}</label>
                    <div class="relative h-48 rounded-[2rem] bg-slate-50 dark:bg-white/5 border-2 border-dashed border-slate-100 dark:border-white/10 flex flex-col items-center justify-center group overflow-hidden">
                        <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer z-20" accept="image/*" onchange="previewMainImage(this, 'mainPhotoPreview')">
                        <img id="mainPhotoPreview" src="{{ $item->image_url }}" class="absolute inset-0 w-full h-full object-cover z-10 transition-opacity">
                        <div class="z-0 text-center">
                            <svg class="w-12 h-12 text-slate-200 dark:text-slate-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Click to upload') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Service Icon --}}
                <div class="space-y-4 mt-3">
                    <label class="block mt-2 text-sm font-black text-[#1A1A31] dark:text-slate-300 text-left">{{ __('Service Icon') }}</label>
                    <div class="relative h-48 rounded-[2rem] bg-slate-50 dark:bg-white/5 border-2 border-dashed border-slate-100 dark:border-white/10 flex flex-col items-center justify-center group overflow-hidden">
                        <input type="file" name="icon" class="absolute inset-0 opacity-0 cursor-pointer z-20" accept="image/*" onchange="previewMainImage(this, 'mainIconPreview')">
                        <img id="mainIconPreview" src="{{ $item->icon_url }}" class="absolute inset-0 w-full h-full object-contain p-10 z-10 transition-opacity">
                        <div class="z-0 text-center">
                            <svg class="w-12 h-12 text-slate-200 dark:text-slate-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Click to upload') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Names Row --}}
            <div class="grid grid-cols-1 mt-4 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <label class="block mt-2 text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Main Service Name (Arabic)') }}</label>
                    <input type="text" name="name_ar" value="{{ $item->name_ar }}" placeholder="{{ __('Enter main service name') }}" class="w-full px-6 py-4 rounded-2xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none">
                </div>
                <div class="space-y-4">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300 text-left">{{ __('Main Service Name (English)') }}</label>
                    <input type="text" name="name_en" value="{{ $item->name_en }}" placeholder="{{ __('Enter main service name') }}" class="w-full px-6 py-4 rounded-2xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none text-left">
                </div>
            </div>

            {{-- Price Row --}}
            <div class="space-y-4 mt-4">
                <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Price') }}</label>
                <div class="relative">
                    <input type="number" step="0.01" name="price" value="{{ $item->price }}" placeholder="{{ __('Enter service price') }}" class="w-full px-16 py-4 rounded-2xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none">
                    <div class="absolute right-6 top-1/2 -translate-y-1/2">
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-5 h-5 opacity-40 dark:invert">
                    </div>
                </div>
            </div>

            {{-- Sub-services Section --}}
            <div class="space-y-8 pt-6 border-t border-slate-50 dark:border-white/5">
                <h3 class="text-xl font-black text-[#1A1A31] dark:text-white">{{ __('Sub-services') }}</h3>
                
                {{-- Add Sub-service Form --}}
                <div class="p-8 rounded-[2rem] bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/10 space-y-8">
                    <div class="space-y-4">
                        <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Service Photo') }}</label>
                        <div class="relative h-32 w-full max-w-xs rounded-2xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 flex flex-col items-center justify-center group overflow-hidden">
                            <input type="file" @change="handleNewSubImage" x-ref="subImageInput" class="absolute inset-0 opacity-0 cursor-pointer z-20" accept="image/*">
                            <template x-if="newSubService.preview">
                                <img :src="newSubService.preview" class="absolute inset-0 w-full h-full object-cover z-10">
                            </template>
                            <template x-if="!newSubService.preview">
                                <div class="z-0 text-center">
                                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Upload Image') }}</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Service Name (Arabic)') }}</label>
                            <input type="text" x-model="newSubService.name_ar" placeholder="{{ __('Enter service name') }}" class="w-full px-6 py-4 rounded-2xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none">
                        </div>
                        <div class="space-y-4">
                            <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300 text-left">{{ __('Service Name (English)') }}</label>
                            <input type="text" x-model="newSubService.name_en" placeholder="{{ __('Enter service name') }}" class="w-full px-6 py-4 rounded-2xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none text-left">
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="button" @click="addSubService()" class="px-8 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm hover:scale-[1.02] transition-all shadow-lg">
                            {{ __('Add Sub-service') }}
                        </button>
                    </div>
                </div>

                {{-- Sub-services List --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <template x-for="(sub, index) in subServices" :key="sub.temp_id">
                        <div x-show="!sub.is_deleted" class="p-4 rounded-3xl bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/10 flex items-center justify-between group h-24 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-white dark:bg-white/10 shadow-sm overflow-hidden flex-shrink-0">
                                    <img :src="sub.image_url" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="text-sm font-black text-[#1A1A31] dark:text-white" x-text="sub.name_ar"></p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold" x-text="sub.name_en"></p>
                                </div>
                            </div>
                            
                            {{-- Hidden inputs --}}
                            <input type="hidden" :name="`children[${index}][id]`" :value="sub.id">
                            <input type="hidden" :name="`children[${index}][name_ar]`" :value="sub.name_ar">
                            <input type="hidden" :name="`children[${index}][name_en]`" :value="sub.name_en">
                            <input type="hidden" :name="`children[${index}][is_deleted]`" :value="sub.is_deleted">
                            <input type="file" :name="`children[${index}][image]`" style="display:none" x-ref="subFile" @change="sub.file = $event.target.files[0]">

                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click="removeSubService(index)" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.895-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-center gap-4">
            <button type="submit" class="px-8 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/20 dark:shadow-white/5 hover:scale-[1.02] transition-all">
                {{ __('Save') }}
            </button>
            <a href="{{ route('admin.services.index') }}" class="px-8 py-4 bg-gray-200 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black text-sm hover:bg-slate-300 dark:hover:bg-white/10 transition-all text-center">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>

<script>
    function previewMainImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.getElementById(previewId);
                img.src = e.target.result;
                img.classList.remove('opacity-0');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection