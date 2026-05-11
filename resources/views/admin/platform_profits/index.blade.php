@extends('layouts.admin')

@section('title', __('Platform Profits'))

@section('content')
<div class="max-w-2xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    
    <!-- Page Title -->
    <div class="flex flex-col items-center justify-center py-8 text-center space-y-2">
        <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">
            {{ __('Platform Profits Settings') }}
        </h2>
        <p class="text-sm font-bold text-slate-400 dark:text-slate-500">
            {{ __('Manage commission rates and view total earnings') }}
        </p>
    </div>

    <!-- Settings Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 md:p-12 shadow-2xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-white/5 relative overflow-hidden">
        
        <form action="{{ route('admin.platform-profits.store') }}" method="POST" class="space-y-10">
            @csrf
            
            <!-- Fees (Commission Percentage) -->
            <div class="space-y-4">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Commission Percentage (%)') }}
                </label>
                <div class="relative group">
                    <div class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} inset-y-0 w-16 flex items-center justify-center bg-slate-50 dark:bg-white/5 border-{{ app()->getLocale() == 'ar' ? 'l' : 'r' }} border-slate-100 dark:border-white/10 rounded-2xl">
                        <span class="text-lg font-black text-primary">%</span>
                    </div>
                    <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate', $stats['commission_rate']) }}" 
                           placeholder="{{ __('Enter commission percentage') }}" 
                           class="w-full px-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl {{ app()->getLocale() == 'ar' ? 'pr-8' : 'pl-8' }} py-6 text-base font-black text-slate-700 dark:text-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                </div>
            </div>

            <!-- Total Platform Profit (Read Only) -->
            <div class="space-y-4">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Total Platform Profit') }}
                </label>
                <div class="relative group">
                    <div class="absolute {{ app()->getLocale() == 'en' ? 'right-0' : 'left-0' }} inset-y-0 w-16 flex items-center justify-center bg-slate-100 dark:bg-white/10 border-{{ app()->getLocale() == 'en' ? 'l' : 'r' }} border-slate-100 dark:border-white/10 rounded-2xl">
                        <span class="text-xs font-black text-slate-400">SAR</span>
                    </div>
                    <input type="text" readonly value="{{ number_format($stats['total_profit'], 2) }}" 
                           class="w-full px-4 bg-slate-100 dark:bg-white/10 border border-transparent rounded-2xl {{ app()->getLocale() == 'ar' ? 'pr-8' : 'pl-8 pr-8' }} py-6 text-base font-black text-slate-400 dark:text-slate-500 cursor-not-allowed text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                </div>
                <p class="text-[10px] font-bold text-slate-400 px-2">
                    * {{ __('This amount is automatically calculated from completed orders.') }}
                </p>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center {{ app()->getLocale() == 'ar' ? 'justify-start' : 'justify-end' }} gap-4 pt-8 border-t border-slate-50 dark:border-white/5">
                <button type="submit" class="px-12 py-5 bg-[#1A1A31] dark:bg-primary text-white rounded-2xl text-sm font-black hover:opacity-90 transition-all shadow-xl shadow-primary/20 min-w-[180px]">
                    {{ __('Save Settings') }}
                </button>
                <a href="{{ route('admin.dashboard') }}" class="px-12 py-5 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-2xl text-sm font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all min-w-[140px] text-center">
                    {{ __('Back') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection