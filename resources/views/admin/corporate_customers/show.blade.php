@extends('layouts.admin')

@section('title', __('Corporate Profile') . ' - ' . $item->user->name)

@section('content')
<div x-data="{ 
    activeTab: 'overview',
    orderSearch: '',
    orderSubTab: 'all',
    invoiceSearch: '',
    paymentSearch: '',
    contractSearch: '',
    chartType: '{{ $chartType }}',
    performanceData: @js($performanceData),
    chart: null,
    updateChart(type) {
        this.chartType = type;
        fetch(`{{ route('admin.corporate-customers.show', $item->id) }}?chart_type=${type}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            this.performanceData = data.performanceData;
            this.renderChart();
        });
    },
    renderChart() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        if (this.chart) this.chart.destroy();
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.5)'); // Indigo-600
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.performanceData.map(d => d.label || d.month),
                datasets: [{
                    label: '{{ __('Orders') }}',
                    data: this.performanceData.map(d => d.count),
                    borderColor: '#4F46E5',
                    backgroundColor: gradient,
                    borderWidth: 4,
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4F46E5',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#4F46E5',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleFont: { size: 13, weight: '900', family: 'Inter' },
                        bodyFont: { size: 12, family: 'Inter' },
                        padding: 16,
                        cornerRadius: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.parsed.y + ' ' + '{{ __('Orders') }}';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { 
                            drawBorder: false,
                            color: 'rgba(226, 232, 240, 0.3)',
                            borderDash: [8, 4] 
                        },
                        ticks: { 
                            font: { weight: 'bold', size: 11 },
                            padding: 10,
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { 
                            font: { weight: 'bold', size: 11 },
                            padding: 10
                        }
                    }
                }
            }
        });
    }
}" x-init="renderChart" class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.corporate-customers.index') }}" class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-primary transition-all shadow-sm">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ $item->user->name }}</h2>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center p-1 bg-slate-100 dark:bg-white/5 rounded-2xl w-fit overflow-x-auto no-scrollbar max-w-full">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-md font-bold whitespace-nowrap transition-all">{{ __('Overview') }}</button>
        <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-md font-bold whitespace-nowrap transition-all">{{ __('Orders') }}</button>
        <button @click="activeTab = 'invoices'" :class="activeTab === 'invoices' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-md font-bold whitespace-nowrap transition-all">{{ __('Invoices') }}</button>
        <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-md font-bold whitespace-nowrap transition-all">{{ __('Payments') }}</button>
        <button @click="activeTab = 'contracts'" :class="activeTab === 'contracts' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-md font-bold whitespace-nowrap transition-all">{{ __('Contracts') }}</button>
        <button @click="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-md font-bold whitespace-nowrap transition-all">{{ __('Reviews') }}</button>
    </div>

    <!-- Tab Content -->
    <div class="space-y-8">
        
        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar Info -->
            <div class="space-y-8">
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-[#1A1A31] dark:bg-indigo-600/5"></div>
                    
                    <div class="relative mt-8">
                        <div class="w-24 h-24 rounded-[2rem] bg-slate-100 dark:bg-white/10 mx-auto overflow-hidden border-4 border-white dark:border-[#1A1A31] shadow-xl flex items-center justify-center text-3xl font-black text-indigo-600 uppercase">
                            {{ mb_substr($item->user->name, 0, 1) }}
                        </div>
                        <div class="mt-6 text-center">
                            <h3 class="text-xl font-black text-slate-800 dark:text-white">
                                @if(app()->getLocale() == 'ar')
                                    {{ $item->user->name }}
                                @else
                                    {{ $item->user->name }}
                                @endif
                            </h3>                        </div>

                        <div class="mt-8 space-y-5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <div class="flex items-center gap-4 group">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Commercial Record') }}</p>
                                    <p class="text-md font-black text-slate-700 dark:text-white/50">{{ $item->commercial_record_number ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 group">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Email') }}</p>
                                    <p class="text-md font-black text-slate-700 dark:text-white/50 truncate">{{ $item->user->email }}</p>
                                </div>
                            </div>

                            <!-- Status Row -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Status') }}:</p>
                                    <span class="px-2 py-0.5 rounded-lg text-md font-black uppercase tracking-wider {{ $item->user->status == 'active' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                        {{ __($item->user->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Account Type -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Account Type') }}:</p>
                                    <p class="text-md font-black text-slate-700 dark:text-white/50 truncate">{{ __('Corporate Customer') }}</p>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Address') }}:</p>
                                    <p class="text-md font-black text-slate-700 dark:text-white/50 truncate">{{ $item->address ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Join Date -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Date') }}:</p>
                                    <p class="text-md font-black text-slate-700 dark:text-white/50 truncate">{{ $item->created_at->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col gap-3">
                     <a href="{{ route('admin.corporate-customers.edit', $item->id) }}" 
   class="w-full py-4 bg-[#1A1A31] dark:bg-indigo-600 text-white rounded-2xl font-black text-xs shadow-lg shadow-indigo-200/20 hover:scale-[1.02] transition-all flex items-center justify-center gap-2 border-2 border-gray-200 dark:border-gray-200">
    {{ __('Edit Profile') }}
</a>
                            <form action="{{ route('admin.corporate-customers.toggle-block', $item->id) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full py-4 rounded-2xl font-black text-xs transition-all border-2 {{ $item->user->status == 'active' ? 'border-rose-500 text-rose-500 hover:bg-rose-500 hover:text-white' : 'border-emerald-500 text-emerald-500 hover:bg-emerald-500 hover:text-white' }}">
                                    {{ $item->user->status == 'active' ? __('Block User') : __('Unblock User') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Mini Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Expenditure') }}</span>
                            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-black text-[10px]">{{ __('SAR') }}</div>
                        </div>
                        <h3 class="text-2xl font-black text-slate-700 dark:text-white">{{ number_format($stats['total_payments'], 2) }}</h3>
                    </div>

                    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Active Orders') }}</span>
                            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center font-black text-[10px]">#</div>
                        </div>
                        <h3 class="text-2xl font-black text-slate-700 dark:text-white">{{ $stats['order_count'] }}</h3>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Corporate Activity') }}</h3>
                        <div class="flex bg-slate-100 dark:bg-white/5 p-1 rounded-xl">
                            <button @click="updateChart('weekly')" :class="chartType === 'weekly' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-primary dark:text-white' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all">{{ __('Weekly') }}</button>
                            <button @click="updateChart('monthly')" :class="chartType === 'monthly' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-primary dark:text-white' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all">{{ __('Monthly') }}</button>
                            <button @click="updateChart('yearly')" :class="chartType === 'yearly' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-primary dark:text-white' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all">{{ __('Yearly') }}</button>
                        </div>
                    </div>
                    <div class="h-[300px] w-full relative">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Company Files -->
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white mb-6">{{ __('Corporate Documents') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($item->commercial_record_file)
                        <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <span class="text-xs font-black text-slate-700 dark:text-white/80">{{ __('Commercial Record') }}</span>
                            </div>
                            <a href="{{ asset('storage/'.$item->commercial_record_file) }}" target="_blank" class="text-[10px] font-black text-indigo-600 hover:underline">{{ __('View') }}</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Tab -->
        <div x-show="activeTab === 'orders'" x-transition class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Orders') }}</h3>
                
                <!-- Sub-tabs for filtering -->
                <div class="flex items-center p-1 bg-slate-100 dark:bg-white/5 rounded-2xl w-fit overflow-x-auto no-scrollbar">
                    <button @click="orderSubTab = 'all'" :class="orderSubTab === 'all' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('All') }}</button>
                    <button @click="orderSubTab = 'pending'" :class="orderSubTab === 'pending' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('New') }}</button>
                    <button @click="orderSubTab = 'scheduled'" :class="orderSubTab === 'scheduled' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('Scheduled') }}</button>
                    <button @click="orderSubTab = 'in_progress'" :class="orderSubTab === 'in_progress' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('In Progress') }}</button>
                    <button @click="orderSubTab = 'completed'" :class="orderSubTab === 'completed' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('Completed') }}</button>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm space-y-6">
                <!-- Search Bar -->
                <div class="flex justify-end">
                    <div class="relative w-full md:w-80 border border-slate-200 dark:border-white/5 rounded-xl">
                        <span class="absolute inset-y-0 left-{{ app()->getLocale() == 'ar' ? 'auto right' : '0' }}-0 {{ app()->getLocale() == 'ar' ? 'pr' : 'pl' }}-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input x-model="orderSearch" type="text" placeholder="{{ __('Search') }}..." class="w-full h-12 {{ app()->getLocale() == 'ar' ? 'pr-12 pl-4' : 'pl-12 pr-4' }} rounded-xl border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <thead>
                            <tr class="text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                                <th class="pb-4 px-2">#</th>
                                <th class="pb-4 px-2">{{ __('Order #') }}</th>
                                <th class="pb-4 px-2">{{ __('Service Name') }}</th>
                                <th class="pb-4 px-2">{{ __('Service Type') }}</th>
                                <th class="pb-4 px-2">{{ __('Address') }}</th>
                                <th class="pb-4 px-2">{{ __('Tech Name') }}</th>
                                <th class="pb-4 px-2">{{ __('Tech Type') }}</th>
                                <th class="pb-4 px-2">{{ __('Price') }}</th>
                                <th class="pb-4 px-2">{{ __('Status') }}</th>
                                <th class="pb-4 px-2">{{ __('Date/Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @php
                                $statusMap = [
                                    'pending' => 'pending',
                                    'scheduled' => 'scheduled',
                                    'in_progress' => 'in_progress',
                                    'completed' => 'completed',
                                    'cancelled' => 'cancelled'
                                ];
                            @endphp
                            @foreach($orders as $index => $order)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all"
                                x-show="(orderSubTab === 'all' || orderSubTab === '{{ $order->status }}') && 
                                       ('{{ $order->order_number }}'.includes(orderSearch) || 
                                        '{{ optional($order->service)->{'name_'.app()->getLocale()} }}'.toLowerCase().includes(orderSearch.toLowerCase()) ||
                                        '{{ optional($order->technician)->user->name }}'.toLowerCase().includes(orderSearch.toLowerCase()))">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white">{{ __('Order') }} - #{{ $order->order_number }}</td>
                                <td class="py-5 px-2 text-slate-600 dark:text-slate-300 font-black">{{ $order->service->{'name'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400">{{ $order->service->category->{'name'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400 truncate max-w-[150px]">{{ $order->address ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-[10px] text-primary font-black">
                                            {{ mb_substr($order->technician->user->name ?? '-', 0, 1) }}
                                        </div>
                                        <span class="text-slate-800 dark:text-white">{{ $order->technician->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $order->technician && $order->technician->maintenance_company_id ? 'bg-indigo-500/10 text-indigo-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ $order->technician && $order->technician->maintenance_company_id ? __('Company') : __('Platform') }}
                                    </span>
                                </td>
                                <td class="py-5 px-2">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-slate-800 dark:text-white">{{ number_format($order->total_price, 2) }}</span>
                                        <span class="text-[9px] text-slate-400">{{ __('SAR') }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider
                                        @if($order->status == 'completed') bg-emerald-500/10 text-emerald-500
                                        @elseif($order->status == 'cancelled') bg-rose-500/10 text-rose-500
                                        @elseif($order->status == 'scheduled') bg-blue-500/10 text-blue-500
                                        @else bg-slate-100 dark:bg-white/10 text-slate-400 @endif">
                                        {{ __($order->status) }}
                                    </span>
                                </td>
                                <td class="py-5 px-2 text-slate-400 whitespace-nowrap" dir="ltr">{{ $order->created_at->format('d/m/Y - H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination Mock -->
                <div class="flex items-center justify-between pt-6 border-t border-slate-50 dark:border-white/5">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Showing') }} {{ count($orders) }} {{ __('from') }} {{ count($orders) }}</p>
                    <div class="flex items-center gap-2">
                        <button class="w-8 h-8 rounded-lg border border-slate-100 dark:border-white/5 flex items-center justify-center text-slate-400 hover:bg-primary hover:text-white transition-all">
                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <div class="px-3 py-1.5 rounded-lg bg-primary/5 text-primary text-[10px] font-black">1 / 1</div>
                        <button class="w-8 h-8 rounded-lg border border-slate-100 dark:border-white/5 flex items-center justify-center text-slate-400 hover:bg-primary hover:text-white transition-all">
                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Tab -->
        <div x-show="activeTab === 'invoices'" x-transition class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Invoice Records') }}</h3>
                <div class="relative w-full md:w-80 border border-slate-200 dark:border-white/5 rounded-xl">
                    <span class="absolute inset-y-0 left-{{ app()->getLocale() == 'ar' ? 'auto right' : '0' }}-0 {{ app()->getLocale() == 'ar' ? 'pr' : 'pl' }}-4 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input x-model="invoiceSearch" type="text" placeholder="{{ __('Search') }}..." class="w-full h-12 {{ app()->getLocale() == 'ar' ? 'pr-12 pl-4' : 'pl-12 pr-4' }} rounded-xl border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>

            <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <thead>
                            <tr class="text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                                <th class="pb-4 px-2">#</th>
                                <th class="pb-4 px-2">{{ __('Invoice #') }}</th>
                                <th class="pb-4 px-2">{{ __('Service Name') }}</th>
                                <th class="pb-4 px-2">{{ __('Service Type') }}</th>
                                <th class="pb-4 px-2">{{ __('Address') }}</th>
                                <th class="pb-4 px-2">{{ __('Tech Name') }}</th>
                                <th class="pb-4 px-2">{{ __('Tech Type') }}</th>
                                <th class="pb-4 px-2">{{ __('Amount') }}</th>
                                <th class="pb-4 px-2">{{ __('Date') }}</th>
                                <th class="pb-4 px-2">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @forelse($invoices as $index => $invoice)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all"
                                x-show="'{{ $invoice->invoice_number }}'.includes(invoiceSearch)">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">#{{ $invoice->invoice_number }}</td>
                                <td class="py-5 px-2 text-slate-600 dark:text-slate-300 font-black">{{ $invoice->order->service->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400">{{ $invoice->order->service->category->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400 truncate max-w-[150px]">{{ $invoice->order->address ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-[10px] text-primary font-black">
                                            {{ mb_substr($invoice->order->technician->user->name ?? '-', 0, 1) }}
                                        </div>
                                        <span class="text-slate-800 dark:text-white">{{ $invoice->order->technician->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $invoice->order->technician && $invoice->order->technician->maintenance_company_id ? 'bg-indigo-500/10 text-indigo-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ $invoice->order->technician && $invoice->order->technician->maintenance_company_id ? __('Company') : __('Platform') }}
                                    </span>
                                </td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ number_format($invoice->amount, 2) }} {{ __('SAR') }}</td>
                                <td class="py-5 px-2 text-slate-400">{{ $invoice->created_at->format('d/m/Y') }}</td>
                                <td class="py-5 px-2">
                                    <a href="{{ route('admin.invoices.download', $invoice->id) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 font-black text-[10px] hover:bg-indigo-600 hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        {{ __('Download') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="py-12 text-center text-slate-400">{{ __('No invoices found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payments Tab -->
        <div x-show="activeTab === 'payments'" x-transition class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Company Payments') }}</h3>
                <div class="relative w-full md:w-80 border border-slate-200 dark:border-white/5 rounded-xl">
                    <span class="absolute inset-y-0 left-{{ app()->getLocale() == 'ar' ? 'auto right' : '0' }}-0 {{ app()->getLocale() == 'ar' ? 'pr' : 'pl' }}-4 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input x-model="paymentSearch" type="text" placeholder="{{ __('Search') }}..." class="w-full h-12 {{ app()->getLocale() == 'ar' ? 'pr-12 pl-4' : 'pl-12 pr-4' }} rounded-xl border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>

            <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <thead>
                            <tr class="text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                                <th class="pb-4 px-2">#</th>
                                <th class="pb-4 px-2">{{ __('Reference / Method') }}</th>
                                <th class="pb-4 px-2">{{ __('Service Name') }}</th>
                                <th class="pb-4 px-2">{{ __('Service Type') }}</th>
                                <th class="pb-4 px-2">{{ __('Address') }}</th>
                                <th class="pb-4 px-2">{{ __('Tech Name') }}</th>
                                <th class="pb-4 px-2">{{ __('Tech Type') }}</th>
                                <th class="pb-4 px-2">{{ __('Amount') }}</th>
                                <th class="pb-4 px-2">{{ __('Status') }}</th>
                                <th class="pb-4 px-2">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @forelse($payments as $index => $payment)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all"
                                x-show="'{{ $payment->payment_method ?? '' }}'.toLowerCase().includes(paymentSearch.toLowerCase())">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 font-black text-slate-800 dark:text-white">{{ $payment->payment_method ?? __('Electronic') }}</td>
                                <td class="py-5 px-2 text-slate-600 dark:text-slate-300 font-black">{{ $payment->order->service->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400">{{ $payment->order->service->category->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400 truncate max-w-[150px]">{{ $payment->order->address ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-[10px] text-primary font-black">
                                            {{ mb_substr($payment->order->technician->user->name ?? '-', 0, 1) }}
                                        </div>
                                        <span class="text-slate-800 dark:text-white">{{ $payment->order->technician->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $payment->order->technician && $payment->order->technician->maintenance_company_id ? 'bg-indigo-500/10 text-indigo-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ $payment->order->technician && $payment->order->technician->maintenance_company_id ? __('Company') : __('Platform') }}
                                    </span>
                                </td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ number_format($payment->amount, 2) }} {{ __('SAR') }}</td>
                                <td class="py-5 px-2">
                                    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider {{ $payment->status == 'paid' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ __($payment->status) }}
                                    </span>
                                </td>
                                <td class="py-5 px-2 text-slate-400">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="py-12 text-center text-slate-400">{{ __('No payments found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Contracts Tab -->
        <div x-show="activeTab === 'contracts'" x-transition class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Contracts') }}</h3>
                <div class="relative w-full md:w-80 border border-slate-200 dark:border-white/5 rounded-xl">
                    <span class="absolute inset-y-0 left-{{ app()->getLocale() == 'ar' ? 'auto right' : '0' }}-0 {{ app()->getLocale() == 'ar' ? 'pr' : 'pl' }}-4 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input x-model="contractSearch" type="text" placeholder="{{ __('Search') }}..." class="w-full h-12 {{ app()->getLocale() == 'ar' ? 'pr-12 pl-4' : 'pl-12 pr-4' }} rounded-xl border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>

            <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <thead>
                            <tr class="text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                                <th class="pb-4 px-2">#</th>
                                <th class="pb-4 px-2">{{ __('Contract #') }}</th>
                                <th class="pb-4 px-2">{{ __('Contract') }}</th>
                                <th class="pb-4 px-2">{{ __('Project Value') }}</th>
                                <th class="pb-4 px-2">{{ __('Paid Amount') }}</th>
                                <th class="pb-4 px-2">{{ __('Remaining Amount') }}</th>
                                <th class="pb-4 px-2">{{ __('Contact Numbers') }}</th>
                                <th class="pb-4 px-2">{{ __('Payment Receipts') }}</th>
                                <th class="pb-4 px-2">{{ __('Status') }}</th>
                                <th class="pb-4 px-2">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @forelse($contracts as $index => $contract)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all"
                                x-show="'{{ $contract->contract_number ?? '' }}'.includes(contractSearch)">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">#{{ $contract->contract_number ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    @if($contract->contract_file)
                                    <a href="{{ asset('storage/' . $contract->contract_file) }}" target="_blank" class="text-primary hover:underline">
                                        {{ __('View') }}
                                    </a>
                                    @else
                                    <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ number_format($contract->project_value, 2) }} {{ __('SAR') }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ number_format($contract->paid_amount, 2) }} {{ __('SAR') }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ number_format($contract->remaining_amount, 2) }} {{ __('SAR') }}</td>
                                <td class="py-5 px-2 text-slate-400">{{ $contract->contact_numbers ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400">{{ $contract->payment_receipts_count ?? 0 }}</td>
                                <td class="py-5 px-2">
                                    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider {{ $contract->status == 'active' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ __($contract->status) }}
                                    </span>
                                </td>
                                <td class="py-5 px-2 text-slate-400">{{ $contract->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="py-12 text-center text-slate-400">{{ __('No contracts found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reviews Tab -->
        <div x-show="activeTab === 'reviews'" x-transition class="space-y-6">
            <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Customer Feedbacks') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($reviews as $review)
                <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-black">
                                {{ mb_substr($review->technician->user->name ?? '-', 0, 1) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 dark:text-white">{{ $review->technician->user->name ?? __('Technician') }}</h4>
                                <p class="text-[10px] text-slate-400 font-bold">{{ $review->order->order_number ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            @for($i=1; $i<=5; $i++)
                            <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endfor
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 italic">"{{ $review->comment ?? __('No comment provided.') }}"</p>
                    <div class="pt-4 border-t border-slate-50 dark:border-white/5 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400">{{ $review->created_at->format('d/m/Y') }}</span>
                        <span class="text-[10px] font-black text-primary uppercase">{{ $review->service->user->name ?? '-' }}</span>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-20 bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-dashed border-slate-200 dark:border-white/10 flex flex-col items-center justify-center text-slate-400">
                    <svg class="w-12 h-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    <p class="text-xs font-bold">{{ __('No ratings available yet') }}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
