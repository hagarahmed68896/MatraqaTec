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
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.5)'); // Emerald-500
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.performanceData.map(d => d.label),
                datasets: [{
                    label: '{{ __('Orders') }}',
                    data: this.performanceData.map(d => d.count),
                    borderColor: '#10b981',
                    backgroundColor: gradient,
                    borderWidth: 4,
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#10b981',
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
            <a href="{{ route('admin.technicians.index') }}" class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-primary transition-all shadow-sm">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ $item->{'name_'.app()->getLocale()} }}</h2>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center p-1 bg-slate-100 dark:bg-white/5 rounded-2xl w-fit overflow-x-auto no-scrollbar max-w-full">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Overview') }}</button>
        <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Orders') }}</button>
        <button @click="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'bg-white dark:bg-[#1A1A31] shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-6 py-2.5 rounded-xl text-xs whitespace-nowrap transition-all">{{ __('Reviews') }}</button>
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
                        <div class="w-24 h-24 rounded-[2rem] bg-slate-100 dark:bg-white/10 mx-auto overflow-hidden border-4 border-white dark:border-[#1A1A31] shadow-xl flex items-center justify-center text-3xl font-black text-primary">
                            @if($item->image)
                                <img src="{{ asset('storage/'.$item->image) }}" class="w-full h-full object-cover">
                            @else
                                {{ mb_substr($item->{'name_'.app()->getLocale()} ?? 'T', 0, 1) }}
                            @endif
                        </div>
                        <div class="mt-6 text-center">
                            <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ $item->{'name_'.app()->getLocale()} }}</h3>
                            <p class="text-xs font-bold text-slate-400 mt-1">{{ __('Professional Technician') }}</p>
                        </div>

                        <div class="mt-8 space-y-5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <div class="flex items-center gap-4 group">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Email') }}</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ $item->user->email }}</p>
                                </div>
                            </div>

                            <!-- Status Row -->
                           <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Status') }}:</p>
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $item->user->status == 'active' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                        {{ __($item->user->status) }}
                                    </span>
                                </div>
                            </div>

                            @if($item->user->city)
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('City') }}:</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ $item->user->city->{'name_'.app()->getLocale()} }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Account Type -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Account Type') }}:</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ __('Technician') }}</p>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Address') }}:</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ $item->user->address ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Join Date -->
                            <div class="flex items-center gap-4 group mb-1">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 flex items-center gap-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">{{ __('Join Date') }}:</p>
                                    <p class="text-xs font-black text-slate-700 dark:text-white/80 truncate">{{ $item->created_at->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col gap-3">
                            <a href="{{ route('admin.technicians.edit', $item->id) }}" class="w-full py-4 bg-primary text-white rounded-2xl font-black text-xs shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                                {{ __('Edit Profile') }}
                            </a>
                            <form action="{{ route('admin.technicians.toggle-block', $item->id) }}" method="POST" class="w-full">
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Revenue') }}</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-black text-[10px]">{{ __('SAR') }}</div>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ number_format($stats['revenue'], 2) }}</h3>
                    </div>

                    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Completed') }}</span>
                            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-black text-[10px]">#</div>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ $stats['completed_orders'] }}</h3>
                    </div>

                    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Rating') }}</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ number_format($stats['rating'], 1) }}</h3>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Order Completion Trend') }}</h3>
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

                <!-- Profile Details -->
                <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6">{{ __('Work Profile') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Specialization') }}</p>
                            <div class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5">
                                <div class="w-2 h-2 rounded-full bg-primary"></div>
                                <span class="text-sm font-bold text-slate-700 dark:text-white/80">{{ $item->service->{'name_'.app()->getLocale()} ?? '-' }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Workplace') }}</p>
                            <div class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5">
                                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                <span class="text-sm font-bold text-slate-700 dark:text-white/80">{{ $item->maintenanceCompany->{'company_name_'.app()->getLocale()} ?? __('Freelancer') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Tab -->
        <div x-show="activeTab === 'orders'" x-transition class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
             <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6">{{ __('Assigned Orders') }}</h3>
             <div class="overflow-x-auto">
                <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <thead>
                        <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                            <th class="pb-4 px-2">{{ __('Order #') }}</th>
                            <th class="pb-4 px-2">{{ __('Customer') }}</th>
                            <th class="pb-4 px-2 text-center">{{ __('Status') }}</th>
                            <th class="pb-4 px-2 text-center">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs font-bold">
                        @foreach($orders as $order)
                        <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                            <td class="py-4 px-2 text-slate-800 dark:text-white font-black">#{{ $order->order_number }}</td>
                            <td class="py-4 px-2 text-slate-500 font-medium">{{ $order->user->name ?? '-' }}</td>
                            <td class="py-4 px-2">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider
                                    @if($order->status == 'completed') bg-emerald-500/10 text-emerald-500
                                    @elseif($order->status == 'cancelled') bg-rose-500/10 text-rose-500
                                    @else bg-blue-500/10 text-blue-500 @endif text-center block mx-auto w-fit">
                                    {{ __($order->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-2 text-slate-800 dark:text-white text-center">{{ number_format($order->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reviews Tab -->
        <div x-show="activeTab === 'reviews'" x-transition class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
             <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6">{{ __('Technician Reviews') }}</h3>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($reviews as $review)
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 group transition-all hover:bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-black uppercase">
                                {{ mb_substr($review->user->name ?? 'C', 0, 1) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 dark:text-white">{{ $review->user->name ?? __('Customer') }}</h4>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ $review->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-0.5">
                           @for($i = 1; $i <= 5; $i++)
                            <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                           @endfor
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic">"{{ $review->comment ?? __('Excellent work!') }}"</p>
                </div>
                @empty
                <div class="col-span-full py-12 text-center text-slate-400 font-medium">{{ __('No reviews recorded yet.') }}</div>
                @endforelse
             </div>
        </div>
    </div>
</div>
@endsection
