@extends('layouts.admin')

@section('title', __('Corporate Profile') . ' - ' . $company->user->name)

@section('content')
<div x-data="{ 
    activeTab: 'overview',
    orderSearch: '',
    orderSubTab: 'all',
    invoiceSearch: '',
    paymentSearch: '',
    settlementSearch: '',
    reviewSearch: '',
    chartType: '{{ $chartType }}',
    performanceData: @js($performanceData),
    chart: null,
    updateChart(type) {
        this.chartType = type;
        fetch(`{{ route('admin.maintenance-companies.show', $company->id) }}?chart_type=${type}`, {
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
            <a href="{{ route('admin.maintenance-companies.index') }}" class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-primary dark:hover:text-white transition-all shadow-sm">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ $company->user->name }}</h2>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center p-1 w-full bg-slate-100 dark:bg-white/5 rounded-2xl overflow-x-auto no-scrollbar">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all text-center">{{ __('Overview') }}</button>
        <button @click="activeTab = 'technicians'" :class="activeTab === 'technicians' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all text-center">{{ __('Technicians') }}</button>
        <button @click="activeTab = 'services'" :class="activeTab === 'services' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all text-center">{{ __('Services') }}</button>
        <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all text-center">{{ __('Orders') }}</button>
        <button @click="activeTab = 'settlements'" :class="activeTab === 'settlements' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all text-center">{{ __('Financial Settlements') }}</button>
    </div>

    <!-- Tab Content -->
    <div class="space-y-8">
        
        <!-- Overview Tab - wrapper div -->
        <div x-show="activeTab === 'overview'" x-transition class="space-y-6">

            <!-- Row 1: Main 3-col grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Sidebar Info (col-1, appears RIGHT in RTL) -->
                <div class="space-y-8">
                    <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm text-center relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-24 bg-[#1A1A31] dark:bg-indigo-600/5"></div>
                        
                        <div class="relative mt-8">
                            <div class="w-24 h-24 rounded-[2rem] bg-slate-100 dark:bg-white/10 mx-auto overflow-hidden border-4 border-white dark:border-[#1A1A31] shadow-xl flex items-center justify-center text-3xl font-black text-indigo-600 uppercase">
                                {{ mb_substr($company->user->name, 0, 1) }}
                            </div>
                            <div class="mt-6 text-center">
                                <h3 class="text-xl font-black text-slate-800 dark:text-white">
                                    @if(app()->getLocale() == 'ar')
                                        {{ $company->user->name }}
                                    @else
                                        {{ $company->user->name }}
                                    @endif
                                </h3>                        </div>

                            <div class="mt-8 space-y-5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                                <div class="flex items-center gap-4 group">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Commercial Record') }}</p>
                                        <p class="text-md font-black text-slate-700 dark:text-white/50">{{ $company->commercial_record_number ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 group">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Email') }}</p>
                                        <p class="text-md font-black text-slate-700 dark:text-white/50 truncate">{{ $company->user->email }}</p>
                                    </div>
                                </div>

                                <!-- Status Row -->
                                <div class="flex items-center gap-4 group mb-1">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0 flex items-center gap-1">
                                        <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Status') }}:</p>
                                        <span class="px-2 py-0.5 rounded-lg text-md font-black uppercase tracking-wider {{ $company->user->status == 'active' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                            {{ __($company->user->status) }}
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
                                        <p class="text-md font-black text-slate-700 dark:text-white/50 truncate">{{ __('Maintenance Company') }}</p>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="flex items-center gap-4 group mb-1">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0 flex items-center gap-1">
                                        <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Address') }}:</p>
                                        <p class="text-md font-black text-slate-700 dark:text-white/50 truncate">{{ $company->address ?? '-' }}</p>
                                    </div>
                                </div>

                                <!-- Join Date -->
                                <div class="flex items-center gap-4 group mb-1">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0 flex items-center gap-1">
                                        <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Date') }}:</p>
                                        <p class="text-md font-black text-slate-700 dark:text-white/50 truncate">{{ $company->created_at->format('Y-m-d') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 flex flex-col gap-3">
                                <!-- Company Working Hours Button -->
                                <button type="button" x-data @click="$dispatch('open-working-hours')" class="w-full py-4 bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-slate-700 dark:text-white rounded-2xl font-black text-xs transition-all flex items-center justify-center gap-2 border-2 border-transparent">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ __('Company Working Hours') }}
                                </button>
                                
                                <a href="{{ route('admin.maintenance-companies.edit', $company->id) }}" class="w-full py-4 bg-[#1A1A31] dark:bg-indigo-600 text-white rounded-2xl font-black text-xs shadow-lg shadow-indigo-200/20 hover:scale-[1.02] transition-all flex items-center justify-center gap-2 border-2 border-gray-200 dark:border-gray-200">{{ __('Edit Profile') }}</a>
                                <form action="{{ route('admin.maintenance-companies.toggle-block', $company->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-4 rounded-2xl font-black text-xs transition-all border-2 {{ $company->user->status == 'active' ? 'border-rose-500 text-rose-500 hover:bg-rose-500 hover:text-white' : 'border-emerald-500 text-emerald-500 ' }}">
                                        {{ $company->user->status == 'active' ? __('Block User') : __('Unblock User') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>


                <!-- Overview Content (col-span-2, appears LEFT in RTL) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Mini Stats (2x2 grid) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Technicians') }}</span>
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
                            </div>
                            <h3 class="text-2xl font-black text-slate-700 dark:text-white">{{ $stats['total_technicians'] }}</h3>
                        </div>
                        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Services') }}</span>
                                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
                            </div>
                            <h3 class="text-2xl font-black text-slate-700 dark:text-white">{{ collect($company_services)->count() }}</h3>
                        </div>
                        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Revenue') }}</span>
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-4 h-4"></div>
                            </div>
                            <h3 class="text-2xl font-black text-slate-700 dark:text-white">{{ number_format($stats['total_revenue'], 2) }}</h3>
                        </div>
                        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Orders Count') }}</span>
                                <div class="w-8 h-8 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg></div>
                            </div>
                            <h3 class="text-2xl font-black text-slate-700 dark:text-white">{{ $stats['total_orders'] }}</h3>
                        </div>
                    </div>

                    <!-- Company Activity Chart -->
                    <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Company Activity') }}</h3>
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
                </div>
            </div>

            <!-- Row 2: Documents (full-width) -->
            <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Documents') }}</h4>
                        <p class="text-[10px] font-bold text-slate-400">{{ __('Business Registration') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Commercial Record Number --}}
                    <div class="flex items-center gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Commercial Record') }}</p>
                            <p class="text-sm font-black text-slate-700 dark:text-white font-mono">{{ $company->commercial_record_number ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- Commercial Record File --}}
                    <div class="flex items-center gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Commercial Register File') }}</p>
                            @if($company->commercial_record_file)
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-emerald-500 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ __('File uploaded') }}
                                    </span>
                                    <a href="{{ Storage::url($company->commercial_record_file) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-indigo-700 dark:text-white rounded-xl text-[10px] font-black transition-all hover:scale-[1.03]">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        {{ __('Open File') }}
                                    </a>
                                    @php $ext = strtolower(pathinfo($company->commercial_record_file, PATHINFO_EXTENSION)); @endphp
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase {{ in_array($ext, ['pdf']) ? 'bg-rose-500/10 text-rose-500' : 'bg-blue-500/10 text-blue-500' }}">
                                        {{ strtoupper($ext) }}
                                    </span>
                                </div>
                            @else
                                <span class="text-xs font-bold text-slate-400">{{ __('No file uploaded') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Bank Account Details (full-width) -->
            <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Bank Account Details') }}</h4>
                        <p class="text-[10px] font-bold text-slate-400">{{ __('Payment and settlement information') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    @foreach([
                        ['label' => __('Bank Name'), 'value' => $company->bank_name],
                        ['label' => __('Account Name'), 'value' => $company->account_name],
                        ['label' => __('Account Number'), 'value' => $company->account_number],
                        ['label' => 'IBAN', 'value' => $company->iban],
                        ['label' => 'SWIFT', 'value' => $company->swift_code],
                        ['label' => __('Bank Address'), 'value' => $company->bank_address],
                    ] as $field)
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ $field['label'] }}</p>
                        <p class="text-sm font-black text-slate-700 dark:text-white font-mono">{{ $field['value'] ?? '-' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>{{-- end overview --}}


        <!-- Technicians Tab -->
        <div x-show="activeTab === 'technicians'" x-transition class="space-y-6" x-data="{ techView: 'list', techSearch: '' }">

            <!-- Header: title + view toggle + search -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center justify-between w-full gap-3">
                   <!-- Search -->
                <div class="relative">
    <input x-model="techSearch" type="text" 
        class="w-80 {{ app()->getLocale() == 'ar' ? 'pr-10 pl-4' : 'pl-10 pr-4' }} py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border-2 border-gray-300 dark:border-white/10 text-xs font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
    
    <div class="absolute {{ app()->getLocale() == 'ar' ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 pointer-events-none">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </div>
</div> 
                <!-- View Toggle -->
                    <div class="flex items-center p-1 bg-slate-100 dark:bg-white/5 rounded-xl gap-1">
                        <button @click="techView = 'list'"
                            :class="techView === 'list' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-800 dark:text-white' : 'text-slate-400'"
                            class="w-9 h-9 rounded-lg flex items-center justify-center transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                        </button>
                        <button @click="techView = 'grid'"
                            :class="techView === 'grid' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-800 dark:text-white' : 'text-slate-400'"
                            class="w-9 h-9 rounded-lg flex items-center justify-center transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </button>
                    </div>
                 
                </div>
            </div>

            {{-- ═══ LIST VIEW ═══ --}}
            <div x-show="techView === 'list'" x-transition>
                <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <thead>
                                <tr class="text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                                    <th class="pb-4 pt-6 px-5">#</th>
                                    <th class="pb-4 pt-6 px-5">{{ __('Technician') }}</th>
                                    <th class="pb-4 pt-6 px-5">{{ __('Primary Service') }}</th>
                                    <th class="pb-4 pt-6 px-5">{{ __('Service Type') }}</th>
                                    <th class="pb-4 pt-6 px-5">{{ __('Orders Count') }}</th>
                                    <th class="pb-4 pt-6 px-5">{{ __('Rating') }}</th>
                                    <th class="pb-4 pt-6 px-5">{{ __('Availability') }}</th>
                                    <th class="pb-4 pt-6 px-5">{{ __('Account Status') }}</th>
                                    <th class="pb-4 pt-6 px-5">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs font-bold divide-y divide-slate-50 dark:divide-white/5">
                                @forelse($technicians as $index => $tech)
                                @php $avgRating = $tech->reviews()->avg('rating') ?? 0; @endphp
                                <tr x-show="techSearch === '' || '{{ strtolower($tech->user->name) }}'.includes(techSearch.toLowerCase())"
                                    class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all">
                                    <td class="py-4 px-5 text-slate-400">{{ $index + 1 }}</td>
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            @if($tech->image)
                                                <img src="{{ asset($tech->image) }}" class="w-9 h-9 rounded-xl object-cover shrink-0">
                                            @else
                                                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 font-black text-sm shrink-0">{{ mb_substr($tech->user->name, 0, 1) }}</div>
                                            @endif
                                            <span class="text-slate-700 dark:text-white font-black">{{ $tech->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 text-slate-500 dark:text-white">{{ optional($tech->category)->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                    <td class="py-4 px-5 text-slate-500 dark:text-white">{{ optional($tech->service)->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                    <td class="py-4 px-5 text-slate-500 dark:text-white">{{ $tech->orders_count ?? 0 }}</td>
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-1">
                                            @for($s = 1; $s <= 5; $s++)
                                            <svg class="w-3 h-3 {{ $s <= round($avgRating) ? 'text-amber-400' : 'text-slate-200 dark:text-white/10' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            @endfor
                                        </div>
                                    </td>
                                    <td class="py-4 px-5">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ ($tech->availability_status ?? 'available') === 'available' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                            {{ ($tech->availability_status ?? 'available') === 'available' ? __('Available') : __('Unavailable') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $tech->user->status == 'active' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                            {{ __($tech->user->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-slate-400">{{ $tech->created_at->format('Y/m/d') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="py-14 text-center text-slate-400">{{ __('No technicians found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══ GRID / CARD VIEW ═══ --}}
            <div x-show="techView === 'grid'" x-transition>
                @if($technicians->isEmpty())
                <div class="py-20 bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-dashed border-slate-200 dark:border-white/10 flex flex-col items-center text-slate-400">
                    <svg class="w-10 h-10 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <p class="text-sm font-bold">{{ __('No technicians found') }}</p>
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($technicians as $tech)
                    @php $avgRating = round($tech->reviews()->avg('rating') ?? 0, 1); @endphp
                    <div x-show="techSearch === '' || '{{ strtolower($tech->user->name) }}'.includes(techSearch.toLowerCase())"
                         class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm p-5 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">

                        {{-- Top row: avatar + name + availability --}}
                        <div class="flex items-center gap-3 mb-5">
                            @if($tech->image)
                                <img src="{{ asset($tech->image) }}" class="w-14 h-14 rounded-2xl object-cover shrink-0">
                            @else
                                <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 font-black text-xl shrink-0">{{ mb_substr($tech->user->name, 0, 1) }}</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <h4 class="font-black text-slate-800 dark:text-white text-sm truncate">{{ $tech->user->name }}</h4>
                                    <span class="shrink-0 px-2 py-0.5 rounded-lg text-[9px] font-black {{ ($tech->availability_status ?? 'available') === 'available' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                        {{ ($tech->availability_status ?? 'available') === 'available' ? __('Available') : __('Unavailable') }}
                                    </span>
                                </div>
                                <p class="text-[11px] font-bold text-slate-400 truncate">{{ __('Specialist in') }} {{ optional($tech->service)->{'name_'.app()->getLocale()} ?? __('General') }}</p>
                            </div>
                        </div>

                        {{-- Details list --}}
                        <div class="space-y-2.5 mb-5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <span class="text-[11px] font-bold"><span class="text-slate-400">{{ __('Service Type') }}:</span> {{ optional($tech->category)->{'name_'.app()->getLocale()} ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-[11px] font-bold"><span class="text-slate-400">{{ __('Areas') }}:</span> {{ is_array($tech->districts) && count($tech->districts) ? __('All Areas') : '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <span class="text-[11px] font-bold"><span class="text-slate-400">{{ __('Orders Count') }}:</span> {{ $tech->orders_count ?? 0 }} {{ __('Orders') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <span class="text-[11px] font-bold text-slate-500 dark:text-white"><span class="text-slate-400">{{ __('Avg Rating') }}:</span> {{ number_format($avgRating, 1) }}</span>
                            </div>
                        </div>

                        {{-- View Details Button --}}
                        @if(Route::has('admin.technicians.show'))
                        <a href="{{ route('admin.technicians.show', $tech->id) }}"
                           class="w-full py-3 flex items-center justify-center font-black text-xs rounded-2xl bg-[#1A1A31] dark:bg-white/10 text-white hover:bg-indigo-600 transition-all">
                            {{ __('View Details') }}
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

        <!-- Services Tab -->
        <div x-show="activeTab === 'services'" x-transition class="space-y-6">
            <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Services') }}</h3>

            @if($company_services->isEmpty())
            <div class="py-20 bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-dashed border-slate-200 dark:border-white/10 flex flex-col items-center justify-center text-slate-400">
                <svg class="w-12 h-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="text-sm font-bold">{{ __('No services assigned') }}</p>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($company_services as $service)
                @php
                    $techCount = $technicians->where('service_id', $service?->id)->count();
                    $orderCount = $orders->filter(fn($o) => optional($o->service)->id == $service?->id)->count();
                    $subServicesCount = $service ? \App\Models\Service::where('parent_id', $service->id)->count() : 0;
                @endphp
                @if($service)
                <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    {{-- Service Image --}}
                    <div class="relative h-44 bg-slate-100 dark:bg-white/5 overflow-hidden">
                        @if($service->image)
                            <img src="{{ asset($service->image) }}" alt="{{ $service->{'name_'.app()->getLocale()} }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-white/10">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Service Info --}}
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-4">
                            {{-- Price Badge --}}
                            <span class="flex items-center gap-1.5 px-3 py-1.5 bg-[#1A1A31] dark:bg-white/10 text-white dark:text-white rounded-2xl text-xs font-black">
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-3.5 h-3.5 inline-block opacity-80">
                                {{ $service->price ? number_format($service->price, 0) : '0' }}
                            </span>

                            {{-- Service Name --}}
                            <h4 class="text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} flex-1 px-3">
                                {{ $service->{'name_'.app()->getLocale()} }}
                            </h4>

                            {{-- Icon --}}
                            @if($service->icon)
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0">
                                <img src="{{ asset($service->icon) }}" alt="" class="w-8 h-8 object-contain">
                            </div>
                            @endif
                        </div>

                        {{-- Stats Row --}}
                        <div class="flex items-center justify-between pt-4 border-t border-slate-50 dark:border-white/5">
                            <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <span class="text-[10px] font-black">{{ $subServicesCount }} {{ __('Services') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-[10px] font-black">{{ $techCount }} {{ __('Tech') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <span class="text-[10px] font-black">{{ $orderCount }} {{ __('Orders') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
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
                                        '{{ optional(optional($order->technician)->user)->name }}'.toLowerCase().includes(orderSearch.toLowerCase()))">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white">{{ __('Order') }} - #{{ $order->order_number }}</td>
                                <td class="py-5 px-2 text-slate-600 dark:text-slate-300 font-black">{{ optional($order->service)->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400">{{ optional(optional($order->service)->category)->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400 truncate max-w-[150px]">{{ $order->address ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-[10px] text-primary font-black">
                                            {{ mb_substr(optional(optional($order->technician)->user)->name ?? '-', 0, 1) }}
                                        </div>
                                        <span class="text-slate-800 dark:text-white">{{ optional(optional($order->technician)->user)->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ optional($order->technician)->maintenance_company_id ? 'bg-indigo-500/10 text-indigo-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ optional($order->technician)->maintenance_company_id ? __('Company') : __('Platform') }}
                                    </span>
                                </td>
                                <td class="py-5 px-2">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-slate-800 dark:text-white">{{ number_format($order->total_price, 2) }}</span>
                                        <span class="text-[9px] text-slate-400"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span>
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

        <!-- Financial Settlements Tab -->
        <div x-show="activeTab === 'settlements'" x-transition class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Financial Settlements') }}</h3>
                <div class="relative w-full md:w-80 border border-slate-200 dark:border-white/5 rounded-xl">
                    <span class="absolute inset-y-0 left-{{ app()->getLocale() == 'ar' ? 'auto right' : '0' }}-0 {{ app()->getLocale() == 'ar' ? 'pr' : 'pl' }}-4 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input x-model="settlementSearch" type="text" placeholder="{{ __('Search') }}..." class="w-full h-12 {{ app()->getLocale() == 'ar' ? 'pr-12 pl-4' : 'pl-12 pr-4' }} rounded-xl border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>

            <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <thead>
                            <tr class="text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                                <th class="pb-4 px-2">#</th>
                                <th class="pb-4 px-2">{{ __('Amount') }}</th>
                                <th class="pb-4 px-2">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @forelse($settlements as $index => $contract)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ number_format($contract->amount, 2) }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></td>
                                <td class="py-5 px-2 text-slate-400">{{ $contract->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="py-12 text-center text-slate-400">{{ __('No settlements found') }}</td></tr>
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
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-black">
                                {{ mb_substr(optional(optional($review->technician)->user)->name ?? '-', 0, 1) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 dark:text-white">{{ optional(optional($review->technician)->user)->name ?? __('Technician') }}</h4>
                                <p class="text-[10px] text-slate-400 font-bold">{{ optional($review->order)->order_number ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            @for($i=1; $i<=5; $i++)
                            <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endfor
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-white italic">"{{ $review->comment ?? __('No comment provided.') }}"</p>
                    <div class="pt-4 border-t border-slate-50 dark:border-white/5 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400">{{ $review->created_at->format('d/m/Y') }}</span>
                        <span class="text-[10px] font-black text-primary uppercase">{{ optional($review->service)->{'name_'.app()->getLocale()} ?? '-' }}</span>
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

    <!-- Working Hours Modal -->
    <div x-data="{ open: false }" 
         @open-working-hours.window="open = true"
         @keydown.escape.window="open = false"
         x-show="open" 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" 
         style="display: none;">
         
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             @click="open = false" 
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Content -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="relative bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl overflow-hidden w-full max-w-2xl mx-4 sm:mx-auto z-50 transform transition-all border border-slate-100 dark:border-white/5">
             
            <!-- Header -->
            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-50 dark:border-white/5">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Company Working Hours') }}</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:text-white dark:hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-8 max-h-[70vh] overflow-y-auto no-scrollbar">
                @if($company->schedules->isEmpty())
                <div class="py-12 border border-dashed border-slate-200 dark:border-white/10 rounded-[1.5rem] flex flex-col items-center justify-center text-slate-400">
                    <svg class="w-12 h-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm font-bold">{{ __('No working hours found') }}</p>
                </div>
                @else
                <div class="space-y-4">
                    @php
                        $daysMapAr = [
                            'Sunday' => 'الأحد',
                            'Monday' => 'الإثنين',
                            'Tuesday' => 'الثلاثاء',
                            'Wednesday' => 'الأربعاء',
                            'Thursday' => 'الخميس',
                            'Friday' => 'الجمعة',
                            'Saturday' => 'السبت',
                        ];
                    @endphp
                    @foreach($company->schedules as $schedule)
                    <div class="flex items-center justify-between p-6 rounded-[1.5rem] bg-slate-50/70 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        <div class="flex-1 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <h4 class="text-base font-black text-slate-800 dark:text-white mb-2">
                                {{ app()->getLocale() == 'ar' ? ($daysMapAr[$schedule->day] ?? __($schedule->day)) : __($schedule->day) }}
                            </h4>
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                {{ __('From') }} 
                                <span class="font-black text-slate-700 dark:text-white/80 mx-1">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->translatedFormat('g:i A') }}
                                </span>
                                {{ __('To') }}
                                <span class="font-black text-slate-700 dark:text-white/80 mx-1">
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->translatedFormat('g:i A') }}
                                </span>
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-[1rem] bg-white dark:bg-[#1A1A31] shadow-sm flex items-center justify-center text-slate-500 dark:text-slate-400 shrink-0 border border-slate-100 dark:border-white/5 ml-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
