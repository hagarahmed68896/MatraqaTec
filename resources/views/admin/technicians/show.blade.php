@extends('layouts.admin')

@section('title', __('Technician Profile') . ' - ' . $item->{'name_'.app()->getLocale()})

@section('content')
<div x-data="{ 
    activeTab: 'overview',
    chartType: '{{ $chartType }}',
    performanceData: @js($performanceData),
    chart: null,
    updateChart(type) {
        this.chartType = type;
        fetch(`{{ route('admin.technicians.show', $item->id) }}?chart_type=${type}`, {
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
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(26, 26, 49, 0.1)');
        gradient.addColorStop(1, 'rgba(26, 26, 49, 0)');

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.performanceData.map(d => d.label),
                datasets: [{
                    label: '{{ __('Orders') }}',
                    data: this.performanceData.map(d => d.count),
                    borderColor: '#1A1A31',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#1A1A31',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1A1A31',
                        padding: 12,
                        cornerRadius: 12,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { font: { size: 10, weight: '600' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '600' } }
                    }
                }
            }
        });
    }
}" x-init="renderChart" class="pb-20 space-y-8 px-4 md:px-8">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-6">
            <a href="{{ route('admin.technicians.index') }}" class="w-12 h-12 rounded-2xl bg-white dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-primary transition-all shadow-sm border border-slate-100 dark:border-white/5">
                <svg class="w-6 h-6 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="text-3xl font-black text-slate-800 dark:text-white">{{ $item->{'name_'.app()->getLocale()} }}</h2>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 p-1.5 bg-white dark:bg-white/5 rounded-[2rem] w-fit shadow-sm border border-slate-100 dark:border-white/5 overflow-x-auto no-scrollbar max-w-full">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/10'" class="px-8 py-3 rounded-[1.5rem] text-sm font-black transition-all uppercase tracking-wider whitespace-nowrap">{{ __('Overview') }}</button>
        <button @click="activeTab = 'tasks'" :class="activeTab === 'tasks' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/10'" class="px-8 py-3 rounded-[1.5rem] text-sm font-black transition-all uppercase tracking-wider whitespace-nowrap">{{ __('Tasks') }}</button>
        <button @click="activeTab = 'settlements'" :class="activeTab === 'settlements' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/10'" class="px-8 py-3 rounded-[1.5rem] text-sm font-black transition-all uppercase tracking-wider whitespace-nowrap">{{ __('Financial Settlements') }}</button>
        <button @click="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/10'" class="px-8 py-3 rounded-[1.5rem] text-sm font-black transition-all uppercase tracking-wider whitespace-nowrap">{{ __('Reviews') }}</button>
    </div>

    <!-- Tab Content -->
    <div class="space-y-8">
        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Sidebar Info (Now inside Overview) -->
            <div class="space-y-8">
                <!-- Technician Main Card -->
                <div class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm text-center relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-32 bg-primary/5 transition-all group-hover:h-36"></div>
                    
                    <div class="relative mt-8">
                        <!-- Profile Photo -->
                        <div class="w-24 h-24 rounded-[2rem] bg-slate-100 dark:bg-white/10 mx-auto overflow-hidden border-4 border-white dark:border-[#1A1A31] shadow-2xl transition-transform group-hover:scale-105 flex items-center justify-center text-4xl font-black text-primary">
                            @if($item->image)
                                <img src="{{ asset('storage/'.$item->image) }}" class="w-full h-full object-cover">
                            @else
                                {{ mb_substr($item->{'name_'.app()->getLocale()} ?? 'T', 0, 1) }}
                            @endif
                        </div>
                        
                        <div class="mt-8 space-y-2 text-center">
                            <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ $item->{'name_'.app()->getLocale()} }}</h3>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $item->maintenanceCompany ? __('Company Technician') : __('Platform Technician') }}</p>
                        </div>

                        <!-- Detailed Info List -->
                        <div class="mt-10 space-y-5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            
                            <!-- Phone -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ __('Phone Number') }}</p>
                                    <p class="text-sm font-black text-slate-700 dark:text-white/80 transition-colors group-hover:text-primary leading-tight overflow-hidden text-ellipsis whitespace-nowrap" dir="ltr">{{ $item->user->phone ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ __('Email Address') }}</p>
                                    <p class="text-sm font-black text-slate-700 dark:text-white/80 transition-colors group-hover:text-primary leading-tight overflow-hidden text-ellipsis whitespace-nowrap">{{ $item->user->email ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Regions -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ __('Working Regions') }}</p>
                                    <p class="text-sm font-black text-slate-700 dark:text-white/80 transition-colors group-hover:text-primary leading-tight">{{ __('All Regions') }}</p>
                                </div>
                            </div>

                            <!-- Join Date -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ __('Registration Date') }}</p>
                                    <p class="text-sm font-black text-slate-700 dark:text-white/80 transition-colors group-hover:text-primary leading-tight">{{ $item->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-10 flex flex-col gap-3">
                            <a href="{{ route('admin.technicians.edit', $item->id) }}" class="w-full py-4 bg-primary text-white rounded-2xl font-black text-xs shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                {{ __('Edit Profile') }}
                            </a>

                            <form action="{{ route('admin.technicians.toggle-block', $item->id) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" 
                                    class="w-full py-4 rounded-2xl font-black text-xs transition-all border-2 cursor-pointer hover:scale-[1.02] active:scale-[0.98] 
                                    {{ $item->user->status == 'active' 
                                        ? 'border-rose-500 text-rose-500 hover:bg-rose-500 hover:text-white shadow-lg shadow-rose-500/10' 
                                        : 'border-emerald-500 text-emerald-500 hover:bg-emerald-500 hover:text-white shadow-lg shadow-emerald-500/10' 
                                    }}">
                                    {{ $item->user->status == 'active' ? __('Block Technician') : __('Unblock Technician') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Technician Bio Card -->
                <div class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm space-y-6">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white flex items-center gap-3">
                        <span class="w-1.5 h-6 bg-primary rounded-full"></span>
                        {{ __('Biography') }}
                    </h3>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed italic">
                        "{{ $item->{'bio_'.app()->getLocale()} ?? __('No biography available for this technician.') }}"
                    </p>
                </div>
            </div>

            <!-- Overview Content -->
            <div class="lg:col-span-2 space-y-8">                
                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Average Rating -->
                    <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm flex items-center justify-between">
                        <div class="space-y-2">
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ __('Average Rating') }}</p>
                            <div class="flex items-end gap-2">
                                <span class="text-4xl font-black text-slate-800 dark:text-white">{{ number_format($stats['rating'], 1) }}/5</span>
                                <svg class="w-6 h-6 text-amber-400 mb-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            </div>
                        </div>
                        <div class="relative w-24 h-24">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-100 dark:text-white/5"/>
                                <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" :stroke-dasharray="251.2" :stroke-dashoffset="251.2 - (251.2 * {{ $stats['rating'] }}) / 5" class="text-amber-400" stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-black text-slate-800 dark:text-white">{{ number_format(($stats['rating'] / 5) * 100, 0) }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Count -->
                    <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm flex items-center justify-between">
                        <div class="space-y-2">
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ __('Tasks Count') }}</p>
                            <div class="flex items-end gap-2">
                                <span class="text-4xl font-black text-slate-800 dark:text-white">{{ $stats['completed_orders'] }}</span>
                                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="w-32 h-16">
                            <!-- Simple mini-line chart for tasks count -->
                            <svg class="w-full h-full" viewBox="0 0 100 40">
                                <path d="M0,35 Q10,10 20,30 T40,15 T60,25 T80,10 T100,30" fill="none" stroke="#10B981" stroke-width="3" stroke-linecap="round"/>
                                <path d="M0,35 Q10,10 20,30 T40,15 T60,25 T80,10 T100,30 V40 H0 Z" fill="url(#miniGradient)"/>
                                <defs>
                                    <linearGradient id="miniGradient" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#10B981" stop-opacity="0.2"/>
                                        <stop offset="100%" stop-color="#10B981" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- User Performance Chart -->
                <div class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('User Performance') }}</h3>
                            <div class="h-1.5 w-12 bg-primary mt-2 rounded-full"></div>
                        </div>
                        <div class="flex bg-slate-100 dark:bg-white/5 p-1 rounded-2xl border border-slate-200/50 dark:border-white/5">
                            <button @click="updateChart('weekly')" :class="chartType === 'weekly' ? 'bg-white dark:bg-[#1A1A31] shadow-md text-primary' : 'text-slate-400 hover:text-slate-600'" class="px-5 py-2 rounded-[1rem] text-[10px] font-black uppercase tracking-widest transition-all">{{ __('Weekly') }}</button>
                            <button @click="updateChart('monthly')" :class="chartType === 'monthly' ? 'bg-white dark:bg-[#1A1A31] shadow-md text-primary' : 'text-slate-400 hover:text-slate-600'" class="px-5 py-2 rounded-[1rem] text-[10px] font-black uppercase tracking-widest transition-all">{{ __('Monthly') }}</button>
                            <button @click="updateChart('yearly')" :class="chartType === 'yearly' ? 'bg-white dark:bg-[#1A1A31] shadow-md text-primary' : 'text-slate-400 hover:text-slate-600'" class="px-5 py-2 rounded-[1rem] text-[10px] font-black uppercase tracking-widest transition-all">{{ __('Yearly') }}</button>
                        </div>
                    </div>
                    <div class="h-[350px] w-full relative">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Profile Data Grid -->
                <!-- Bank Account Information -->
                <div class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm">
                    <div class="flex items-center justify-between mb-10">
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Bank Account Information') }}</h3>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">{{ __('Account Name') }}</label>
                            <div class="px-6 py-4 bg-slate-50 dark:bg-white/5 rounded-2xl text-sm font-bold border border-slate-100 dark:border-white/5 text-slate-700 dark:text-white/80">
                                {{ $item->account_name ?? '-' }}
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">{{ __('Account Number') }}</label>
                            <div class="px-6 py-4 bg-slate-50 dark:bg-white/5 rounded-2xl text-sm font-bold border border-slate-100 dark:border-white/5 text-slate-700 dark:text-white/80">
                                {{ $item->account_number ?? '-' }}
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">{{ __('Bank Name') }}</label>
                            <div class="px-6 py-4 bg-slate-50 dark:bg-white/5 rounded-2xl text-sm font-bold border border-slate-100 dark:border-white/5 text-slate-700 dark:text-white/80">
                                {{ $item->bank_name ?? '-' }}
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">{{ __('Bank Address') }}</label>
                            <div class="px-6 py-4 bg-slate-50 dark:bg-white/5 rounded-2xl text-sm font-bold border border-slate-100 dark:border-white/5 text-slate-700 dark:text-white/80">
                                {{ $item->bank_address ?? '-' }}
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">{{ __('IBAN') }}</label>
                            <div class="px-6 py-4 bg-slate-50 dark:bg-white/5 rounded-2xl text-sm font-bold border border-slate-100 dark:border-white/5 text-slate-700 dark:text-white/80">
                                {{ $item->iban ?? '-' }}
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">{{ __('SWIFT Code') }}</label>
                            <div class="px-6 py-4 bg-slate-50 dark:bg-white/5 rounded-2xl text-sm font-bold border border-slate-100 dark:border-white/5 text-slate-700 dark:text-white/80">
                                {{ $item->swift_code ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Tab Content -->
            <div x-show="activeTab === 'tasks'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="p-10 flex items-center justify-between border-b border-slate-100 dark:border-white/5">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Tasks') }}</h3>
                        <div class="h-1.5 w-12 bg-primary mt-2 rounded-full"></div>
                    </div>
                    <div class="flex gap-4">
                         <div class="relative group" x-data="{ open: false }">
                            <button @click="open = !open" class="px-6 py-3 bg-slate-100 dark:bg-white/5 rounded-2xl text-xs font-black text-slate-600 dark:text-slate-400 flex items-center gap-3 transition-all hover:bg-slate-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                {{ __('Filter by') }}
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <!-- Dropdown content for task filtering -->
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-white/5 text-slate-400 text-[10px] font-black uppercase tracking-widest">
                                <th class="py-6 px-8 rounded-{{ app()->getLocale() == 'ar' ? 'tr' : 'tl' }}-[2.5rem]">#</th>
                                <th class="py-6 px-8">{{ __('Order Number') }}</th>
                                <th class="py-6 px-8">{{ __('Customer Name') }}</th>
                                <th class="py-6 px-8">{{ __('Customer Type') }}</th>
                                <th class="py-6 px-8">{{ __('Service Name') }}</th>
                                <th class="py-6 px-8">{{ __('Service Category') }}</th>
                                <th class="py-6 px-8">{{ __('Address') }}</th>
                                <th class="py-6 px-8">{{ __('Tech Type') }}</th>
                                <th class="py-6 px-8 text-center">{{ __('Status') }}</th>
                                <th class="py-6 px-8 rounded-{{ app()->getLocale() == 'ar' ? 'tl' : 'tr' }}-[2.5rem]">{{ __('Date/Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach($orders as $index => $order)
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-all group">
                                <td class="py-6 px-8 text-xs font-black text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-6 px-8 text-sm font-black text-slate-800 dark:text-white">#{{ $order->order_number }}</td>
                                <td class="py-6 px-8">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-black text-[10px]">
                                            {{ mb_substr($order->user->name ?? 'C', 0, 1) }}
                                        </div>
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $order->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-6 px-8 text-sm font-black text-slate-500 uppercase">{{ __($order->user->type ?? 'Individual') }}</td>
                                <td class="py-6 px-8 text-sm font-bold text-slate-700 dark:text-slate-300">{{ $order->service->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-6 px-8 text-sm font-bold text-slate-500 italic">{{ $order->service->parent->{'name_'.app()->getLocale()} ?? '-' }}</td>
                                <td class="py-6 px-8 text-sm font-bold text-slate-500 max-w-[150px] truncate">{{ $order->address ?? '-' }}</td>
                                <td class="py-6 px-8 text-sm font-black text-slate-500">
                                    {{ $order->technician->maintenanceCompany ? __('Corporate') : __('Independent') }}
                                </td>
                                <td class="py-6 px-8 text-center">
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest
                                        @if($order->status == 'completed') bg-emerald-500/10 text-emerald-500
                                        @elseif($order->status == 'cancelled') bg-rose-500 text-white
                                        @elseif($order->status == 'scheduled') bg-indigo-500 text-white
                                        @else bg-blue-500/10 text-blue-500 @endif">
                                        {{ __($order->status) }}
                                    </span>
                                </td>
                                <td class="py-6 px-8 text-sm font-black text-slate-800 dark:text-white truncate">
                                    {{ $order->created_at->format('Y/m/d H:i') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

             <!-- Settlements Tab Content -->
             <div x-show="activeTab === 'settlements'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="p-10 flex items-center justify-between border-b border-slate-100 dark:border-white/5">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Financial Settlements') }}</h3>
                        <div class="h-1.5 w-12 bg-primary mt-2 rounded-full"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-white/5 text-slate-400 text-[10px] font-black uppercase tracking-widest">
                                <th class="py-6 px-8 rounded-{{ app()->getLocale() == 'ar' ? 'tr' : 'tl' }}-[2.5rem]">#</th>
                                <th class="py-6 px-8">{{ __('Settlement Number') }}</th>
                                <th class="py-6 px-8">{{ __('Order Number') }}</th>
                                <th class="py-6 px-8">{{ __('Amount') }}</th>
                                <th class="py-6 px-8">{{ __('Payment Method') }}</th>
                                <th class="py-6 px-8 text-center">{{ __('Status') }}</th>
                                <th class="py-6 px-8">{{ __('Date') }}</th>
                                <th class="py-6 px-8 rounded-{{ app()->getLocale() == 'ar' ? 'tl' : 'tr' }}-[2.5rem]">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach($settlements as $index => $s)
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                                <td class="py-6 px-8 text-xs font-black text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-6 px-8 text-sm font-black text-slate-800 dark:text-white">SET-{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-6 px-8 text-sm font-bold text-slate-500">#{{ $s->order->order_number ?? '-' }}</td>
                                <td class="py-6 px-8">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-slate-800 dark:text-white">{{ number_format($s->amount, 2) }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span>
                                    </div>
                                </td>
                                <td class="py-6 px-8 text-sm font-bold text-slate-500">{{ __($s->payment_method) }}</td>
                                <td class="py-6 px-8 text-center">
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $s->status === 'completed' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500' }}">
                                        {{ __($s->status) }}
                                    </span>
                                </td>
                                <td class="py-6 px-8 text-sm font-black text-slate-800 dark:text-white">
                                    {{ $s->created_at->format('Y/m/d') }}
                                </td>
                                <td class="py-6 px-8">
                                    <div class="flex items-center gap-3">
                                        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:bg-primary hover:text-white transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:bg-primary hover:text-white transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reviews Tab Content -->
            <div x-show="activeTab === 'reviews'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="mb-10">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Technician Reviews') }}</h3>
                    <div class="h-1.5 w-12 bg-primary mt-2 rounded-full"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @forelse($reviews as $review)
                    <div class="p-8 rounded-[2rem] bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 group transition-all hover:bg-white hover:shadow-xl hover:shadow-primary/5 hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-black text-lg uppercase tracking-widest">
                                    {{ mb_substr($review->user->name ?? 'C', 0, 1) }}
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-black text-slate-800 dark:text-white">{{ $review->user->name ?? __('Customer') }}</h4>
                                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $review->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-0.5">
                               @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-800' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                               @endfor
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-bold italic">"{{ $review->comment ?? __('No comment provided.') }}"</p>
                    </div>
                    @empty
                    <div class="col-span-full py-20 text-center space-y-4">
                        <div class="w-20 h-20 bg-slate-100 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto text-slate-300">
                             <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                        <p class="text-slate-400 font-black uppercase tracking-widest text-sm">{{ __('No reviews recorded yet.') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>

    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
