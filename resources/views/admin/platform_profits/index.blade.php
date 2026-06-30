@extends('layouts.admin')

@section('title', __('Platform Profits Settings'))

@section('content')
<div class="max-w-3xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom duration-700" dir="rtl">
    
    <!-- Page Title -->
    <div class="flex flex-col items-center justify-center py-8 text-center space-y-2">
        <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">
            {{ __('Platform Profits Settings') }}
        </h2>
        <p class="text-sm font-bold text-slate-400 dark:text-slate-500">
            {{ __('Manage VAT and platform commission rates for services and spare parts') }}
        </p>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 rounded-2xl p-5">
        <ul class="space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-sm text-red-600 dark:text-red-400 font-bold flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Settings Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 md:p-12 shadow-2xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-white/5 relative overflow-hidden">
        
        <form action="{{ route('admin.platform-profits.store') }}" method="POST" class="space-y-8">
            @csrf
            
            {{-- Services Settings Section --}}
            <div class="space-y-6">
                <h3 class="text-md font-black text-[#1A1A31] dark:text-white pb-3 border-b border-slate-100 dark:border-white/10 text-right">
                    {{ __('Maintenance Services Settings') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Services VAT -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-right">
                            {{ __('Services VAT Rate (%)') }}
                        </label>
                        <div class="relative group flex items-center">
                            <input type="number" step="0.01" name="vat_rate_services" 
                                   value="{{ old('vat_rate_services', $stats['vat_rate_services'] ?? 15) }}" 
                                   class="w-full pl-12 pr-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl py-4 text-sm font-black text-slate-700 dark:text-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-right">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center bg-slate-100 dark:bg-white/10 border-r border-slate-100 dark:border-white/10 rounded-l-2xl">
                                <span class="text-sm font-black text-slate-500">%</span>
                            </div>
                        </div>
                    </div>
                    <!-- Services Commission -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-right">
                            {{ __('Services Platform Commission (%)') }}
                        </label>
                        <div class="relative group flex items-center">
                            <input type="number" step="0.01" name="platform_commission_services" 
                                   value="{{ old('platform_commission_services', $stats['platform_commission_services'] ?? 15) }}" 
                                   class="w-full pl-12 pr-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl py-4 text-sm font-black text-slate-700 dark:text-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-right">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center bg-slate-100 dark:bg-white/10 border-r border-slate-100 dark:border-white/10 rounded-l-2xl">
                                <span class="text-sm font-black text-slate-500">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Spare Parts Settings Section --}}
            <div class="space-y-6">
                <h3 class="text-md font-black text-[#1A1A31] dark:text-white pb-3 border-b border-slate-100 dark:border-white/10 text-right">
                    {{ __('Spare Parts Settings') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Parts VAT -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-right">
                            {{ __('Spare Parts VAT Rate (%)') }}
                        </label>
                        <div class="relative group flex items-center">
                            <input type="number" step="0.01" name="vat_rate_parts" 
                                   value="{{ old('vat_rate_parts', $stats['vat_rate_parts'] ?? 15) }}" 
                                   class="w-full pl-12 pr-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl py-4 text-sm font-black text-slate-700 dark:text-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-right">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center bg-slate-100 dark:bg-white/10 border-r border-slate-100 dark:border-white/10 rounded-l-2xl">
                                <span class="text-sm font-black text-slate-500">%</span>
                            </div>
                        </div>
                    </div>
                    <!-- Parts Commission -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-right">
                            {{ __('Spare Parts Platform Commission (%)') }}
                        </label>
                        <div class="relative group flex items-center">
                            <input type="number" step="0.01" name="platform_commission_parts" 
                                   value="{{ old('platform_commission_parts', $stats['platform_commission_parts'] ?? 15) }}" 
                                   class="w-full pl-12 pr-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl py-4 text-sm font-black text-slate-700 dark:text-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-right">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center bg-slate-100 dark:bg-white/10 border-r border-slate-100 dark:border-white/10 rounded-l-2xl">
                                <span class="text-sm font-black text-slate-500">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Platform Profit (Read Only) -->
            <div class="space-y-4">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-right">
                    {{ __('Total Platform Profit') }}
                </label>
                <div class="relative group flex items-center">
                    <input type="text" readonly value="{{ number_format($stats['total_profit'] ?? 0, 2) }}" 
                           class="w-full pl-16 pr-4 bg-slate-100 dark:bg-white/10 border border-transparent rounded-2xl py-4 text-sm font-black text-slate-400 dark:text-slate-500 cursor-not-allowed text-right">
                    <div class="absolute left-0 top-0 bottom-0 w-16 flex items-center justify-center bg-slate-100 dark:bg-white/10 border-r border-slate-100 dark:border-white/10 rounded-l-2xl">
                        <span class="text-xs font-black text-slate-400">SAR</span>
                    </div>
                </div>
                <p class="text-[10px] font-bold text-slate-400 text-right px-2">
                    * {{ __('This amount is automatically calculated from completed orders.') }}
                </p>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-start gap-4 pt-8 border-t border-slate-50 dark:border-white/5">
                <button type="submit" class="px-12 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl text-sm font-black hover:opacity-90 transition-all shadow-xl shadow-primary/20 min-w-[180px]">
                    {{ __('Save Settings') }}
                </button>
                <a href="{{ route('admin.dashboard') }}" class="px-12 py-4 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-2xl text-sm font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all min-w-[140px] text-center">
                    {{ __('Back') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection