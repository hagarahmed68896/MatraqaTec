@extends('layouts.admin')

@section('title', __('Platform Settings') . ' - ' . __('MatraqaTec'))

@section('content')
<div x-data="{ 
    tab: 'platform',
    lang: '{{ old('default_language', $items['default_language'] ?? 'ar') }}',
    mode: '{{ old('system_mode', $items['system_mode'] ?? 'light') }}',
    reminder: '{{ old('reminder_type', $items['reminder_type'] ?? 'day') }}'
}" class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    


    <!-- Page Title -->
    <div class="flex items-center justify-center py-4">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Platform Settings') }}</h1>
    </div>

    <!-- Settings Card -->
    <div class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm max-w-4xl mx-auto">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-10">
            @csrf
            
            <!-- Default Language -->
            <div class="space-y-4">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Default Language for Platform') }}
                </label>
                <div class="flex items-center bg-slate-50 dark:bg-white/5 p-1.5 rounded-2xl gap-2">
                    <input type="hidden" name="default_language" :value="lang">
                    <button type="button" @click="lang = 'en'" :class="lang == 'en' ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-400'" class="flex-1 py-3 px-6 rounded-xl text-sm font-black transition-all">
                        {{ __('English Language') }}
                    </button>
                    <button type="button" @click="lang = 'ar'" :class="lang == 'ar' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-lg' : 'text-slate-400'" class="flex-1 py-3 px-6 rounded-xl text-sm font-black transition-all">
                        {{ __('Arabic Language') }}
                    </button>
                </div>
            </div>

            <!-- System Mode -->
            <div class="space-y-4 mt-6">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('System Mode') }}
                </label>
                <div class="flex items-center bg-slate-50 dark:bg-white/5 p-1.5 rounded-2xl gap-2">
                    <input type="hidden" name="system_mode" :value="mode">
                    <button type="button" @click="mode = 'auto'" :class="mode == 'auto' ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-400'" class="flex-1 py-3 px-4 rounded-xl text-sm font-black transition-all whitespace-nowrap">
                        {{ __('Automatic (Based on Device)') }}
                    </button>
                    <button type="button" @click="mode = 'dark'" :class="mode == 'dark' ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-400'" class="flex-1 py-3 px-4 rounded-xl text-sm font-black transition-all">
                        {{ __('Night Mode') }}
                    </button>
                    <button type="button" @click="mode = 'light'" :class="mode == 'light' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-lg' : 'text-slate-400'" class="flex-1 py-3 px-4 rounded-xl text-sm font-black transition-all">
                        {{ __('Day Mode') }}
                    </button>
                </div>
            </div>

            <!-- Order Acceptance Duration -->
            <div class="space-y-4 mt-6">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Order Acceptance Duration') }}
                </label>
                <div class="relative group">
                    <input type="number" name="order_acceptance_duration" value="{{ old('order_acceptance_duration', $items['order_acceptance_duration'] ?? '') }}" placeholder="{{ __('Order Acceptance Duration') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <div class="absolute {{ app()->getLocale() == 'ar' ? 'left-6 border-r pr-6' : 'right-6 border-l pl-6' }} top-1/2 -translate-y-1/2 border-slate-200 dark:border-white/10 text-slate-400 font-bold text-sm">
                        {{ __('Minute') }}
                    </div>
                </div>
                <p class="text-xs text-slate-400 font-bold text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Specify the time allowed for the technician to accept the order (in minutes)') }}
                </p>
            </div>

            <!-- Required Photos Before -->
            <div class="space-y-4 mt-6">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Required Photos Before Work') }}
                </label>
                <input type="number" name="required_photos_before_count" value="{{ old('required_photos_before_count', $items['required_photos_before_count'] ?? '') }}" placeholder="{{ __('Enter number of photos required before work') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
            </div>

            <!-- Required Photos After -->
            <div class="space-y-4 mt-6">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Required Photos After Work') }}
                </label>
                <input type="number" name="required_photos_after_count" value="{{ old('required_photos_after_count', $items['required_photos_after_count'] ?? '') }}" placeholder="{{ __('Enter number of photos required after finishing work') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
            </div>

            <!-- Appointment Reminder -->
            <div class="space-y-4 mt-6">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Appointment Reminder') }}
                </label>
                <div class="space-y-4 bg-slate-50 dark:bg-white/5 p-6 rounded-[2rem] border border-slate-100 dark:border-white/5">
                    <div class="flex items-center justify-between p-4 bg-white dark:bg-[#1A1A31] rounded-2xl border border-slate-100 dark:border-white/10 shadow-sm cursor-pointer" @click="reminder = 'day'">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all" :class="reminder == 'day' ? 'border-primary' : 'border-slate-200 dark:border-white/10'">
                                <div x-show="reminder == 'day'" class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>
                            <span class="text-sm font-black text-slate-700 dark:text-white">{{ __('Before Day') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-white dark:bg-[#1A1A31] rounded-2xl border border-slate-100 dark:border-white/10 shadow-sm cursor-pointer" @click="reminder = 'hour'">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all" :class="reminder == 'hour' ? 'border-primary' : 'border-slate-200 dark:border-white/10'">
                                <div x-show="reminder == 'hour'" class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>
                            <span class="text-sm font-black text-slate-700 dark:text-white">{{ __('Before Hour') }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-white dark:bg-[#1A1A31] rounded-2xl border border-slate-100 dark:border-white/10 shadow-sm cursor-pointer" @click="reminder = 'custom'">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all" :class="reminder == 'custom' ? 'border-primary' : 'border-slate-200 dark:border-white/10'">
                                    <div x-show="reminder == 'custom'" class="w-3 h-3 rounded-full bg-primary"></div>
                                </div>
                                <span class="text-sm font-black text-slate-700 dark:text-white">{{ __('Other') }}</span>
                            </div>
                        </div>
                        <div x-show="reminder == 'custom'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="px-4">
                            <div class="relative group">
                                <input type="number" name="reminder_custom_value" value="{{ old('reminder_custom_value', $items['reminder_custom_value'] ?? '') }}" placeholder="{{ __('Select reminder time before appointment') }}" class="w-full bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                                <div class="absolute {{ app()->getLocale() == 'ar' ? 'left-6 border-r pr-6' : 'right-6 border-l pl-6' }} top-1/2 -translate-y-1/2 border-slate-200 dark:border-white/10 text-slate-400 font-bold text-sm">
                                    {{ __('Minute') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="reminder_type" :value="reminder">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center {{ app()->getLocale() == 'ar' ? 'justify-start' : 'justify-end' }} gap-4 pt-6 border-t border-slate-100 dark:border-white/5">
                <button type="submit" class="px-8 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-2xl text-sm font-black hover:opacity-90 transition-all shadow-lg shadow-indigo-500/10 min-w-[140px]">
                    {{ __('Save') }}
                </button>
                <button type="reset" class="px-8 py-4 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-2xl text-sm font-black hover:bg-slate-200 transition-all min-w-[140px]">
                    {{ __('Cancel') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection