@extends('layouts.admin')

@section('title', __('Contracts Management'))
@section('page_title', __('Contracts Management'))

@section('content')
<div x-data="contractManagement()" class="space-y-8 pb-20" dir="rtl">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Contracts Management') }}</h1>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $cardStats = [
                ['label' => 'Total Contracts', 'value' => $stats['total_contracts'], 'color' => '#10B981', 'trend_str' => $stats['total_contracts_trend'] ?? '+0.00%', 'trend_val' => $stats['total_contracts_trend_value'] ?? 0, 'icon' => 'contract', 'prefix' => ''],
                ['label' => 'Total Companies', 'value' => $stats['total_companies'], 'color' => '#10B981', 'trend_str' => $stats['total_companies_trend'] ?? '+0.00%', 'trend_val' => $stats['total_companies_trend_value'] ?? 0, 'icon' => 'company', 'prefix' => ''],
                ['label' => 'Expired Contracts', 'value' => $stats['expired_contracts'], 'color' => '#EF4444', 'trend_str' => $stats['expired_contracts_trend'] ?? '+0.00%', 'trend_val' => $stats['expired_contracts_trend_value'] ?? 0, 'icon' => 'expired', 'prefix' => ''],
                ['label' => 'Collected Amounts', 'value' => number_format($stats['collected_amount']), 'color' => '#10B981', 'trend_str' => $stats['collected_amount_trend'] ?? '+0.00%', 'trend_val' => $stats['collected_amount_trend_value'] ?? 0, 'icon' => 'money', 'prefix' => '<img src="' . asset('assets/images/Vector (1).svg') . '" alt="SAR" class="inline-block w-4 h-4 align-middle">'],
            ];
        @endphp

        @foreach($cardStats as $index => $stat)
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm flex flex-col justify-between h-48 relative overflow-hidden group hover:shadow-md transition-all border border-transparent dark:border-white/5">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-md font-bold text-[#1A1A31] dark:text-slate-400 opacity-60">{{ __($stat['label']) }}</p>
                    <div class="flex items-center gap-3">
                        <h3 class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ $stat['value'] }} <span class="text-sm font-bold text-slate-400 dark:text-slate-500">{!! $stat['prefix'] !!}</span></h3>
                        <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-white/5 flex items-center justify-center">
                            @if($stat['icon'] == 'contract')
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            @elseif($stat['icon'] == 'company')
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            @elseif($stat['icon'] == 'expired')
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @elseif($stat['icon'] == 'money')
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ ($stat['trend_val'] ?? 0) >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} border border-current opacity-60">
                            {{ $stat['trend_str'] ?? '+0.00%' }}
                        </span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">{{ __('Compared to last week') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Sparkline Chart -->
            <div class="absolute bottom-0 left-0 right-0 h-16 opacity-30 group-hover:opacity-50 transition-opacity">
                <canvas id="chart-{{ $index }}" class="w-full h-full"></canvas>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Contracts Table Section -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] shadow-sm border border-transparent dark:border-white/5 overflow-visible">
        <!-- Controls Row -->
        <div class="p-6 border-b border-slate-50 dark:border-white/5 relative min-h-[5rem]">
            <!-- Default Controls (Show when no items selected) -->
            <div x-show="selectedRows.length === 0" class="flex flex-col md:flex-row items-center justify-between gap-6 w-full">
                
                {{-- Right Side (Filter + Search) --}}
                <div class="flex items-center gap-3 flex-1 max-w-2xl justify-start">
                    <!-- Filter Button & Dropdown -->
                    <div class="relative">
                        <button @click="showFilters = !showFilters" class="w-12 h-12 flex items-center justify-center bg-slate-50 dark:bg-white/5 rounded-xl text-slate-400 hover:text-primary dark:hover:text-white transition shadow-sm relative border border-transparent dark:border-white/10 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                             <!-- Red Dot Indicator -->
                             @if(request('status') || request('sort_by'))
                             <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-[#1A1A31]"></div>
                             @endif
                        </button>

                        <!-- Filter Dropdown Panel -->
                        <div x-show="showFilters" 
                             @click.away="showFilters = false" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="scale-95 translate-y-2" 
                             x-transition:enter-end="scale-100 translate-y-0"
                             class="absolute top-full right-0 mt-3 w-72 bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-6 space-y-6 text-right">
                            
                            <form id="filterForm" action="{{ route('admin.contracts.index') }}" method="GET" class="space-y-6">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="tab" value="{{ request('tab') }}">
                                
                                <!-- Sort By -->
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white text-right block">{{ __('Sort By:') }}</label>
                                    <div class="space-y-3">
                                        @foreach(['' => __('All'), 'name' => __('Name'), 'newest' => __('Newest'), 'oldest' => __('Oldest')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-xs font-bold text-slate-500 dark:text-slate-400 transition-colors">{{ $label }}</span>
                                             <input type="radio" name="sort_by" value="{{ $val }}" x-model="sortBy" 
                                                    class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-100 dark:bg-white/5"></div>

                                <!-- Status -->
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white text-right block">{{ __('Status:') }}</label>
                                    <div class="space-y-3">
                                        @php
                                            $statuses = [
                                                '' => __('All'),
                                                'pending' => __('Pending'),
                                                'offer_sent' => __('Offer Sent'),
                                                'offer_accepted' => __('Offer Accepted'),
                                                'offer_rejected' => __('Offer Rejected'),
                                                'awaiting_signed_contract' => __('Awaiting Signed Contract'),
                                                'awaiting_payment_proof' => __('Awaiting Payment Proof'),
                                                'active' => __('Active'),
                                                'completed' => __('Completed'),
                                                'expired' => __('Expired')
                                            ];
                                        @endphp
                                        @foreach($statuses as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                                 <span class="text-xs font-bold text-slate-500 dark:text-slate-400 transition-colors">{{ $label }}</span>
                                                 <input type="radio" name="status" value="{{ $val }}" x-model="status" 
                                                        class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-white/10">
                                    <a href="{{ route('admin.contracts.index') }}" class="flex-1 py-3 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-xl font-bold text-xs text-center hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                                        {{ __('Reset') }}
                                    </a>
                                    <button type="submit" class="flex-[2] p-3 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-xl font-black text-xs shadow-lg hover:scale-[1.02] transition-all">
                                        {{ __('Apply') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative md:w-80 flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="{{ __('Search...') }}" 
                               onkeydown="if(event.key === 'Enter') { window.location.href = '{{ route('admin.contracts.index') }}?search=' + this.value }"
                               class="w-full pr-12 pl-4 py-3 bg-[#FBFBFF] dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- Left Side (Download) --}}
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('admin.contracts.download') }}" class="flex items-center gap-2 px-5 py-3 border border-slate-200 dark:border-white/10 text-[#1A1A31] dark:text-white text-sm font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        {{ __('Download') }}
                    </a>
                </div>
            </div>

            <!-- Bulk Actions Bar (Visible when rows selected) -->
            <div x-show="selectedRows.length > 0" x-cloak class="absolute inset-0 bg-white dark:bg-[#1A1A31] z-20 flex items-center justify-between px-6 border-b border-slate-50 dark:border-white/5 rounded-t-[2.5rem]">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-slate-500 bg-slate-100 dark:bg-white/5 dark:text-slate-300 px-4 py-2.5 rounded-xl">
                        <span x-text="selectedRows.length"></span> {{ __('Selected') }}
                    </span>
                    
                    <button @click="bulkDownload()"
                            class="px-6 py-3 bg-[#1A1A31] dark:bg-white dark:text-[#1A1A31] text-white rounded-xl text-xs font-black hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg shadow-black/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        {{ __('Download Selected') }}
                    </button>

                    <button @click="confirmBulkDelete()"
                            class="px-6 py-3 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-500/20 rounded-xl text-xs font-black hover:bg-red-100 dark:hover:bg-red-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Delete Selected') }}
                    </button>
                </div>
                <button @click="selectedRows = []" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-[12px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-50 dark:border-white/5">
                        <th class="py-6 px-8 flex items-center gap-4 min-w-[80px]">
                            <div class="relative w-5 h-5 border-2 border-slate-200 dark:border-white/10 rounded-lg flex items-center justify-center cursor-pointer"
                                 :class="selectAll ? 'bg-primary border-primary' : ''"
                                 @click="toggleSelectAll()">
                                <svg x-show="selectAll" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span>#</span>
                        </th>
                        <th class="py-6 px-4">{{ __('Contract Number') }}</th>
                        <th class="py-6 px-4">{{ __('Maintenance Company') }}</th>
                        <th class="py-6 px-4">{{ __('Contract') }}</th>
                        <th class="py-6 px-4">{{ __('Project Value') }}</th>
                        <th class="py-6 px-4">{{ __('Paid Amount') }}</th>
                        <th class="py-6 px-4">{{ __('Remaining') }}</th>
                        <th class="py-6 px-4">{{ __('Contact') }}</th>
                        <th class="py-6 px-4 text-center">{{ __('Receipts') }}</th>
                        <th class="py-6 px-4">{{ __('Status') }}</th>
                        <th class="py-6 px-4">{{ __('Date') }}</th>
                        <th class="py-6 px-8 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-white/5">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50 dark:hover:bg-white/10 transition-all group">
                        <td class="py-6 px-8 flex items-center gap-4" onclick="event.stopPropagation()">
                             <div class="relative w-5 h-5 border-2 border-slate-200 dark:border-white/10 rounded-lg flex items-center justify-center cursor-pointer"
                                  :class="selectedRows.includes('{{ $item->id }}') ? 'bg-primary border-primary' : ''"
                                  @click="toggleRow('{{ $item->id }}')">
                                 <svg x-show="selectedRows.includes('{{ $item->id }}')" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                             </div>
                            <span class="text-xs font-mono opacity-40 dark:text-white">{{ $loop->iteration }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[#1A1A31] dark:text-white font-black text-md">{{ $item->contract_number }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-3">
                                <span class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->maintenanceCompany->company_name_ar ?? $item->maintenanceCompany->name ?? '-' }}</span>
                            </div>
                        </td>

                         <td class="py-6 px-4">
                             <a href="{{ route('admin.contracts.show', $item->id) }}" onclick="event.stopPropagation()" class="text-primary dark:text-white hover:text-primary dark:hover:text-white-dark font-black text-md decoration-slice decoration-2 underline-offset-4 hover:underline transition-all">
                                 {{ __('The Contract') }}
                             </a>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-1">
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-4 h-4 align-middle">
                                <span class="text-md font-black text-[#1A1A31] dark:text-white">{{ $item->project_value !== null ? number_format($item->project_value) : '-' }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-1">
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-4 h-4 align-middle">
                                <span class="text-md font-black text-[#1A1A31] dark:text-white">{{ number_format($item->paid_amount) }}</span>
                            </div>
                        </td>
                         <td class="py-6 px-4">
                            <div class="flex items-center gap-1">
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-4 h-4 align-middle">
                                <span class="text-md font-black text-[#1A1A31] dark:text-white">{{ $item->project_value !== null ? number_format($item->remaining_amount) : '-' }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                             <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $item->contact_numbers ?: ($item->contact_number ?? '-') }}</span>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <span class="px-3 py-1 bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white rounded-lg text-xs font-black">{{ $item->paymentReceipts->count() }}</span>
                        </td>
                        <td class="py-6 px-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10',
                                    'offer_sent' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10',
                                    'offer_accepted' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
                                    'offer_rejected' => 'bg-red-50 text-red-600 dark:bg-red-500/10',
                                    'awaiting_signed_contract' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10',
                                    'awaiting_payment_proof' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/10',
                                    'active' => 'bg-green-50 text-green-600 dark:bg-green-500/10',
                                    'completed' => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10',
                                    'expired' => 'bg-red-50 text-red-600 dark:bg-red-500/10',
                                ];
                                $colorClass = $statusColors[$item->status] ?? 'bg-slate-50 text-slate-600 dark:bg-white/5';
                            @endphp
                            <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $colorClass }}">
                                {{ __($item->status) }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500">{{ $item->created_at->format('Y/m/d') }}</span>
                        </td>
                        <td class="py-6 px-8 text-center" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.contracts.show', $item->id) }}" 
                                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-slate-50 dark:bg-white/5 text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-white/20 hover:text-[#1A1A31] dark:hover:text-white transition-all"
                                   title="{{ __('View') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('admin.contracts.edit', $item->id) }}" 
                                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-slate-50 dark:bg-white/5 text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-white/20 hover:text-[#1A1A31] dark:hover:text-white transition-all"
                                   title="{{ __('Edit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <button type="button" @click="confirmDelete('{{ $item->id }}')" 
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 dark:bg-red-500/10 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all"
                                        title="{{ __('Delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-20 text-center text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest bg-white dark:bg-[#1A1A31]">
                            {{ __('No contracts found currently') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="px-10 py-6 border-t border-slate-50 dark:border-white/5 bg-white dark:bg-[#1A1A31] rounded-b-[2.5rem] shadow-sm">
         {{ $items->appends(request()->query())->links('vendor.pagination.custom-admin') }}
    </div>

    <!-- Hidden Delete Forms -->
    <form id="single-delete-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form id="bulk-delete-form" method="POST" action="{{ route('admin.contracts.bulk-destroy') }}" class="hidden">
        @csrf
        <input type="hidden" name="ids" id="bulk-delete-ids">
    </form>

    <!-- Delete Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showDeleteModal" 
             class="fixed inset-0 z-[1000] flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto"
             x-cloak>
            
            <!-- Backdrop -->
            <div x-show="showDeleteModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showDeleteModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content -->
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white dark:bg-[#1A1A31] w-full max-w-md rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden transform transition-all">
                
                <div class="p-10 text-center">
                    <div class="mx-auto w-24 h-24 bg-rose-50 dark:bg-rose-500/10 rounded-full flex items-center justify-center mb-8 relative">
                        <div class="absolute inset-0 rounded-full bg-rose-500/10 animate-ping"></div>
                        <svg class="w-10 h-10 text-rose-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-4">{{ __('Confirm Deletion') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold leading-relaxed mb-10">
                        <span x-show="isBulkDelete">{{ __('Are you sure you want to delete the selected contracts?') }}</span>
                        <span x-show="!isBulkDelete">{{ __('Are you sure you want to delete this contract?') }}</span>
                    </p>
                    
                    <div class="flex gap-4">
                        <button type="button" @click="showDeleteModal = false" 
                                class="flex-1 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all uppercase tracking-widest text-md">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="executeDelete()" 
                                class="flex-1 py-4 bg-rose-500 text-white rounded-2xl font-black hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/20 uppercase tracking-widest text-md">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('contractManagement', () => ({
            showFilters: false,
            // Initialize filter state from request
            sortBy: '{{ request('sort_by', '') }}',
            status: '{{ request('status', '') }}',
            // Initialize selectedRows from request old input if needed, or empty
            selectedRows: [],
            selectAll: false,
            showDeleteModal: false,
            isBulkDelete: false,
            deleteTargetId: null,
            
            toggleSelectAll() {
                this.selectAll = !this.selectAll;
                const allIds = [{{ $items->pluck('id')->implode(',') }}];
                
                if (this.selectAll) {
                    this.selectedRows = allIds.map(String);
                } else {
                    this.selectedRows = [];
                }
            },

            toggleRow(id) {
                // Ensure ID is string for comparison
                id = String(id);
                if (this.selectedRows.includes(id)) {
                    this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
                } else {
                    this.selectedRows.push(id);
                }
                
                // Update selectAll status
                const allIds = [{{ $items->pluck('id')->implode(',') }}];
                this.selectAll = allIds.length > 0 && this.selectedRows.length === allIds.length;
            },

            bulkDownload() {
                if (this.selectedRows.length === 0) return;
                const ids = this.selectedRows.join(',');
                // Redirect to download with IDs
                window.location.href = "{{ route('admin.contracts.download') }}?ids=" + ids;
            },

            confirmDelete(id) {
                this.deleteTargetId = id;
                this.isBulkDelete = false;
                this.showDeleteModal = true;
            },

            confirmBulkDelete() {
                if (this.selectedRows.length === 0) return;
                this.isBulkDelete = true;
                this.showDeleteModal = true;
            },

            executeDelete() {
                if (this.isBulkDelete) {
                    const form = document.getElementById('bulk-delete-form');
                    document.getElementById('bulk-delete-ids').value = this.selectedRows.join(',');
                    form.submit();
                } else if (this.deleteTargetId) {
                    const form = document.getElementById('single-delete-form');
                    form.action = "{{ url('admin/contracts') }}/" + this.deleteTargetId;
                    form.submit();
                }
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        @foreach($cardStats as $index => $stat)
        new Chart(document.getElementById('chart-{{ $index }}').getContext('2d'), {
            type: 'line',
            data: {
                labels: Array(10).fill(''),
                datasets: [{
                    data: {!! json_encode(array_map(function() { return rand(10, 50); }, range(1, 10))) !!},
                    borderColor: '{{ $stat["color"] }}',
                    borderWidth: 3,
                    fill: {
                        target: 'origin',
                        above: '{{ $stat["color"] }}10'
                    },
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: { 
                    x: { display: false }, 
                    y: { display: false, min: 0 } 
                },
                elements: {
                    line: {
                        tension: 0.4
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        });
        @endforeach
    });
</script>
@endsection