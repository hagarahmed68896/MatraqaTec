@extends('layouts.admin')

@section('title', __('Company Profile') . ' - ' . $company->user->name)

@section('content')
<div x-data="{ 
    activeTab: 'overview',
    orderSearch: '',
    orderSubTab: 'all',
    invoiceSearch: '',
    paymentSearch: '',
    reviewSearch: '',
    settlementSearch: '',
    chartType: '{{ $chartType }}',
    performanceData: @js($performanceData),
    chart: null,
    showScheduleModal: false,
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
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.5)'); // Emerald-500
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.performanceData.map(d => d.label),
                datasets: [{
                    label: '{{ __('Revenue') }}',
                    data: this.performanceData.map(d => d.count),
                    borderColor: '#10B981',
                    backgroundColor: gradient,
                    borderWidth: 4,
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10B981',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#10B981',
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
                                return context.parsed.y.toLocaleString() + ' <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle">';
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
                            padding: 10
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
    
    <!-- Top Gallery/Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.maintenance-companies.index') }}" class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-primary transition-all shadow-sm">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ $company->user->name }}</h2>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center p-1 bg-slate-100 dark:bg-white/5 rounded-2xl w-fit overflow-x-auto no-scrollbar max-w-full">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Overview') }}</button>
        <button @click="activeTab = 'technicians'" :class="activeTab === 'technicians' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Technicians') }}</button>
        <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Orders') }}</button>
        <button @click="activeTab = 'invoices'" :class="activeTab === 'invoices' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Invoices') }}</button>
        <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Payments') }}</button>
        <button @click="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Reviews') }}</button>
        <button @click="activeTab = 'settlements'" :class="activeTab === 'settlements' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Settlements') }}</button>
    </div>

    <!-- Tab Content -->
    <div class="space-y-8">
        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar Info -->
            <div class="space-y-8">
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-primary/5"></div>
                    
                    <div class="relative mt-8">
                        <div class="w-24 h-24 rounded-[2rem] bg-slate-100 dark:bg-white/10 mx-auto overflow-hidden border-4 border-white dark:border-[#1A1A31] shadow-xl flex items-center justify-center text-3xl font-black text-primary uppercase">
                            {{ mb_substr($company->user->name, 0, 1) }}
                        </div>
                        <div class="mt-6 text-center">
                            <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ $company->user->name }}</h3>
                            <p class="text-xs font-bold text-slate-400 mt-1">{{ __('Maintenance Company') }}</p>
                        </div>

                        <div class="mt-8 space-y-5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <div class="flex items-center gap-4 group">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Email') }}</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ $company->user->email }}</p>
                                </div>
                            </div>

                            <!-- Status Row -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Status') }}:</p>
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $company->user->status == 'active' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                        {{ __($company->user->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Commercial Record') }}:</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ $company->commercial_record_number ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Account Type -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3a2 2 0 002 2h4a2 2 0 002-2v-3M8 14V7a2 2 0 012-2h4a2 2 0 012 2v7M8 14h8"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Account Type') }}:</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ __('Maintenance Company') }}</p>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Address') }}:</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ $company->address ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Join Date -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Date') }}:</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ $company->created_at->format('Y-m-d') }}</p>
                                </div>
                            </div>

                            <!-- Work Schedule Trigger -->
                            <div @click="showScheduleModal = true" class="flex items-center gap-4 group mb-1 cursor-pointer">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Work Schedule') }}:</p>
                                    <p class="text-xs font-black text-primary truncate border-b border-primary/20 hover:border-primary transition-all">{{ __('View Schedule') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col gap-3">
                            <a href="{{ route('admin.maintenance-companies.edit', $company->id) }}" class="w-full py-4 bg-primary text-white rounded-2xl font-black text-xs shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                                {{ __('Edit Info') }}
                            </a>
                            <form action="{{ route('admin.maintenance-companies.toggle-block', $company->id) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full py-4 rounded-2xl font-black text-xs transition-all border-2 {{ $company->user->status == 'active' ? 'border-rose-500 text-rose-500 hover:bg-rose-500 hover:text-white' : 'border-emerald-500 text-emerald-500 hover:bg-emerald-500 hover:text-white' }}">
                                    {{ $company->user->status == 'active' ? __('Block') : __('Unblock') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Bank Account Information -->
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Bank Account Information') }}</h4>
                    </div>

                    <div class="space-y-4 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <!-- Bank Name -->
                        <div class="flex flex-col gap-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Bank Name') }}</p>
                            <p class="text-sm font-black text-slate-700 dark:text-white/80">{{ $company->bank_name ?? '-' }}</p>
                        </div>

                        <!-- Account Name -->
                        <div class="flex flex-col gap-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Account Name') }}</p>
                            <p class="text-sm font-black text-slate-700 dark:text-white/80">{{ $company->account_name ?? '-' }}</p>
                        </div>

                        <!-- Account Number -->
                        <div class="flex flex-col gap-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Account Number') }}</p>
                            <p class="text-sm font-black text-slate-700 dark:text-white/80 font-mono">{{ $company->account_number ?? '-' }}</p>
                        </div>

                        <!-- IBAN -->
                        <div class="flex flex-col gap-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('IBAN') }}</p>
                            <p class="text-sm font-black text-slate-700 dark:text-white/80 font-mono">{{ $company->iban ?? '-' }}</p>
                        </div>

                        <!-- SWIFT Code -->
                        <div class="flex flex-col gap-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('SWIFT Code') }}</p>
                            <p class="text-sm font-black text-slate-700 dark:text-white/80 font-mono">{{ $company->swift_code ?? '-' }}</p>
                        </div>

                        <!-- Bank Address -->
                        @if($company->bank_address)
                        <div class="flex flex-col gap-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Bank Address') }}</p>
                            <p class="text-sm font-black text-slate-700 dark:text-white/80">{{ $company->bank_address }}</p>
                        </div>
                        @endif
                    </div>
                </div>

           
            </div>

            <!-- Overview Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Mini Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Revenue') }}</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-black text-[10px]"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></div>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total_revenue'], 2) }}</h3>
                    </div>

                    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Staff') }}</span>
                            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ $stats['total_technicians'] }}</h3>
                    </div>

                    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Orders') }}</span>
                            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-black text-[10px]">#</div>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ $stats['total_orders'] }}</h3>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Financial Performance') }}</h3>
                        <div class="flex bg-slate-100 dark:bg-white/5 p-1 rounded-xl">
                            <button @click="updateChart('weekly')" :class="chartType === 'weekly' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-primary' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all">{{ __('Weekly') }}</button>
                            <button @click="updateChart('monthly')" :class="chartType === 'monthly' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-primary' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all">{{ __('Monthly') }}</button>
                            <button @click="updateChart('yearly')" :class="chartType === 'yearly' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-primary' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all">{{ __('Yearly') }}</button>
                        </div>
                    </div>
                    <div class="h-[300px] w-full relative">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Services -->
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6">{{ __('Service Ecosystem') }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($company->services as $service)
                        <span class="px-4 py-2 bg-slate-50 dark:bg-white/5 rounded-xl border border-slate-100 dark:border-white/5 text-xs font-bold text-slate-600 dark:text-slate-300">
                            {{ $service->{'name_'.app()->getLocale()} }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Technicians Tab -->
        <div x-show="activeTab === 'technicians'" x-transition class="space-y-8">
             <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($technicians as $tech)
                    <div class="flex flex-col items-center p-6 rounded-3xl border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/5 text-center group transition-all hover:shadow-xl">
                        <div class="w-20 h-20 rounded-2xl bg-white dark:bg-white/10 mx-auto mb-4 overflow-hidden border-4 border-white dark:border-[#1A1A31] shadow-lg">
                            @if($tech->image)
                            <img src="{{ asset('storage/'.$tech->image) }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-primary font-black text-2xl uppercase">{{ mb_substr($tech->{'name_'.app()->getLocale()}, 0, 1) }}</div>
                            @endif
                        </div>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white mb-1">{{ $tech->{'name_'.app()->getLocale()} }}</h4>
                        <p class="text-[10px] text-slate-400 font-bold mb-4">{{ $tech->service->{'name_'.app()->getLocale()} ?? '-' }}</p>
                        <a href="{{ route('admin.technicians.show', $tech->id) }}" class="w-full py-3 bg-white dark:bg-white/10 rounded-xl text-primary font-black text-[10px] hover:bg-primary hover:text-white transition-all">{{ __('View Profile') }}</a>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-20 text-slate-400">{{ __('No technicians registered yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Orders Tab -->
        <div x-show="activeTab === 'orders'" x-transition class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                
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
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-{{ app()->getLocale() == 'ar' ? 'auto right' : '0' }}-0 {{ app()->getLocale() == 'ar' ? 'pr' : 'pl' }}-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input x-model="orderSearch" type="text" placeholder="{{ __('Search') }}..." class="w-full h-12 {{ app()->getLocale() == 'ar' ? 'pr-12 pl-4' : 'pl-12 pr-4' }} rounded-xl border-slate-100 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <thead>
                            <tr class="text-slate-400 text-[12px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                                <th class="pb-4 px-2">#</th>
                                <th class="pb-4 px-2">{{ __('Order #') }}</th>
                                <th class="pb-4 px-2">{{ __('Customer') }}</th>
                                <th class="pb-4 px-2">{{ __('Service Name') }}</th>
                                <th class="pb-4 px-2">{{ __('Address') }}</th>
                                <th class="pb-4 px-2">{{ __('Technician') }}</th>
                                <th class="pb-4 px-2">{{ __('Price') }}</th>
                                <th class="pb-4 px-2">{{ __('Status') }}</th>
                                <th class="pb-4 px-2">{{ __('Date/Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @foreach($orders as $index => $order)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all"
                                x-show="(orderSubTab === 'all' || orderSubTab === '{{ $order->status }}') && 
                                       ('{{ $order->order_number }}'.includes(orderSearch) || 
                                        '{{ optional($order->user)->name }}'.toLowerCase().includes(orderSearch.toLowerCase()) ||
                                        '{{ optional($order->service)->{'name_'.app()->getLocale()} }}'.toLowerCase().includes(orderSearch.toLowerCase()))">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white">{{ __('Order') }} - #{{ $order->order_number }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ $order->user->name ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-600 dark:text-slate-300">{{ $order->service->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400 truncate max-w-[150px]">{{ $order->address ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-[10px] text-primary font-black">
                                            {{ mb_substr($order->technician->{'name_'.app()->getLocale()} ?? '-', 0, 1) }}
                                        </div>
                                        <span class="text-slate-800 dark:text-white">{{ $order->technician->{'name_'.app()->getLocale()} ?? '-' }}</span>
                                    </div>
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

        <!-- Invoices Tab -->
        <div x-show="activeTab === 'invoices'" x-transition class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="relative w-full md:w-80">
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
                                <th class="pb-4 px-2">{{ __('Customer') }}</th>
                                <th class="pb-4 px-2">{{ __('Service') }}</th>
                                <th class="pb-4 px-2">{{ __('Technician') }}</th>
                                <th class="pb-4 px-2">{{ __('Amount') }}</th>
                                <th class="pb-4 px-2">{{ __('Status') }}</th>
                                <th class="pb-4 px-2">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @forelse($invoices as $index => $invoice)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">#{{ $invoice->invoice_number ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white">{{ $invoice->order->user->name ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-600 dark:text-slate-300">{{ $invoice->order->service->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-[10px] text-primary font-black">
                                            {{ mb_substr($invoice->order->technician->user->name ?? '-', 0, 1) }}
                                        </div>
                                        <span class="text-slate-800 dark:text-white">{{ $invoice->order->technician->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-slate-800 dark:text-white">{{ number_format($invoice->total_amount, 2) }}</span>
                                        <span class="text-[9px] text-slate-400"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider {{ $invoice->status == 'paid' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ __($invoice->status) }}
                                    </span>
                                </td>
                                <td class="py-5 px-2 text-slate-400">{{ $invoice->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="py-12 text-center text-slate-400">{{ __('No invoices found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payments Tab -->
        <div x-show="activeTab === 'payments'" x-transition class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="relative w-full md:w-80">
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
                                <th class="pb-4 px-2">{{ __('Transaction #') }}</th>
                                <th class="pb-4 px-2">{{ __('Customer') }}</th>
                                <th class="pb-4 px-2">{{ __('Service') }}</th>
                                <th class="pb-4 px-2">{{ __('Technician') }}</th>
                                <th class="pb-4 px-2">{{ __('Amount') }}</th>
                                <th class="pb-4 px-2">{{ __('Method') }}</th>
                                <th class="pb-4 px-2">{{ __('Status') }}</th>
                                <th class="pb-4 px-2">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @forelse($payments as $index => $payment)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">#{{ $payment->transaction_id ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white">{{ $payment->order->user->name ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-600 dark:text-slate-300">{{ $payment->order->service->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-[10px] text-primary font-black">
                                            {{ mb_substr($payment->order->technician->user->name ?? '-', 0, 1) }}
                                        </div>
                                        <span class="text-slate-800 dark:text-white">{{ $payment->order->technician->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-slate-800 dark:text-white">{{ number_format($payment->amount, 2) }}</span>
                                        <span class="text-[9px] text-slate-400"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300">
                                        {{ __($payment->payment_method) }}
                                    </span>
                                </td>
                                <td class="py-5 px-2">
                                    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider {{ $payment->status == 'completed' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ __($payment->status) }}
                                    </span>
                                </td>
                                <td class="py-5 px-2 text-slate-400">{{ $payment->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="py-12 text-center text-slate-400">{{ __('No payments found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reviews Tab -->
        <div x-show="activeTab === 'reviews'" x-transition class="space-y-6">
            
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
                            @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-white/10' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            @endfor
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 italic">"{{ $review->comment ?? __('No comment provided.') }}"</p>
                    <div class="pt-4 border-t border-slate-50 dark:border-white/5 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400">{{ $review->created_at->format('d/m/Y') }}</span>
                        <span class="text-[10px] font-black text-primary uppercase">{{ $review->service->{'name_'.app()->getLocale()} ?? '-' }}</span>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-20 text-slate-400">{{ __('No reviews yet.') }}</div>
                @endforelse
            </div>
        </div>

        <!-- Settlements Tab -->
        <div x-show="activeTab === 'settlements'" x-transition class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="relative w-full md:w-80">
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
                                <th class="pb-4 px-2">{{ __('Ref #') }}</th>
                                <th class="pb-4 px-2">{{ __('Customer') }}</th>
                                <th class="pb-4 px-2">{{ __('Service Name') }}</th>
                                <th class="pb-4 px-2">{{ __('Address') }}</th>
                                <th class="pb-4 px-2">{{ __('Technician') }}</th>
                                <th class="pb-4 px-2">{{ __('Amount') }}</th>
                                <th class="pb-4 px-2">{{ __('Status') }}</th>
                                <th class="pb-4 px-2">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold">
                            @forelse($settlements as $index => $settlement)
                            <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all"
                                x-show="'{{ $settlement->amount }}'.includes(settlementSearch) || '{{ $settlement->reference_number ?? '' }}'.toLowerCase().includes(settlementSearch.toLowerCase())">
                                <td class="py-5 px-2 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">#{{ $settlement->reference_number ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ $settlement->order->user->name ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-600 dark:text-slate-300">{{ $settlement->order->service->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-5 px-2 text-slate-400 truncate max-w-[150px]">{{ $settlement->order->address ?? '-' }}</td>
                                <td class="py-5 px-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-[10px] text-primary font-black">
                                            {{ mb_substr($settlement->order->technician->{'name_'.app()->getLocale()} ?? '-', 0, 1) }}
                                        </div>
                                        <span class="text-slate-800 dark:text-white">{{ $settlement->order->technician->{'name_'.app()->getLocale()} ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-2 text-slate-800 dark:text-white font-black">{{ number_format($settlement->amount, 2) }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></td>
                                <td class="py-5 px-2">
                                    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider {{ $settlement->status == 'completed' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ __($settlement->status) }}
                                    </span>
                                </td>
                                <td class="py-5 px-2 text-slate-400">{{ $settlement->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="py-12 text-center text-slate-400">{{ __('No settlements found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Work Schedule Modal -->
    <div x-show="showScheduleModal" 
         class="fixed inset-0 z-[100] overflow-y-auto" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="showScheduleModal = false"></div>

            <div class="inline-block w-full max-w-lg overflow-hidden text-right align-bottom transition-all transform bg-white dark:bg-[#1A1A31] rounded-[2.5rem] shadow-2xl sm:my-8 sm:align-middle"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <div class="relative p-8 px-10">
                    <!-- Close Button -->
                    <button @click="showScheduleModal = false" class="absolute top-8 {{ app()->getLocale() == 'ar' ? 'left-8' : 'right-8' }} w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-primary transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- Header -->
                    <div class="mb-10 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">{{ __('Maintenance Company Work Hours') }}</h3>
                        <div class="h-1.5 w-12 bg-primary rounded-full"></div>
                    </div>

                    <!-- schedule List -->
                    <div class="grid grid-cols-1 gap-4">
                        @php
                            $days = [
                                'Sunday' => __('Sunday'),
                                'Monday' => __('Monday'),
                                'Tuesday' => __('Tuesday'),
                                'Wednesday' => __('Wednesday'),
                                'Thursday' => __('Thursday'),
                                'Friday' => __('Friday'),
                                'Saturday' => __('Saturday'),
                            ];
                        @endphp
                        
                        @foreach($days as $dayKey => $dayName)
                            @php
                                $schedule = $company->schedules->where('day', $dayKey)->first();
                            @endphp
                            <div class="flex items-center justify-between p-5 rounded-[2rem] {{ $schedule ? 'bg-primary/5 border-primary/10' : 'bg-slate-50 dark:bg-white/5 border-slate-100 dark:border-white/5 opacity-50' }} border transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-[1.25rem] {{ $schedule ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white dark:bg-[#1A1A31] text-slate-300' }} flex items-center justify-center border border-transparent">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                                        <h4 class="text-sm font-black {{ $schedule ? 'text-slate-800 dark:text-white' : 'text-slate-400' }}">{{ $dayName }}</h4>
                                        <p class="text-[11px] font-bold {{ $schedule ? 'text-primary' : 'text-slate-400' }} mt-0.5">
                                            @if($schedule)
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->translatedFormat('g:i A') }} 
                                                - 
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->translatedFormat('g:i A') }}
                                            @else
                                                {{ __('Closed') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @if($schedule)
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">{{ __('Active') }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection