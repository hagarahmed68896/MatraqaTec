@extends('layouts.admin')

@section('title', __('Financial Settlements'))
@section('page_title', __('Financial Settlements'))

@section('content')
<div x-data="settlementManagement" class="space-y-8 pb-20" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-[2.5rem] font-black text-[#1A1A31] dark:text-white leading-none">{{ __('Financial Settlements') }}</h1>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @php
            $cardStats = [
                [
                    'label' => __('Total Payments'), 
                    'value' => number_format($stats['total_payments']['value'], 0), 
                    'color' => '#10B981', 
                    'icon' => 'payments', 
                    'trend' => '0.43%',
                    'comparison' => __('compared to last week')
                ],
                [
                    'label' => __('Total Settlements'), 
                    'value' => number_format($stats['total']['value'], 0), 
                    'color' => '#10B981', 
                    'icon' => 'settlements', 
                    'trend' => '0.43%',
                    'comparison' => __('compared to last week')
                ],
            ];
        @endphp

        @foreach($cardStats as $index => $stat)
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-50 dark:border-white/5 flex items-center justify-between h-56 relative overflow-hidden group hover:shadow-md transition-all">
            <!-- Sparkline Background -->
            <div class="absolute inset-y-0 left-0 w-1/2 opacity-20 group-hover:opacity-30 transition-opacity">
                <canvas id="chart-{{ $index }}" class="w-full h-full"></canvas>
            </div>

            <div class="relative z-10 flex flex-col justify-center h-full space-y-4 {{ app()->getLocale() == 'ar' ? 'mr-auto' : 'ml-auto' }} text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <p class="text-lg font-bold text-[#1A1A31] dark:text-white">{{ $stat['label'] }}</p>
                
                <div class="flex items-center gap-4">
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-4xl font-black text-[#1A1A31] dark:text-white">{{ $stat['value'] }}</h3>
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-5 h-5 opacity-40">
                    </div>

                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-green-50 text-green-500 text-[10px] font-black border border-green-100">
                        <span>{{ $stat['trend'] }}</span>
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path></svg>
                    </div>
                </div>

                <p class="text-xs font-bold text-slate-400 opacity-60">{{ $stat['comparison'] }}</p>
            </div>

            <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-300 shrink-0 relative z-10 group-hover:scale-110 transition-transform">
                 @if($stat['icon'] == 'payments')
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                 @else
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- CONTROLS CONTAINER -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-50 dark:border-white/5 shadow-sm p-4">
        <div class="flex items-center justify-between {{ app()->getLocale() == 'ar' ? 'flex-row' : 'flex-row-reverse' }}">

            <!-- Left Side: Dropdown and Download -->
            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="h-11 px-6 flex items-center gap-3 bg-[#1A1A31] text-white rounded-xl font-bold text-xs shadow-lg hover:scale-[1.02] transition-all">
                        <span>{{ __('All Financial Settlements') }}</span>
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute top-full mt-2 w-48 bg-white dark:bg-[#1A1A31] rounded-xl shadow-xl border border-slate-100 dark:border-white/10 z-[110] py-2 overflow-hidden">
                        <a href="{{ route('admin.financial-settlements.index') }}" class="block px-6 py-3 text-xs font-bold text-[#1A1A31] dark:text-white hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-colors">{{ __('All Settlements') }}</a>
                        <a href="{{ route('admin.financial-settlements.index', ['status' => 'pending']) }}" class="block px-6 py-3 text-xs font-bold text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-colors">{{ __('Pending') }}</a>
                        <a href="{{ route('admin.financial-settlements.index', ['status' => 'transferred']) }}" class="block px-6 py-3 text-xs font-bold text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-colors">{{ __('Transferred') }}</a>
                    </div>
                </div>

                <a href="{{ route('admin.financial-settlements.download', request()->query()) }}" 
                   class="h-11 px-6 flex items-center gap-3 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 text-[#1A1A31] dark:text-white rounded-xl font-bold text-xs hover:bg-slate-50 dark:hover:bg-white/10 transition-all">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>{{ __('Download') }}</span>
                </a>
            </div>

            <!-- Right Side: Search and Filter -->
            <div class="flex items-center gap-3 flex-1 max-w-2xl px-2">

                <!-- Filter Icon Button -->
                <div class="relative">
                    <button @click="showFilters = !showFilters" 
                            class="w-11 h-11 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 dark:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        <!-- Active indicator -->
                        <div x-show="hasActiveFilters" x-cloak class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-primary border-2 border-white rounded-full"></div>
                    </button>

                    <!-- Filter Dropdown Panel -->
                    <div x-show="showFilters" @click.away="showFilters = false" x-cloak 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute top-full {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} mt-2 w-80 bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-6 space-y-6 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        
                        <!-- Filter form — submits on Apply button click -->
                        <form id="filterForm" action="{{ route('admin.financial-settlements.index') }}" method="GET" class="space-y-6">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            
                            <!-- Sort By -->
                            <div class="space-y-3">
                                <h4 class="text-xs font-black text-[#1A1A31] dark:text-white opacity-60 uppercase tracking-widest">{{ __('Sort by:') }}</h4>
                                <div class="space-y-2">
                                    @foreach(['' => __('All'), 'name' => __('Name'), 'newest' => __('Newest'), 'oldest' => __('Oldest')] as $val => $label)
                                    <label class="flex items-center justify-between cursor-pointer group py-1">
                                        <span class="text-xs font-bold text-slate-500 dark:text-white transition-colors">{{ $label }}</span>
                                        <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                             :class="sortBy === '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                            <input type="radio" name="sort_by" value="{{ $val }}" x-model="sortBy" class="hidden">
                                            <template x-if="sortBy === '{{ $val }}'">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                            </template>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                            <!-- Account Type -->
                            <div class="space-y-3">
                                <h4 class="text-xs font-black text-[#1A1A31] dark:text-white opacity-60 uppercase tracking-widest">{{ __('Account Type:') }}</h4>
                                <div class="space-y-2">
                                    @foreach(['' => __('All'), 'company' => __('Company'), 'technician' => __('Technician')] as $val => $label)
                                    <label class="flex items-center justify-between cursor-pointer group py-1">
                                        <span class="text-xs font-bold text-slate-500 dark:text-white transition-colors">{{ $label }}</span>
                                        <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                             :class="accountType === '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                            <input type="radio" name="account_type" value="{{ $val }}" x-model="accountType" class="hidden">
                                            <template x-if="accountType === '{{ $val }}'">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                            </template>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                            <!-- Payment Method -->
                            <div class="space-y-3">
                                <h4 class="text-xs font-black text-[#1A1A31] dark:text-white opacity-60 uppercase tracking-widest">{{ __('Payment Method:') }}</h4>
                                <div class="space-y-2">
                                    @foreach(['' => __('All'), 'Credit Card' => __('Credit Card'), 'Apple pay' => 'Apple Pay', 'Wallet' => __('Wallet')] as $val => $label)
                                    <label class="flex items-center justify-between cursor-pointer group py-1">
                                        <span class="text-xs font-bold text-slate-500 dark:text-white transition-colors">{{ $label }}</span>
                                        <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                             :class="paymentMethod === '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                            <input type="radio" name="payment_method" value="{{ $val }}" x-model="paymentMethod" class="hidden">
                                            <template x-if="paymentMethod === '{{ $val }}'">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                            </template>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex gap-2 pt-4 border-t border-slate-50 dark:border-white/5">
                                <button type="submit" class="flex-1 py-3 bg-[#1A1A31] dark:bg-primary text-white rounded-xl font-black text-xs shadow-lg hover:scale-[1.02] transition-all">
                                    {{ __('Apply') }}
                                </button>
                                <button type="button" @click="resetFilters()" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-400 rounded-xl font-bold text-xs hover:bg-slate-200 transition-all">
                                    {{ __('Reset') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Search -->
                <div class="flex-1 relative group">
                    <form action="{{ route('admin.financial-settlements.index') }}" method="GET" id="searchForm">
                        <!-- Preserve active filters when searching -->
                        @foreach(request()->except('search', '_token', 'page') as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full h-11 {{ app()->getLocale() == 'ar' ? 'pr-12 pl-6' : 'pl-12 pr-6' }} bg-slate-50 dark:bg-white/5 border border-transparent focus:border-slate-100 dark:focus:border-white/10 rounded-xl transition-all font-bold text-xs text-[#1A1A31] dark:text-white placeholder:text-slate-300"
                               placeholder="{{ __('Search...') }}">
                        <div class="absolute {{ app()->getLocale() == 'ar' ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- BULK SELECTION BAR (appears when rows selected) -->
    <div x-show="selectedRows.length > 0" x-cloak
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4"
         class="fixed top-6 left-1/2 -translate-x-1/2 z-[200]">
        <div class="flex items-center gap-3 bg-[#1A1A31] dark:bg-slate-800 text-white rounded-2xl shadow-2xl px-5 py-3.5 border border-white/10">
            <!-- Count Badge -->
            <div class="flex items-center gap-2 border-r border-white/10 pr-4">
                <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center text-xs font-black" x-text="selectedRows.length"></div>
                <span class="text-xs font-bold opacity-70">{{ __('selected') }}</span>
            </div>

            <!-- Download Selected -->
            <button @click="downloadSelected()" 
                    class="flex items-center gap-2 px-4 h-9 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-black transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>{{ __('Download') }}</span>
            </button>

            <!-- Deselect All -->
            <button @click="selectedRows = []" class="w-9 h-9 flex items-center justify-center bg-white/5 hover:bg-white/10 rounded-xl transition-all opacity-60 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] border-b border-slate-50 dark:border-white/5">
                        <th class="py-8 px-10">
                            <input type="checkbox"
                                   @change="toggleAll($event.target.checked)"
                                   :checked="selectedRows.length === {{ $items->count() }} && {{ $items->count() }} > 0"
                                   class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary bg-transparent cursor-pointer">
                        </th>
                        <th class="py-8 px-4">#</th>
                        <th class="py-8 px-4">{{ __('Settlement Number') }}</th>
                        <th class="py-8 px-4">{{ __('Name') }}</th>
                        <th class="py-8 px-4">{{ __('Account Type') }}</th>
                        <th class="py-8 px-4">{{ __('Order Number') }}</th>
                        <th class="py-8 px-4">{{ __('Amount') }}</th>
                        <th class="py-8 px-4">{{ __('Payment Method') }}</th>
                        <th class="py-8 px-4">{{ __('Status') }}</th>
                        <th class="py-8 px-4">{{ __('Date') }}</th>
                        <th class="py-8 px-10 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                    @forelse($items as $index => $item)
                    <tr class="hover:bg-slate-50/30 dark:hover:bg-white/3 transition-all group" :class="selectedRows.includes({{ $item->id }}) ? 'bg-primary/5 dark:bg-primary/10' : ''">
                        <td class="py-8 px-10">
                            <input type="checkbox"
                                   value="{{ $item->id }}"
                                   @change="toggleRow({{ $item->id }})"
                                   :checked="selectedRows.includes({{ $item->id }})"
                                   class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary bg-transparent cursor-pointer">
                        </td>
                        <td class="py-8 px-4 text-xs font-black text-slate-400 group-hover:text-primary dark:hover:text-white transition-colors">
                            {{ ($items->currentPage() - 1) * $items->perPage() + $index + 1 }}
                        </td>
                        <td class="py-8 px-4">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-black text-[#1A1A31] dark:text-white">{{ __('Settlement - #') }}{{ $item->id }}</span>
                            </div>
                        </td>
                        <td class="py-8 px-4">
                            <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ $item->maintenanceCompany->company_name_ar ?? $item->user->name ?? '-' }}</span>
                        </td>
                        <td class="py-8 px-4">
                            <span class="px-3 py-1.5 bg-slate-50 dark:bg-white/5 rounded-lg text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                {{ $item->maintenance_company_id ? __('Company') : __('Technician') }}
                            </span>
                        </td>
                        <td class="py-8 px-4">
                            <span class="text-xs font-bold text-slate-400">{{ __('Order - #') }}{{ $item->order->order_number ?? $item->order_id }}</span>
                        </td>
                        <td class="py-8 px-4">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ number_format($item->amount, 0) }}</span>
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-3.5 h-3.5 opacity-40">
                            </div>
                        </td>
                        <td class="py-8 px-4 text-xs font-bold text-slate-500 dark:text-white">{{ __($item->payment_method ?? 'Credit Card') }}</td>
                        <td class="py-8 px-4">
                            @if($item->status == 'transferred')
                                <span class="px-4 py-1.5 rounded-xl bg-green-50 text-green-500 dark:bg-green-500/10 dark:text-green-400 text-[9px] font-black uppercase tracking-wider">{{ __('Transferred') }}</span>
                            @else
                                <span class="px-4 py-1.5 rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-500/10 dark:text-amber-400 text-[9px] font-black uppercase tracking-wider">{{ __('Pending') }}</span>
                            @endif
                        </td>
                        <td class="py-8 px-4 text-xs font-bold text-slate-400">{{ $item->created_at->format('Y/m/d') }}</td>
                        <td class="py-8 px-10">
                            <div class="flex items-center justify-center">
                                <a href="{{ route('admin.financial-settlements.show', $item->id) }}" 
                                   title="{{ __('View') }}"
                                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-primary dark:hover:text-white hover:bg-white dark:hover:bg-primary/20 hover:shadow-sm transition-all group/btn">
                                    <svg class="w-4 h-4 transition-transform group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-32">
                            <div class="flex flex-col items-center justify-center space-y-4 opacity-20">
                                <div class="w-20 h-20 rounded-[2.5rem] bg-slate-100 dark:bg-white/5 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ __('No settlements found') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FOOTER PAGINATION -->
        @if($items->hasPages() || $items->total() > 0)
        <div class="p-8 bg-slate-50/30 dark:bg-white/5 border-t border-slate-50 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400">{{ __('Rows per page:') }}</span>
                    <div class="relative" x-data="{ open: false, limit: {{ request('limit', 10) }} }">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-lg text-xs font-black text-[#1A1A31] dark:text-white shadow-sm">
                            <span x-text="limit"></span>
                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute bottom-full mb-2 left-0 w-20 bg-white dark:bg-[#1A1A31] rounded-xl shadow-xl border border-slate-100 dark:border-white/10 z-[120] py-2">
                            @foreach([10, 25, 50, 100] as $l)
                            <a href="{{ request()->fullUrlWithQuery(['limit' => $l]) }}" class="block px-4 py-2 text-xs font-black hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white {{ request('limit', 10) == $l ? 'text-primary' : 'text-slate-400' }}">{{ $l }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">
                    {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} {{ __('of') }} {{ $items->total() }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($items->onFirstPage())
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-300 cursor-not-allowed" disabled>
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                @else
                    <a href="{{ $items->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 hover:text-primary dark:hover:text-white hover:border-primary transition-all shadow-sm">
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @endif

                @foreach($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $page => $url)
                    @if($page == $items->currentPage())
                        <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1A1A31] text-white text-xs font-black shadow-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 hover:text-primary dark:hover:text-white hover:border-primary transition-all shadow-sm text-xs font-black">{{ $page }}</a>
                    @endif
                @endforeach

                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 hover:text-primary dark:hover:text-white hover:border-primary transition-all shadow-sm">
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @else
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-300 cursor-not-allowed" disabled>
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settlementManagement', () => ({
            showFilters: false,
            sortBy: '{{ request('sort_by', '') }}',
            accountType: '{{ request('account_type', '') }}',
            operationType: '{{ request('operation_type', '') }}',
            paymentMethod: '{{ request('payment_method', '') }}',
            selectedRows: [],

            get hasActiveFilters() {
                return this.sortBy !== '' || this.accountType !== '' || this.operationType !== '' || this.paymentMethod !== '';
            },

            toggleRow(id) {
                if (this.selectedRows.includes(id)) {
                    this.selectedRows = this.selectedRows.filter(r => r !== id);
                } else {
                    this.selectedRows.push(id);
                }
            },

            toggleAll(checked) {
                if (checked) {
                    this.selectedRows = [{{ $items->pluck('id')->join(', ') }}];
                } else {
                    this.selectedRows = [];
                }
            },

            downloadSelected() {
                const ids = this.selectedRows.join(',');
                const url = '{{ route('admin.financial-settlements.download') }}?ids=' + ids;
                window.location.href = url;
            },

            resetFilters() {
                this.sortBy = '';
                this.accountType = '';
                this.operationType = '';
                this.paymentMethod = '';
                window.location.href = "{{ route('admin.financial-settlements.index') }}";
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        // Sparklines
        @foreach($cardStats as $index => $stat)
        if (document.getElementById('chart-{{ $index }}')) {
            new Chart(document.getElementById('chart-{{ $index }}').getContext('2d'), {
                type: 'line',
                data: {
                    labels: Array(12).fill(''),
                    datasets: [{
                        data: [{{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}, {{ rand(20, 60) }}],
                        borderColor: '{{ $stat["color"] }}',
                        borderWidth: 3,
                        fill: true,
                        backgroundColor: (context) => {
                            const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 150);
                            gradient.addColorStop(0, '{{ $stat["color"] }}30');
                            gradient.addColorStop(1, '{{ $stat["color"] }}00');
                            return gradient;
                        },
                        tension: 0.45,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: { x: { display: false }, y: { display: false, min: 0 } }
                }
            });
        }
        @endforeach
    });
</script>
@endsection