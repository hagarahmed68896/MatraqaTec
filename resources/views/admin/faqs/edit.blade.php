@extends('layouts.admin')

@section('title', __('Edit FAQ') . ' - ' . __('MatraqaTec'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.faqs.index') }}" class="w-12 h-12 bg-white dark:bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 hover:text-primary dark:hover:text-white hover:bg-primary/5 transition-all shadow-sm">
                <svg class="w-6 h-6 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Edit FAQ') }}</h1>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-100 dark:border-white/5">
        <form action="{{ route('admin.faqs.update', $item->id) }}" method="POST" class="space-y-12">
            @csrf
            @method('PUT')
            
            <!-- Section: Question Data -->
            <div class="space-y-8">
                <h3 class="text-lg font-black text-slate-800 dark:text-white flex items-center gap-3">
                    <span class="w-2 h-8 bg-primary rounded-full"></span>
                    {{ __('Question Data') }}
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Question AR -->
                    <div class="space-y-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300">
                            {{ __('Question (Arabic)') }}
                        </label>
                        <input type="text" name="question_ar" value="{{ old('question_ar', $item->question_ar) }}" placeholder="{{ __('Enter question') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        @error('question_ar') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Question EN -->
                    <div class="space-y-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300">
                            {{ __('Question (English)') }}
                        </label>
                        <input type="text" name="question_en" value="{{ old('question_en', $item->question_en) }}" placeholder="{{ __('Enter question') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        @error('question_en') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Answer AR -->
                    <div class="space-y-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300">
                            {{ __('Answer (Arabic)') }}
                        </label>
                        <textarea name="answer_ar" rows="4" placeholder="{{ __('Enter answer') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">{{ old('answer_ar', $item->answer_ar) }}</textarea>
                        @error('answer_ar') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Answer EN -->
                    <div class="space-y-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300">
                            {{ __('Answer (English)') }}
                        </label>
                        <textarea name="answer_en" rows="4" placeholder="{{ __('Enter answer') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">{{ old('answer_en', $item->answer_en) }}</textarea>
                        @error('answer_en') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Lower Sections: Target Group & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Target Group Dropdown -->
                <div class="space-y-4" x-data="{ open: false, selected: '{{ old('target_group', $item->target_group) }}' }">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-primary/40 rounded-full"></span>
                        {{ __('Target Group') }}
                    </label>
                    <div class="relative">
                        <button @click="open = !open" type="button" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-5 text-sm font-bold text-slate-700 dark:text-white flex items-center justify-between hover:bg-slate-100 dark:hover:bg-white/10 transition-all group">
                            <span x-text="selected == 'all' ? '{{ __('All Groups') }}' : (selected == 'clients' ? '{{ __('Clients') }}' : (selected == 'companies' ? '{{ __('Companies') }}' : '{{ __('Technicians') }}'))"></span>
                            <svg class="w-5 h-5 text-slate-400 transition-transform group-hover:text-primary dark:hover:text-white" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <input type="hidden" name="target_group" x-model="selected">
                        
                        <!-- Dropdown Panel -->
                        <div x-show="open" 
                             @click.away="open = false" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="absolute z-50 w-full mt-2 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-[2rem] shadow-2xl p-3 space-y-1">
                            @foreach(['all' => 'All Groups', 'clients' => 'Clients', 'companies' => 'Companies', 'technicians' => 'Technicians'] as $val => $label)
                            <label class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white cursor-pointer transition-all group">
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

                <!-- Status Dropdown -->
                <div class="space-y-4" x-data="{ open: false, selected: '{{ old('status', $item->status) }}' }">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-emerald-500/40 rounded-full"></span>
                        {{ __('Status') }}
                    </label>
                    <div class="relative">
                        <button @click="open = !open" type="button" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-5 text-sm font-bold text-slate-700 dark:text-white flex items-center justify-between hover:bg-slate-100 dark:hover:bg-white/10 transition-all group">
                            <span x-text="selected == 'active' ? '{{ __('Active') }}' : '{{ __('Inactive') }}'"></span>
                            <svg class="w-5 h-5 text-slate-400 transition-transform group-hover:text-emerald-500" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <input type="hidden" name="status" x-model="selected">
                        
                        <!-- Dropdown Panel -->
                        <div x-show="open" 
                             @click.away="open = false" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="absolute z-50 w-full mt-2 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-[2rem] shadow-2xl p-3 space-y-1">
                            @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                            <label class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white cursor-pointer transition-all group">
                                <span class="text-sm font-bold text-slate-600 dark:text-slate-300 group-hover:text-emerald-500 transition-colors">{{ __($label) }}</span>
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

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-10 border-t border-slate-100 dark:border-white/5">
                <a href="{{ route('admin.faqs.index') }}" class="px-12 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl text-sm font-black hover:bg-slate-200 transition-all text-center">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-12 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-2xl text-sm font-black hover:opacity-90 transition-all shadow-xl shadow-primary/20">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection