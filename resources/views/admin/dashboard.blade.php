@extends('layouts.admin')

@section('title', __('Admin Dashboard') . ' - ' . __('MatraqaTec'))
@section('page_title', __('Admin Dashboard'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700" x-data="orderManagement()">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        #tech-map { width: 100%; height: 500px; border-radius: 3rem; }
        .leaflet-container { background: #f8fafc; }
    </style>
    
    <!-- Dashboard Heading -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Admin Dashboard') }}</h2>
    </div>

    <!-- Stats Grid: Mirroring Screenshot -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- Available Technicians -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-300 mb-1">{{ __('Total Available Technicians') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['available_techs'] }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['available_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['available_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['available_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['available_change'] }}%+</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium mt-1">{{ __('Compared to last week') }}</p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
                <canvas id="sparkline1" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- New Orders -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-300 mb-1">{{ __('New Orders') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['new_orders'] }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['new_orders_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['new_orders_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['new_orders_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['new_orders_change'] }}%+</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium mt-1">{{ __('Compared to last week') }}</p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
                <canvas id="sparkline2" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-300 mb-1">{{ __('Total Revenue') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total_revenue']) }}</h3>
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" class="w-4 h-4 opacity-70 filter dark:invert" alt="SAR">
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['revenue_change'] }}%+</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium mt-1">{{ __('Compared to last week') }}</p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
                <canvas id="sparkline3" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Avg Quality -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-300 mb-1">{{ __('Average Quality') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['avg_quality'] }}/5</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['quality_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['quality_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['quality_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['quality_change'] }}%+</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium mt-1">{{ __('Compared to last week') }}</p>
                </div>
                <div class="relative w-14 h-14">
                    <canvas id="qualityDoughnut"></canvas>
                    <span class="absolute inset-0 flex items-center justify-center text-[10px] font-black text-slate-800 dark:text-white">{{ $stats['avg_quality'] }}/5</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Charts Section -->
    <!-- Main Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Chart -->
        <div x-data="{ 
            period: '{{ $stats['revenue_period'] }}',
            loading: false,
            async updatePeriod(val) {
                this.period = val;
                this.loading = true;
                try {
                    const response = await axios.get('{{ route('admin.dashboard.revenue') }}', {
                        params: { 
                            period: val,
                            locale: '{{ app()->getLocale() }}'
                        }
                    });
                    const chart = Chart.getChart('revenueChart');
                    if (chart) {
                        chart.data.labels = response.data.labels;
                        chart.data.datasets[0].data = response.data.data;
                        chart.update('active');
                    }
                } catch (error) {
                    console.error('Revenue chart update failed:', error);
                } finally {
                    this.loading = false;
                }
            }
        }" class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm relative">
            <div x-show="loading" class="absolute inset-0 bg-white/50 dark:bg-primary-dark/50 backdrop-blur-[2px] z-10 rounded-[2rem] flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Total Revenue') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total_revenue']) }}</span>
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" class="w-4 h-4 opacity-70 filter dark:invert" alt="SAR">
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['revenue_change'] }}%+</span>
                    </div>
                </div>
                <select x-model="period" @change="updatePeriod($event.target.value)" class="bg-slate-100 dark:bg-indigo-600 border border-slate-200 dark:border-indigo-500 rounded-lg text-xs font-bold px-3 py-1.5 focus:ring-0 text-slate-700 dark:text-gray transition-all hover:bg-slate-200 dark:hover:bg-indigo-500 cursor-pointer shadow-sm">
                    <option value="weekly">{{ __('Weekly') }}</option>
                    <option value="monthly">{{ __('Monthly') }}</option>
                    <option value="yearly">{{ __('Yearly') }}</option>
                </select>
            </div>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Users Chart -->
        <div x-data="{ 
            period: '{{ $stats['users_period'] }}',
            loading: false,
            async updatePeriod(val) {
                this.period = val;
                this.loading = true;
                try {
                    const response = await axios.get('{{ route('admin.dashboard.users') }}', {
                        params: { 
                            period: val,
                            locale: '{{ app()->getLocale() }}'
                        }
                    });
                    const chart = Chart.getChart('usersChart');
                    if (chart) {
                        chart.data.labels = response.data.labels;
                        chart.data.datasets[0].data = response.data.individuals;
                        chart.data.datasets[1].data = response.data.corporate;
                        chart.data.datasets[2].data = response.data.technicians;
                        chart.update('active');
                    }
                } catch (error) {
                    console.error('Users chart update failed:', error);
                } finally {
                    this.loading = false;
                }
            }
        }" class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm relative">
            <div x-show="loading" class="absolute inset-0 bg-white/50 dark:bg-primary-dark/50 backdrop-blur-[2px] z-10 rounded-[2rem] flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Total Users') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-2xl font-black text-slate-800 dark:text-white">{{ \App\Models\User::count() }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#C7D2FE]"></span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ __('Individuals') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#1e1b4b] dark:bg-[#4F46E5]"></span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ __('Corporate') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#818CF8]"></span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ __('Technicians') }}</span>
                        </div>
                    </div>
                    <select x-model="period" @change="updatePeriod($event.target.value)" class="bg-slate-100 dark:bg-indigo-600 border border-slate-200 dark:border-indigo-500 rounded-lg text-xs font-bold px-3 py-1.5 focus:ring-0 text-slate-700 dark:text-gray transition-all hover:bg-slate-200 dark:hover:bg-indigo-500 cursor-pointer shadow-sm">
                        <option value="weekly">{{ __('Weekly') }}</option>
                        <option value="monthly">{{ __('Monthly') }}</option>
                        <option value="yearly">{{ __('Yearly') }}</option>
                    </select>
                </div>
            </div>
            <div class="h-64">
                <canvas id="usersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Lists & Table Section -->
    <div x-data="{ 
        status: 'all',
        loading: false,
        filterOrders(val) {
            this.status = val;
            this.loading = true;
            fetch('{{ route('admin.dashboard.orders') }}?status=' + val)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('orders-table-body').innerHTML = html;
                    this.loading = false;
                });
        }
    }" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Technicians -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm h-fit">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Top Technicians') }}</h3>
                @can('view technicians')
                <a href="{{ route('admin.technicians.top') }}" class="px-3 py-1.5 bg-slate-100 dark:bg-white/10 text-[10px] font-black text-slate-600 dark:text-slate-300 rounded-lg hover:bg-primary hover:text-white transition-all">{{ __('Show All') }}</a>
                @endcan
            </div>
            
            <div class="space-y-4">
                @forelse($topTechnicians as $tech)
                <div class="group p-5 rounded-[2rem] border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/5 hover:bg-white dark:hover:bg-primary-dark/80 hover:shadow-xl hover:shadow-indigo-500/10 hover:border-indigo-500/20 transition-all duration-500 flex items-center justify-between">
                    <div class="flex items-center gap-5">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-600 overflow-hidden flex items-center justify-center text-white text-xl font-black shadow-lg shadow-indigo-500/30 transform group-hover:scale-105 group-hover:rotate-3 transition-all duration-500">
                                @if($tech->image)
                                <img src="{{ asset('storage/'.$tech->image) }}" class="w-full h-full object-cover">
                                @else
                                <span>{{ mb_substr($tech->{'name_'.app()->getLocale()} ?? 'F', 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-[3px] border-white dark:border-[#1A1A31] shadow-sm bg-{{ $tech->availability_status == 'available' ? 'emerald' : 'rose' }}-500 animate-pulse"></div>
                        </div>
                        <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <h4 class="text-sm font-black text-slate-800 dark:text-white mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $tech->{'name_'.app()->getLocale()} }}</h4>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded-lg bg-indigo-500/10 text-indigo-500 text-[9px] font-black uppercase tracking-wider">{{ $tech->category->{'name_'.app()->getLocale()} ?? __('Technician') }}</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-secondary" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    <span class="text-xs font-black text-slate-700 dark:text-white">{{ number_format($tech->reviews()->avg('rating') ?? 0, 1) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-1 h-1 rounded-full bg-slate-300 dark:bg-white/20"></div>
                                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500">{{ $tech->orders_count }} {{ __('Orders') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @can('view technicians')
                    <a href="{{ route('admin.technicians.show', $tech->id) }}" class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 flex items-center justify-center hover:bg-primary hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white transition-all shadow-sm group/btn" title="{{ __('Show Details') }}">
                        <svg class="w-6 h-6 transition-transform group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </a>
                    @endcan
                </div>
                @empty
                <div class="py-12 text-center text-slate-400">{{ __('No technicians found') }}</div>
                @endforelse
            </div>
        </div>

        <div x-data='{
            categoryType: "all",
            labels: {!! json_encode($serviceLabels) !!},
            open: false,
            loading: false,
            updateChart(type) {
                console.log("Updating Categories Chart to:", type);
                this.categoryType = type;
                this.open = false;
                this.loading = true;
                
                fetch("{{ route('admin.dashboard.categories') }}?type=" + type)
                    .then(res => res.json())
                    .then(data => {
                        console.log("Categories data received:", data);
                        this.labels = data.labels;
                        
                        let chart = Chart.getChart("categoryChart");
                        if (!chart) {
                            const canvas = document.getElementById("categoryChart");
                            chart = Chart.getChart(canvas);
                        }
                        
                        if (chart) {
                            chart.data.labels = data.labels;
                            chart.data.datasets[0].data = data.data;
                            chart.update("active");
                        }
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error("Chart update failed:", err);
                        this.loading = false;
                    });
            }
        }' class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm relative">
            <div x-show="loading" class="absolute inset-0 bg-white/50 dark:bg-primary-dark/50 backdrop-blur-[2px] z-10 rounded-[2rem] flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>

            <div class="flex items-center justify-between mb-8">
                <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Service Categories') }}</h3>
                
                <div class="relative">
                    <button @click="open = !open" class="flex items-center gap-3 px-4 py-2 bg-slate-100 dark:bg-indigo-900 border border-slate-200 dark:border-indigo-500 rounded-xl text-xs font-bold text-slate-700 dark:text-gray shadow-sm hover:border-primary/50 dark:hover:bg-indigo-500 transition-all min-w-[140px] justify-between">
                        <span x-text="categoryType === 'individual' ? '{{ __('Individuals') }}' : (categoryType === 'corporate_customer' ? '{{ __('Corporate') }}' : '{{ __('All Categories') }}')"></span>
                        <svg class="w-3 h-3 transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-[#1e1b4b] border border-slate-100 dark:border-white/10 rounded-2xl shadow-xl z-20 py-2">
                        <button @click="updateChart('all')" class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} px-4 py-2.5 text-xs font-bold hover:bg-slate-50 dark:hover:bg-white/10 transition-colors" :class="categoryType === 'all' ? 'text-primary' : 'text-slate-600 dark:text-slate-300'">
                            {{ __('All Categories') }}
                        </button>
                        <button @click="updateChart('individual')" class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} px-4 py-2.5 text-xs font-bold hover:bg-slate-50 dark:hover:bg-white/10 transition-colors" :class="categoryType === 'individual' ? 'text-primary' : 'text-slate-900 dark:text-slate-900'">
                            {{ __('Individuals') }}
                        </button>
                        <button @click="updateChart('corporate_customer')" class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} px-4 py-2.5 text-xs font-bold hover:bg-slate-50 dark:hover:bg-white/10 transition-colors" :class="categoryType === 'corporate_customer' ? 'text-primary' : 'text-slate-600 dark:text-slate-900'">
                            {{ __('Corporate') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="h-64 relative">
                <canvas id="categoryChart"></canvas>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mt-8">
                <template x-for="(label, index) in labels" :key="index">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" :style="'background-color: ' + ['#1e1b4b', '#4F46E5', '#818CF8', '#C7D2FE', '#E0E7FF'][index % 5]"></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-300" x-text="label"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table FULL WIDTH -->
    <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm mt-8">
                    <h3 class="text-lg mb-2 sm:text-xl font-black text-slate-800 dark:text-white whitespace-nowrap">{{ __('Orders') }}</h3>

        <div class="flex items-center justify-between mb-8 gap-4 flex-wrap sm:flex-nowrap">
            @can('view orders')
            <a href="{{ route('admin.orders.index') }}" 
            class="px-6 py-3 bg-[#1A1A31] text-white text-xs font-black
             rounded-xl hover:opacity-90 dark:border dark:border-4 dark:border-white/10
             transition-all shadow-lg shadow-secondary/20 whitespace-nowrap">
                {{ __('Show More') }}
            </a>
            @endcan
            <div class="flex items-center gap-4 sm:gap-6 flex-1 justify-end">
                <div class="flex items-center p-1 bg-slate-50 dark:bg-white/5 rounded-2xl overflow-x-auto no-scrollbar max-w-[180px] xs:max-w-none">
                    <button @click="filterOrders('new')" :class="status == 'new' ? 'bg-white dark:bg-white/10 shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-4 sm:px-5 py-2 rounded-xl text-[11px] sm:text-xs transition-all whitespace-nowrap">{{ __('New') }}</button>
                    <button @click="filterOrders('scheduled')" :class="status == 'scheduled' ? 'bg-white dark:bg-white/10 shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-4 sm:px-5 py-2 rounded-xl text-[11px] sm:text-xs transition-all whitespace-nowrap">{{ __('Scheduled') }}</button>
                    <button @click="filterOrders('in_progress')" :class="status == 'in_progress' ? 'bg-white dark:bg-white/10 shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-4 sm:px-5 py-2 rounded-xl text-[11px] sm:text-xs transition-all whitespace-nowrap">{{ __('In Progress') }}</button>
                    <button @click="filterOrders('completed')" :class="status == 'completed' ? 'bg-white dark:bg-white/10 shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-4 sm:px-5 py-2 rounded-xl text-[11px] sm:text-xs transition-all whitespace-nowrap">{{ __('Completed') }}</button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto relative min-h-[400px]">
            <div x-show="loading" class="absolute inset-0 bg-white/50 dark:bg-primary-dark/50 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-xs font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="pb-4 px-2">#</th>
                        <th class="pb-4 px-2">{{ __('Order Number') }}</th>
                        <th class="pb-4 px-2">{{ __('Customer Name') }}</th>
                        <th class="pb-4 px-2">{{ __('Customer Type') }}</th>
                        <th class="pb-4 px-2">{{ __('Service Name') }}</th>
                        <th class="pb-4 px-2">{{ __('Service Type') }}</th>
                        <th class="pb-4 px-2">{{ __('Address') }}</th>
                        <th class="pb-4 px-2">{{ __('Date/Time') }}</th>
                        <th class="pb-4 px-2">{{ __('Price') }}</th>
                        <th class="pb-4 px-2 text-center" x-show="status == 'new' || status == 'all'">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body" class="text-xs font-bold">
                    @include('admin.dashboard-orders-table', ['recent_orders' => $recent_orders, 'status' => $status])
                </tbody>
            </table>
        </div>
    </div>

    <!-- ACCEPT ORDER MODAL -->
    <div x-show="showAcceptModal" x-cloak 
         class="fixed inset-0 z-[200] flex items-center justify-center p-6 bg-[#1A1A31]/40 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        
        <div class="bg-white dark:bg-[#1A1A31] rounded-[3rem] w-full max-w-2xl shadow-2xl relative overflow-hidden"
             @click.away="showAcceptModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <!-- Close Button -->
            <button @click="showAcceptModal = false" class="absolute top-8 left-8 text-slate-300 hover:text-slate-500 dark:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="p-12 text-center space-y-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Accept Order') }}</h2>
                    <button @click="toggleViewMode()" class="px-6 py-2 rounded-xl border border-slate-100 dark:border-white/10 font-bold text-xs text-[#1A1A31] dark:text-white hover:bg-slate-50 dark:hover:bg-white/5 transition-all flex items-center gap-2">
                        <template x-if="viewMode === 'list'">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ __('Show Map') }}
                            </span>
                        </template>
                        <template x-if="viewMode === 'map'">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                {{ __('Show List') }}
                            </span>
                        </template>
                    </button>
                </div>

                <!-- LIST VIEW -->
                <div x-show="viewMode === 'list'" class="space-y-8">
                    <!-- Tabs -->
                    <div class="flex p-1.5 bg-slate-50 dark:bg-white/5 rounded-2xl">
                        <button @click="activeTab = 'platform'; fetchTechnicians()" 
                                :class="activeTab === 'platform' ? 'bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] shadow-lg' : 'text-slate-400'"
                                class="flex-1 py-3 rounded-xl font-bold text-md transition-all whitespace-nowrap px-4">
                            {{ __('Assign Platform Technician') }}
                        </button>
                        <button @click="activeTab = 'company'; fetchCompanies()" 
                                :class="activeTab === 'company' ? 'bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] shadow-lg' : 'text-slate-400'"
                                class="flex-1 py-3 rounded-xl font-bold text-md transition-all whitespace-nowrap px-4">
                            {{ __('Send Order to Maintenance Company') }}
                        </button>
                    </div>

                    <p class="text-slate-400 font-bold text-md leading-relaxed" x-text="activeTab === 'platform' ? '{{ __('Select an available technician to perform the maintenance request') }}' : '{{ __('Select an available maintenance company to perform the request') }}'"></p>

                    <!-- List Section -->
                    <div class="space-y-6 text-right">
                        <div class="flex items-center justify-between">
                            <h3 class="text-md font-black text-[#1A1A31] dark:text-white" x-text="activeTab === 'platform' ? '{{ __('Technicians') }}' : '{{ __('Maintenance Companies') }}'"></h3>
                        </div>

                        <!-- Items Container -->
                        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            <template x-if="(activeTab === 'platform' && loadingTechs) || (activeTab === 'company' && loadingCompanies)">
                                <div class="py-10 text-center">
                                    <div class="inline-block w-8 h-8 border-4 border-[#1A1A31] border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            </template>

                            <template x-if="activeTab === 'platform' && !loadingTechs">
                                <div class="space-y-4">
                                    <template x-for="tech in technicians" :key="tech.id">
                                        <label class="flex items-center gap-6 p-6 rounded-[2rem] border-2 transition-all cursor-pointer group"
                                               :class="selectedTechId === tech.id ? 'border-primary bg-primary/5 shadow-sm' : 'border-slate-50 dark:border-white/5 hover:border-slate-100 dark:hover:border-white/10'">
                                            <div class="flex-1 flex items-center gap-4">
                                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 dark:bg-white/5">
                                                    <img :src="tech.avatar || '/assets/admin/images/avatar-placeholder.png'" class="w-full h-full object-cover">
                                                </div>
                                                <div class="space-y-1">
                                                    <h4 class="font-black text-[#1A1A31] dark:text-white transition-colors" x-text="tech.name"></h4>
                                                    <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400">
                                                        <span x-text="tech.service_name"></span>
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                            <span x-text="`{{ __('Rating:') }} ${tech.rating}`"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                                 :class="selectedTechId === tech.id ? 'border-primary bg-primary' : 'border-slate-200 group-hover:border-primary/50'">
                                                <input type="radio" x-model="selectedTechId" :value="tech.id" class="hidden">
                                                <svg x-show="selectedTechId === tech.id" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </template>

                            <template x-if="activeTab === 'company' && !loadingCompanies">
                                <div class="space-y-4">
                                    <template x-for="comp in companies" :key="comp.id">
                                        <label class="flex items-center gap-6 p-6 rounded-[2rem] border-2 transition-all cursor-pointer group"
                                               :class="selectedCompanyId === comp.id ? 'border-primary bg-primary/5 shadow-sm' : 'border-slate-50 dark:border-white/5 hover:border-slate-100 dark:hover:border-white/10'">
                                            <div class="flex-1 flex items-center gap-4">
                                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 dark:bg-white/5">
                                                    <img :src="comp.avatar || '/assets/admin/images/avatar-placeholder.png'" class="w-full h-full object-cover">
                                                </div>
                                                <div class="space-y-1">
                                                    <h4 class="font-black text-[#1A1A31] dark:text-white transition-colors" x-text="comp.name"></h4>
                                                    <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400">
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                            <span x-text="`{{ __('Rating:') }} ${comp.rating}`"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                                 :class="selectedCompanyId === comp.id ? 'border-primary bg-primary' : 'border-slate-200 group-hover:border-primary/50'">
                                                <input type="radio" x-model="selectedCompanyId" :value="comp.id" class="hidden">
                                                <svg x-show="selectedCompanyId === comp.id" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="activeTab === 'platform' ? fetchTechnicians(true) : fetchCompanies(true)" class="w-full py-4 border-2 border-slate-100 dark:border-white/10 rounded-2xl font-black text-xs text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                            {{ __('Show More') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- MAP VIEW -->
                <div x-show="viewMode === 'map'" class="relative" style="min-height: 500px;" x-effect="if(searchMap !== undefined) renderMarkers()">
                    <div class="absolute top-6 right-6 z-[1000] flex gap-3 pointer-events-auto">
                        <button @click="viewMode = 'list'" class="w-10 h-10 rounded-xl bg-white/90 dark:bg-[#1A1A31]/90 backdrop-blur-md border border-slate-100 dark:border-white/10 shadow-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="absolute top-6 left-6 right-20 z-[1000] flex gap-4 pointer-events-none">
                        <div class="flex-1 max-w-sm pointer-events-auto">
                            <div class="relative">
                                <input type="text" x-model="searchMap" @input="renderMarkers()" placeholder="{{ __('Search...') }}" class="w-full h-12 pr-12 pl-4 bg-white/90 dark:bg-[#1A1A31]/90 backdrop-blur-md border border-slate-100 dark:border-white/10 rounded-2xl shadow-lg focus:outline-none font-bold text-md text-[#1A1A31] dark:text-white">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tech-map" class="z-[1]"></div>
                </div>

                <!-- Footer Actions -->
                <div class="flex gap-4 pt-6">
                    <form :action="`/admin/orders/${selectedOrder}/accept`" method="POST" class="flex-1">
                        @csrf
                        <template x-if="activeTab === 'platform'">
                            <input type="hidden" name="technician_id" :value="selectedTechId">
                        </template>
                        <template x-if="activeTab === 'company'">
                            <input type="hidden" name="maintenance_company_id" :value="selectedCompanyId">
                        </template>
                        <button type="submit" 
                                :disabled="(activeTab === 'platform' && !selectedTechId) || (activeTab === 'company' && !selectedCompanyId)"
                                :class="(activeTab === 'platform' && selectedTechId) || (activeTab === 'company' && selectedCompanyId) ? 'bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] shadow-xl shadow-[#1A1A31]/20 hover:scale-[1.02]' : 'bg-slate-300 dark:bg-white/10 dark:text-slate-500 cursor-not-allowed'"
                                class="w-full py-5 rounded-[1.5rem] font-black text-md transition-all transform uppercase tracking-widest">
                            <span x-text="activeTab === 'platform' ? '{{ __('Send assignment to technician') }}' : '{{ __('Send order to company') }}'"></span>
                        </button>
                    </form>
                    <button @click="showAcceptModal = false" class="flex-[0.5] py-5 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-300 rounded-[1.5rem] font-bold text-md hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- REFUSE ORDER MODAL -->
    <div x-show="showRefuseModal" x-cloak 
         class="fixed inset-0 z-[200] flex items-center justify-center p-6 bg-[#1A1A31]/40 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        
        <div class="bg-white dark:bg-[#1A1A31] rounded-[3rem] w-full max-w-xl shadow-2xl relative overflow-hidden"
             @click.away="showRefuseModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <button @click="showRefuseModal = false" class="absolute top-8 left-8 text-slate-300 hover:text-slate-500 dark:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="p-12 text-center space-y-8">
                <div class="w-20 h-20 bg-red-50 rounded-[2rem] flex items-center justify-center mx-auto text-red-500">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>

                <div class="space-y-2">
                    <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Refuse Order') }}</h2>
                    <p class="text-slate-400 font-bold text-md">{{ __('Please state the reason for rejection to clarify to the customer') }}</p>
                </div>

                <form :action="`/admin/orders/${selectedOrder}/destroy`" method="POST" class="space-y-8">
                    @csrf
                    @method('DELETE')
                    <textarea name="rejection_reason" x-model="rejectionReason" required
                              placeholder="{{ __('Write the rejection reason here...') }}"
                              class="w-full h-40 p-6 bg-slate-50 dark:bg-white/5 border-none rounded-[2rem] focus:ring-2 focus:ring-red-500/20 transition-all font-bold text-md text-[#1A1A31] dark:text-white resize-none"></textarea>

                    <div class="flex gap-4">
                        <button type="submit" 
                                :disabled="!rejectionReason.trim()"
                                :class="rejectionReason.trim() ? 'bg-red-500 shadow-xl shadow-red-500/20 hover:scale-[1.02]' : 'bg-slate-300 cursor-not-allowed'"
                                class="flex-1 py-5 text-white rounded-[1.5rem] font-black text-md transition-all transform capitalize tracking-widest">
                            {{ __('Confirm Rejection') }}
                        </button>
                        <button type="button" @click="showRefuseModal = false" class="flex-[0.5] py-5 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-300 rounded-[1.5rem] font-bold text-md hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    Chart.defaults.font.family = 'Cairo';
    Chart.defaults.color = 'rgba(0, 0, 0, 0.4)';
    const isDark = document.documentElement.classList.contains('dark');
    if(isDark) Chart.defaults.color = 'rgba(255, 255, 255, 0.7)';

    const commonLineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { display: false }, y: { display: false } },
        elements: { point: { radius: 0 }, line: { tension: 0.4, borderWidth: 2 } }
    };

    // Sparklines
    const sparklineData = {
        sparkline1: { 
            color: '{{ $stats['available_change'] >= 0 ? "#10B981" : "#EF4444" }}',
            data: [{{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}]
        },
        sparkline2: { 
            color: '{{ $stats['new_orders_change'] >= 0 ? "#10B981" : "#EF4444" }}',
            data: [{{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}]
        },
        sparkline3: { 
            color: '{{ $stats['revenue_change'] >= 0 ? "#10B981" : "#EF4444" }}',
            data: [{{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}]
        }
    };

    Object.keys(sparklineData).forEach(id => {
        const config = sparklineData[id];
        new Chart(document.getElementById(id).getContext('2d'), {
            type: 'line',
            data: {
                labels: Array(config.data.length).fill(''),
                datasets: [{
                    data: config.data,
                    borderColor: config.color,
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, 'transparent');
                        gradient.addColorStop(1, config.color + '20');
                        return gradient;
                    },
                    tension: 0.4
                }]
            },
            options: commonLineOptions
        });
    });

    // Quality Doughnut (Tiny)
    new Chart(document.getElementById('qualityDoughnut').getContext('2d'), {
        type: 'doughnut',
        data: {
            datasets: [{ 
                data: [{{ $stats['avg_quality'] }}, {{ 5 - $stats['avg_quality'] }}], 
                backgroundColor: ['#10B981', isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)'], 
                borderWidth: 0 
            }]
        },
        options: { cutout: '80%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
    });

    // Main Revenue Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($revenueLabels) !!},
            datasets: [{ 
                data: {!! json_encode($revenueChart) !!}, 
                backgroundColor: isDark ? '#4F46E5' : '#1e1b4b',
                borderRadius: 10,
                barThickness: 28
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    backgroundColor: isDark ? '#1e1b4b' : '#1e1b4b',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    borderRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '# ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: { 
                    position: '{{ app()->getLocale() == "ar" ? "left" : "right" }}',
                    grid: { 
                        borderDash: [5, 5], 
                        color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' 
                    }, 
                    border: { display: false }, 
                    ticks: { color: isDark ? 'rgba(255,255,255,0.5)' : '#94a3b8' } 
                },
                x: { 
                    grid: { display: false }, 
                    border: { display: false }, 
                    ticks: { color: isDark ? 'rgba(255,255,255,0.7)' : '#64748b' } 
                }
            }
        }
    });

    // Users Chart - Grouped Bar Chart
    new Chart(document.getElementById('usersChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($usersLabels) !!},
            datasets: [
                { 
                    label: '{{ __('Individuals') }}',
                    data: {!! json_encode($individualsData) !!}, 
                    backgroundColor: isDark ? '#A5B4FC' : '#C7D2FE',
                    borderRadius: 10,
                    barThickness: 16
                },
                { 
                    label: '{{ __('Corporate') }}',
                    data: {!! json_encode($corporateData) !!}, 
                    backgroundColor: isDark ? '#4F46E5' : '#1e1b4b',
                    borderRadius: 10,
                    barThickness: 16
                },
                { 
                    label: '{{ __('Technicians') }}',
                    data: {!! json_encode($techniciansData) !!}, 
                    backgroundColor: isDark ? '#818CF8' : '#818CF8',
                    borderRadius: 10,
                    barThickness: 16
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    backgroundColor: isDark ? '#1e1b4b' : '#1e1b4b',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    borderRadius: 8,
                    displayColors: true,
                    boxWidth: 8,
                    boxHeight: 8,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: { 
                    position: '{{ app()->getLocale() == "ar" ? "left" : "right" }}',
                    grid: { 
                        borderDash: [5, 5], 
                        color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' 
                    }, 
                    border: { display: false }, 
                    ticks: { color: isDark ? 'rgba(255,255,255,0.5)' : '#94a3b8' } 
                },
                x: { 
                    grid: { display: false }, 
                    border: { display: false }, 
                    ticks: { color: isDark ? 'rgba(255,255,255,0.7)' : '#64748b' } 
                }
            }
        }
    });

    // Services Doughnut
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($serviceLabels) !!},
            datasets: [{ 
                data: {!! json_encode($serviceData) !!}, 
                backgroundColor: ['#1e1b4b', '#4F46E5', '#818CF8', '#C7D2FE', '#E0E7FF'],
                borderWidth: 0
            }]
        },
        options: { 
            cutout: '75%', 
            plugins: { legend: { display: false } },
            responsive: true,
            maintainAspectRatio: false
        }
    });
});

document.addEventListener('alpine:init', () => {
    Alpine.data('orderManagement', () => ({
        showAcceptModal: false,
        showRefuseModal: false,
        selectedOrder: null,
        status: 'new',
        loading: false,
        viewMode: 'list',
        map: null,
        markers: [],
        selectedMapTech: null,
        searchMap: '',
        technicians: [],
        companies: [],
        loadingTechs: false,
        loadingCompanies: false,
        activeTab: 'platform',
        selectedTechId: null,
        selectedCompanyId: null,
        rejectionReason: '',
        categories: @json($categories),

        filterOrders(val) {
            this.status = val;
            this.loading = true;
            fetch('{{ route('admin.dashboard.orders') }}?status=' + val)
                .then(res => res.text())
                .then(html => {
                    const body = document.getElementById('orders-table-body');
                    body.innerHTML = html;
                    this.loading = false;
                    this.$nextTick(() => {
                        Alpine.initTree(body);
                    });
                });
        },

        async openAcceptModal(orderId) {
            console.log('Opening accept modal for:', orderId);
            this.selectedOrder = orderId;
            this.showAcceptModal = true;
            this.selectedTechId = null;
            this.selectedCompanyId = null;
            this.activeTab = 'platform';
            this.viewMode = 'list';
            await this.fetchTechnicians();
        },

        async toggleViewMode() {
            this.viewMode = this.viewMode === 'list' ? 'map' : 'list';
            if (this.viewMode === 'map') {
                this.$nextTick(() => this.initMap());
            }
        },

        initMap() {
            if (!this.map) {
                this.map = L.map('tech-map', {
                    zoomControl: false,
                    attributionControl: false
                }).setView([24.7136, 46.6753], 12);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
            }
            this.renderMarkers();
        },

        renderMarkers() {
            if (!this.map) return;
            this.markers.forEach(m => this.map.removeLayer(m));
            this.markers = [];
            let data = this.activeTab === 'platform' ? this.technicians : this.companies;
            if (this.searchMap.trim()) {
                const query = this.searchMap.toLowerCase();
                data = data.filter(item => item.name.toLowerCase().includes(query));
            }
            data.forEach(item => {
                if (item.lat && item.lng) {
                    const marker = L.marker([item.lat, item.lng]).addTo(this.map)
                        .on('click', () => { this.selectedMapTech = item; });
                    this.markers.push(marker);
                }
            });
        },

        async fetchTechnicians() {
            this.loadingTechs = true;
            try {
                const res = await fetch(`/admin/orders/${this.selectedOrder}/available-technicians`);
                const result = await res.json();
                if (result.status) this.technicians = result.data;
            } finally { this.loadingTechs = false; }
        },

        async fetchCompanies() {
            this.loadingCompanies = true;
            try {
                const res = await fetch(`/admin/orders/${this.selectedOrder}/available-companies`);
                const result = await res.json();
                if (result.status) this.companies = result.data;
            } finally { this.loadingCompanies = false; }
        },

        openRefuseModal(orderId) {
            console.log('Opening refuse modal for:', orderId);
            this.selectedOrder = orderId;
            this.showRefuseModal = true;
            this.rejectionReason = '';
        }
    }));
});
</script>
@endsection
