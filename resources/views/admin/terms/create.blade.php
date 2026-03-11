@extends('layouts.admin')

@section('title', __('Add New Term') . ' - ' . __('MatraqaTec'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.terms.index') }}" class="w-12 h-12 bg-white dark:bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 hover:text-primary dark:hover:text-white hover:bg-primary/5 transition-all shadow-sm">
                <svg class="w-6 h-6 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Add New Term') }}</h1>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-100 dark:border-white/5">
        <form action="{{ route('admin.terms.store') }}" method="POST" class="space-y-10">
            @csrf
            
            <div class="space-y-8">
                <h3 class="text-lg font-black text-slate-800 dark:text-white flex items-center gap-3">
                    <span class="w-2 h-8 bg-primary rounded-full"></span>
                    {{ __('Term Data') }}
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Title AR -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Title (Arabic)') }}
                        </label>
                        <input type="text" name="title_ar" value="{{ old('title_ar') }}" placeholder="{{ __('Enter Title') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        @error('title_ar') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Title EN -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Title (English)') }}
                        </label>
                        <input type="text" name="title_en" value="{{ old('title_en') }}" placeholder="{{ __('Enter Title') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        @error('title_en') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Content AR -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Content (Arabic)') }}
                        </label>
                        <textarea name="content_ar" rows="4" placeholder="{{ __('Enter Content') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">{{ old('content_ar') }}</textarea>
                        @error('content_ar') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Content EN -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Content (English)') }}
                        </label>
                        <textarea name="content_en" rows="4" placeholder="{{ __('Enter Content') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">{{ old('content_en') }}</textarea>
                        @error('content_en') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Target Group Picker -->
                    <div class="space-y-3" x-data="{ open: false, selected: '{{ old('target_group', 'all') }}' }">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Target Audience') }}
                        </label>
                        <div class="relative">
                            <button @click="open = !open" type="button" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white flex items-center justify-between">
                                <span x-text="selected == 'all' ? '{{ __('All') }}' : (selected == 'clients' ? '{{ __('Clients') }}' : (selected == 'companies' ? '{{ __('Companies') }}' : '{{ __('Technicians') }}'))"></span>
                                <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <input type="hidden" name="target_group" x-model="selected">
                            
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute z-10 w-full mt-2 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-[1.5rem] shadow-xl p-2 space-y-1">
                                @foreach(['all' => 'All', 'clients' => 'Clients', 'companies' => 'Companies', 'technicians' => 'Technicians'] as $val => $label)
                                <label class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white cursor-pointer transition-all group">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300 transition-colors">{{ __($label) }}</span>
                                    <div class="relative flex items-center">
                                        <input type="radio" value="{{ $val }}" x-model="selected" @change="open = false" class="peer appearance-none w-6 h-6 rounded-full border-2 border-slate-200 dark:border-white/10 checked:border-primary checked:bg-primary transition-all cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white absolute inset-0 m-auto pointer-events-none opacity-0 peer-checked:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @error('target_group') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Status Picker -->
                    <div class="space-y-3" x-data="{ open: false, selected: '{{ old('status', 'active') }}' }">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Status') }}
                        </label>
                        <div class="relative">
                            <button @click="open = !open" type="button" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white flex items-center justify-between">
                                <span x-text="selected == 'active' ? '{{ __('Active') }}' : '{{ __('Inactive') }}'"></span>
                                <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <input type="hidden" name="status" x-model="selected">
                            
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute z-10 w-full mt-2 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-[1.5rem] shadow-xl p-2 space-y-1">
                                @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                                <label class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white cursor-pointer transition-all group">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300 transition-colors">{{ __($label) }}</span>
                                    <div class="relative flex items-center">
                                        <input type="radio" value="{{ $val }}" x-model="selected" @change="open = false" class="peer appearance-none w-6 h-6 rounded-full border-2 border-slate-200 dark:border-white/10 checked:border-emerald-500 checked:bg-emerald-500 transition-all cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white absolute inset-0 m-auto pointer-events-none opacity-0 peer-checked:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @error('status') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-4">
                <button type="reset" class="px-12 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl text-sm font-black hover:bg-slate-200 transition-all">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="px-12 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-2xl text-sm font-black hover:opacity-90 transition-all shadow-xl shadow-primary/20">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection