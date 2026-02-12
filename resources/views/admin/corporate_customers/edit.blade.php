@extends('layouts.admin')

@section('page_title', __('Edit Corporate Account Data'))

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.corporate-customers.index') }}" 
               class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/5 text-slate-400 hover:text-primary transition-all shadow-sm">
                <svg class="w-6 h-6 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Edit Account Data') }}</h1>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-white/5 overflow-hidden">
        <form action="{{ route('admin.corporate-customers.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="p-8 md:p-12">
            @csrf
            @method('PUT')

            <div class="mb-10 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">{{ __('Account Data') }}</h3>
                <div class="h-1.5 w-12 bg-primary rounded-full"></div>
            </div>

            <div class="space-y-8">
                <!-- Grid Row 1: Company Name & Phone -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Company Name -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Company Name') }}</label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', $item->user->name) }}" 
                               placeholder="{{ __('Enter company name') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('name') border-red-500 @enderror">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Phone -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">{{ __('Phone Number') }}</label>
                        <div class="flex items-center w-full bg-slate-50 dark:bg-white/5 border border-transparent focus-within:border-primary focus-within:bg-white dark:focus-within:bg-[#1A1A31] rounded-2xl transition-all overflow-hidden" dir="ltr">
                            <div class="pl-6 pr-4 flex items-center justify-center border-r border-slate-200 dark:border-white/10 h-full">
                                <span class="text-slate-400 font-bold text-sm whitespace-nowrap">+966</span>
                            </div>
                            <input type="text" 
                                   name="phone" 
                                   value="{{ old('phone', $item->user->phone) }}" 
                                   placeholder="5XXXXXXXX"
                                   class="flex-1 px-4 py-4 bg-transparent border-none text-sm font-bold transition-all outline-none @error('phone') text-red-500 @enderror"
                                   style="text-align: left;">
                        </div>
                        @error('phone') <p class="text-red-500 text-xs mt-1 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Grid Row 2: Email & Tax Number -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Email -->
                    <div class="space-y-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Email') }}</label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email', $item->user->email) }}" 
                               placeholder="{{ __('Enter email address') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('email') border-red-500 @enderror">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Tax Number -->
                    <div class="space-y-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Tax Number') }}</label>
                        <input type="text" 
                               name="tax_number" 
                               value="{{ old('tax_number', $item->tax_number) }}" 
                               placeholder="{{ __('Enter tax number') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('tax_number') border-red-500 @enderror">
                        @error('tax_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Grid Row 3: CR Number & CR File -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- CR Number -->
                    <div class="space-y-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Commercial Record Number') }}</label>
                        <input type="text" 
                               name="commercial_record_number" 
                               value="{{ old('commercial_record_number', $item->commercial_record_number) }}" 
                               placeholder="{{ __('Enter CR number') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('commercial_record_number') border-red-500 @enderror">
                        @error('commercial_record_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- CR File Upload -->
                    <div class="space-y-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}" x-data="{ fileName: '{{ $item->commercial_record_file ? basename($item->commercial_record_file) : __('No file selected yet') }}' }">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Commercial Register') }}</label>
                        <div class="flex gap-4">
                            <div class="flex-1 bg-slate-50 dark:bg-white/5 border border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-400 truncate" x-text="fileName"></div>
                            <label class="px-6 py-4 bg-[#1A1A31] text-white rounded-2xl font-bold hover:bg-black transition-all text-sm whitespace-nowrap cursor-pointer">
                                {{ __('Upload File') }}
                                <input type="file" name="commercial_record_file" class="hidden" @change="fileName = $event.target.files[0].name">
                            </label>
                        </div>
                        @if($item->commercial_record_file)
                            <p class="text-xs text-primary font-bold mt-1">
                                <a href="{{ asset('storage/' . $item->commercial_record_file) }}" target="_blank" class="hover:underline">{{ __('View Current File') }}</a>
                            </p>
                        @endif
                        @error('commercial_record_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Modern Account Status Selection -->
                <div class="space-y-4 pt-4">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">{{ __('Account Status') }}</label>
                    <div class="relative" x-data="{ 
                        open: false, 
                        status: '{{ old('status', $item->user->status ?? 'active') }}',
                        statusLabel: '{{ old('status', $item->user->status ?? 'active') == 'active' ? __('نشط') : __('غير نشط') }}'
                    }">
                        <!-- Dropdown Button -->
                        <button type="button" 
                                @click="open = !open"
                                class="w-full flex items-center justify-between px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent hover:border-primary/30 rounded-2xl transition-all outline-none">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full shadow-sm shadow-current" :class="status === 'active' ? 'bg-green-50 text-green-500/40' : 'bg-red-500 text-red-500/40'"></div>
                                <span class="text-sm font-bold" :class="status === 'active' ? 'text-slate-800 dark:text-white' : 'text-slate-500 dark:text-slate-400'" x-text="statusLabel"></span>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Hidden Input -->
                        <input type="hidden" name="status" x-model="status">

                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute z-50 w-full mt-2 p-2 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-2xl shadow-xl">
                            
                            <button type="button" 
                                    @click="status = 'active'; statusLabel = '{{ __('نشط') }}'; open = false"
                                    class="w-full flex items-center gap-4 p-4 rounded-xl transition-all"
                                    :class="status === 'active' ? 'bg-green-50 dark:bg-green-500/5' : 'hover:bg-slate-50 dark:hover:bg-white/5'">
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-green-500/10 text-green-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col text-right">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('نشط') }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Active Account') }}</span>
                                </div>
                            </button>

                            <button type="button" 
                                    @click="status = 'inactive'; statusLabel = '{{ __('غير نشط') }}'; open = false"
                                    class="w-full flex items-center gap-4 p-4 rounded-xl transition-all mt-1"
                                    :class="status === 'inactive' ? 'bg-red-50 dark:bg-red-500/5' : 'hover:bg-slate-50 dark:hover:bg-white/5'">
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-500/10 text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col text-right">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('غير نشط') }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Deactivated Account') }}</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="grid grid-cols-2 gap-4 mt-12 pt-8 border-t border-slate-100 dark:border-white/5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <button type="submit" 
                        class="py-4 bg-[#1A1A31] text-white rounded-2xl font-black shadow-xl shadow-primary/20 hover:bg-black transition-all uppercase tracking-widest text-sm">
                    {{ __('Save Changes') }}
                </button>
                <a href="{{ route('admin.corporate-customers.index') }}" 
                   class="py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all text-center uppercase tracking-widest text-sm">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection