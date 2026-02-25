@extends('layouts.admin')

@section('title', __('Platform Profit Percentage'))

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    
    <!-- Page Title -->
    <div class="flex items-center justify-center py-4">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white border-b-4 border-primary pb-2 px-6">
            {{ __('Platform Profit Percentage') }}
        </h2>
    </div>

    <!-- Settings Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-white/5 relative overflow-hidden">
        
        <form action="{{ route('admin.platform-profits.store') }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Fees -->
            <div class="space-y-4">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Fees') }}
                </label>
                <div class="relative group">
                    <div class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} inset-y-0 w-16 flex items-center justify-center bg-slate-50 dark:bg-white/5 border-{{ app()->getLocale() == 'ar' ? 'l' : 'r' }} border-slate-100 dark:border-white/10 rounded-2xl">
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-5 h-5 opacity-40 group-focus-within:opacity-100 transition-opacity">
                    </div>
                    <input type="number" step="0.01" name="fees" value="{{ old('fees', $fees) }}" placeholder="{{ __('Enter Fees') }}" class="w-full px-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl {{ app()->getLocale() == 'ar' ? 'pr-8' : 'pl-8' }} py-5 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                </div>
            </div>

            <!-- Platform Profit -->
            <div class="space-y-4 mt-6">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Platform Profit') }}
                </label>
                <div class="relative group">
                    <div class="absolute {{ app()->getLocale() == 'en' ? 'right-0' : 'left-0' }} inset-y-0 w-16 flex items-center justify-center bg-slate-50 dark:bg-white/5 border-{{ app()->getLocale() == 'en' ? 'l' : 'r' }} border-slate-100 dark:border-white/10 rounded-2xl">
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-5 h-5 opacity-40 group-focus-within:opacity-100 transition-opacity">
                    </div>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $profit) }}" placeholder="{{ __('Select platform profit') }}" class="w-full px-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl {{ app()->getLocale() == 'ar' ? 'pr-8' : 'pl-8 pr-8' }} py-5 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center {{ app()->getLocale() == 'ar' ? 'justify-start' : 'justify-end' }} gap-4 pt-8 border-t border-slate-100 dark:border-white/5 mt-8">
                <button type="submit" class="px-12 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-2xl text-sm font-black hover:opacity-90 transition-all shadow-xl shadow-primary/20 min-w-[160px]">
                    {{ __('Save') }}
                </button>
                <a href="{{ route('admin.dashboard') }}" class="px-12 py-4 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-2xl text-sm font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all min-w-[160px] text-center">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection