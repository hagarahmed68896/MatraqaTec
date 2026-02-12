@extends('layouts.admin')

@section('title', __('Admin Dashboard') . ' - ' . __('MatraqaTec'))
@section('page_title', __('Admin Dashboard'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    
    <!-- Dashboard Heading -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Admin Dashboard') }}</h2>
    </div>

    <!-- Stats Grid: Mirroring Screenshot -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- Available Technicians -->
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-slate-400 dark:text-slate-300 text-xs font-bold mb-1">{{ __('Total Available Technicians') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ $stats['available_techs'] }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['available_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['available_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['available_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['available_change'] }}%+</span>
                    </div>
                </div>
                <div class="w-16 h-10">
                    <canvas id="sparkline1"></canvas>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium">{{ __('Compared to last week') }}</p>
        </div>

        <!-- New Orders -->
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-slate-400 dark:text-slate-300 text-xs font-bold mb-1">{{ __('New Orders') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ $stats['new_orders'] }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['new_orders_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['new_orders_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['new_orders_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['new_orders_change'] }}%+</span>
                    </div>
                </div>
                <div class="w-16 h-10">
                    <canvas id="sparkline2"></canvas>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium">{{ __('Compared to last week') }}</p>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-slate-400 dark:text-slate-300 text-xs font-bold mb-1">{{ __('Total Revenue') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total_revenue']) }}</h3>
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" class="w-4 h-4 opacity-70 filter dark:invert" alt="SAR">
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['revenue_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['revenue_change'] }}%+</span>
                    </div>
                </div>
                <div class="w-16 h-10">
                    <canvas id="sparkline3"></canvas>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium">{{ __('Compared to last week') }}</p>
        </div>

        <!-- Avg Quality -->
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-3xl border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-slate-400 dark:text-slate-300 text-xs font-bold mb-1">{{ __('Average Quality') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ $stats['avg_quality'] }}/5</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $stats['quality_change'] >= 0 ? 'green' : 'red' }}-500/10 text-{{ $stats['quality_change'] >= 0 ? 'green' : 'red' }}-500 font-bold border border-{{ $stats['quality_change'] >= 0 ? 'green' : 'red' }}-500/20">{{ $stats['quality_change'] }}%+</span>
                    </div>
                </div>
                <div class="relative w-12 h-12">
                    <canvas id="qualityDoughnut"></canvas>
                    <span class="absolute inset-0 flex items-center justify-center text-[8px] font-black text-slate-800 dark:text-white">{{ $stats['avg_quality'] }}/5</span>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium">{{ __('Compared to last week') }}</p>
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
                    <h3 class="text-lg font-black text-slate-800 dark:text-gray">{{ __('Total Users') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-2xl font-black text-slate-800 dark:text-gray">{{ \App\Models\User::count() }}</span>
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
                <a href="{{ route('admin.technicians.top') }}" class="px-3 py-1.5 bg-slate-100 dark:bg-white/10 text-[10px] font-black text-slate-600 dark:text-slate-300 rounded-lg hover:bg-primary hover:text-white transition-all">{{ __('Show All') }}</a>
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
                                    <span class="text-xs font-black text-slate-700 dark:text-white/80">{{ number_format($tech->reviews()->avg('rating') ?? 0, 1) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-1 h-1 rounded-full bg-slate-300 dark:bg-white/20"></div>
                                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500">{{ $tech->orders_count }} {{ __('Orders') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.technicians.show', $tech->id) }}" class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 flex items-center justify-center hover:bg-primary hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white transition-all shadow-sm group/btn" title="{{ __('Show Details') }}">
                        <svg class="w-6 h-6 transition-transform group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </a>
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
    }" class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm mt-8">
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('admin.orders.index') }}" class="px-6 py-3 bg-secondary text-white text-xs font-black rounded-xl hover:opacity-90 transition-all shadow-lg shadow-secondary/20">
                {{ __('Show More') }}
            </a>
            <div class="flex items-center gap-6">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Orders') }}</h3>
                <div class="flex items-center p-1 bg-slate-50 dark:bg-white/5 rounded-2xl">
                    <button @click="filterOrders('new')" :class="status == 'new' ? 'bg-white dark:bg-white/10 shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('New') }}</button>
                    <button @click="filterOrders('scheduled')" :class="status == 'scheduled' ? 'bg-white dark:bg-white/10 shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('Scheduled') }}</button>
                    <button @click="filterOrders('in_progress')" :class="status == 'in_progress' ? 'bg-white dark:bg-white/10 shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('In Progress') }}</button>
                    <button @click="filterOrders('completed')" :class="status == 'completed' ? 'bg-white dark:bg-white/10 shadow-sm text-slate-900 dark:text-white' : 'text-slate-400 font-bold'" class="px-5 py-2 rounded-xl text-xs transition-all">{{ __('Completed') }}</button>
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
                        <th class="pb-4 px-2 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body" class="text-xs font-bold">
                    @include('admin.dashboard-orders-table', ['recent_orders' => $recent_orders])
                </tbody>
            </table>
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
    ['sparkline1', 'sparkline2', 'sparkline3'].forEach(id => {
        new Chart(document.getElementById(id), {
            type: 'line',
            data: {
                labels: [1,2,3,4,5,6],
                datasets: [{ data: [10, 15, 8, 20, 12, 25], borderColor: '#10B981', fill: false }]
            },
            options: commonLineOptions
        });
    });

    // Quality Doughnut (Tiny)
    new Chart(document.getElementById('qualityDoughnut'), {
        type: 'doughnut',
        data: {
            datasets: [{ 
                data: [{{ $stats['avg_quality'] }}, {{ 5 - $stats['avg_quality'] }}], 
                backgroundColor: ['#10B981', 'rgba(0,0,0,0.1)'], 
                borderWidth: 0 
            }]
        },
        options: { cutout: '80%', plugins: { legend: { display: false } } }
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
</script>
@endsection
