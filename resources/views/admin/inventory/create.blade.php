@extends('layouts.admin')

@section('title', __('Add Item'))

@section('content')
<div class="max-w-4xl mx-auto" dir="rtl">
    {{-- Header --}}
    <div class="flex mb-4 items-center gap-4 mb-8">
        <a href="{{ route('admin.inventory.index') }}" class="w-10 h-10 flex items-center justify-center bg-white dark:bg-white/5 rounded-xl shadow-sm hover:bg-slate-50 dark:hover:bg-white/10 transition-colors">
            <svg class="w-6 h-6 text-slate-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
        <h2 class="text-2xl mb-2 font-black text-[#1A1A31] dark:text-white">{{ __('Add Item') }}</h2>
    </div>

    <form action="{{ route('admin.inventory.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- Main Data Card --}}
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-50 dark:border-white/5 space-y-10">
            <h3 class="text-xl font-black text-[#1A1A31] dark:text-white leading-none">{{ __('Item Data') }}</h3>
            
            {{-- Photo Section --}}
            <div class="space-y-4 mb-4 mt-4">
                <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Photo') }}</label>
                <div id="drop-area" class="relative group">
                    <input type="file" name="image" id="image-input" class="absolute inset-0 opacity-0 cursor-pointer z-20" accept="image/*" onchange="handleFile(this)">
                    
                    {{-- Dropzone State --}}
                    <div id="dropzone-empty" class="h-24 w-full rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-dashed border-slate-200 dark:border-white/10 flex items-center justify-center transition-all group-hover:border-[#1A1A31]/20 dark:group-hover:border-white/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-white/10 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Click to upload') }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">{{ __('Max size 2MB') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Preview State (Hidden by default) --}}
                    <div id="dropzone-preview" class="hidden h-24 w-full rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-200 dark:border-white/10 px-6 flex items-center justify-between transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-white dark:bg-white/10 p-2 flex items-center justify-center shadow-sm overflow-hidden">
                                <img id="preview-img" src="" class="w-full h-full object-contain">
                            </div>
                            <div class="text-right">
                                <p id="file-name" class="text-xs font-black text-[#1A1A31] dark:text-white truncate max-w-[200px]"></p>
                                <p id="file-size" class="text-[10px] font-bold text-slate-400 uppercase"></p>
                            </div>
                        </div>
                        <button type="button" onclick="clearImage()" class="w-10 h-10 flex items-center justify-center bg-white dark:bg-white/10 rounded-xl text-slate-400 hover:text-red-500 transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.895-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Names Row --}}
            <div class="grid mb-4 grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Item Name (Arabic)') }}</label>
                    <input type="text" name="name_ar" placeholder="{{ __('Enter item name') }}" required class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none">
                </div>
                <div class="space-y-4">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300 text-left">{{ __('Item Name (English)') }}</label>
                    <input type="text" name="name_en" placeholder="{{ __('Enter item name') }}" required class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none text-left">
                </div>
            </div>

            {{-- Price & Status Row --}}
            <div class="grid mb-4 grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Price') }}</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="price" placeholder="{{ __('0.00') }}" required class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none pr-14">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white dark:bg-white/10 rounded-xl flex items-center justify-center border border-slate-100 dark:border-white/10">
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-5 h-5 opacity-40 dark:invert">
                        </div>
                    </div>
                </div>
               <div class="space-y-4" x-data="{ status: 'available' }">
    <div class="flex items-center justify-between">
        <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">
            {{ __('Status') }}
        </label>
        <span :class="status === 'available' ? 'bg-green-500' : 'bg-red-500'" 
              class="w-2 h-2 rounded-full animate-pulse transition-colors duration-500"></span>
    </div>

    <div class="grid grid-cols-2 gap-3 p-1.5 bg-[#F8F9FE] dark:bg-white/5 rounded-[2rem] border border-slate-100 dark:border-white/10 relative">
        
        <input type="hidden" name="status" :value="status">

        <button type="button" 
                @click="status = 'available'"
                :class="status === 'available' ? 'bg-white dark:bg-[#1A1A31] shadow-md text-[#1A1A31] dark:text-white' : 'text-slate-400 hover:text-slate-600'"
                class="flex items-center justify-center gap-2 py-3 px-4 rounded-[1.5rem] text-sm font-bold transition-all duration-300 z-10">
            <svg class="w-4 h-4" :class="status === 'available' ? 'text-green-500' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ __('Available') }}
        </button>

        <button type="button" 
                @click="status = 'not_available'"
                :class="status === 'not_available' ? 'bg-white dark:bg-[#1A1A31] shadow-md text-[#1A1A31] dark:text-white' : 'text-slate-400 hover:text-slate-600'"
                class="flex items-center justify-center gap-2 py-3 px-4 rounded-[1.5rem] text-sm font-bold transition-all duration-300 z-10">
            <svg class="w-4 h-4" :class="status === 'not_available' ? 'text-red-500' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ __('Out of Stock') }}
        </button>
    </div>
</div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('admin.inventory.index') }}" class="px-8 py-4 bg-gray-200 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black text-sm hover:bg-[#E2E6F0] dark:hover:bg-white/10 transition-all">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-12 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/10 dark:shadow-white/5 hover:scale-[1.02] transition-all">
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function handleFile(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('dropzone-empty').classList.add('hidden');
                document.getElementById('dropzone-preview').classList.remove('hidden');
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('file-name').textContent = file.name;
                document.getElementById('file-size').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            }
            
            reader.readAsDataURL(file);
        }
    }

    function clearImage() {
        const input = document.getElementById('image-input');
        input.value = '';
        document.getElementById('dropzone-empty').classList.remove('hidden');
        document.getElementById('dropzone-preview').classList.add('hidden');
        document.getElementById('preview-img').src = '';
    }
</script>
@endsection
