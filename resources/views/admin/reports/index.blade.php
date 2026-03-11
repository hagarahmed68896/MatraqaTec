@extends('layouts.admin')

@section('title', __('Reports Dashboard'))

@section('content')
<div class="space-y-8 pb-12" dir="rtl">
    <!-- Top Filter Bar -->
    <form action="{{ route('admin.reports.index') }}" method="GET" id="filterForm" class="flex items-center justify-between gap-4">
                <h1 class="text-3xl font-black text-[#1A1A31] dark:text-white tracking-tight">{{ __('Reports') }}</h1>

        <div class="flex items-center gap-3">
          
            <!-- Category Filter -->
            <div x-data="{ 
                open: false, 
                selected: '{{ $data['filters']['category_id'] ? $data['filters']['all_categories']->where('id', $data['filters']['category_id'])->first()->{'name_'.app()->getLocale()} : __('All Categories') }}' 
            }" class="relative">
                <button type="button" @click="open = !open" class="h-11 px-5 rounded-2xl bg-white dark:bg-[#1A1A31] border border-slate-50 dark:border-white/5 shadow-sm flex items-center gap-3 text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-primary transition-all">
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    <span x-text="selected"></span>
                </button>
                <input type="hidden" name="category_id" x-ref="categoryId" value="{{ $data['filters']['category_id'] }}">
                <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white dark:bg-[#1A1A31] rounded-2xl border border-slate-100 dark:border-white/10 shadow-xl overflow-hidden z-30">
                    <button type="button" @click="selected = '{{ __('All Categories') }}'; $refs.categoryId.value = ''; $el.closest('form').submit(); open = false" class="w-full px-5 py-3 text-right text-xs font-bold hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-colors">{{ __('All Categories') }}</button>
                    @foreach($data['filters']['all_categories'] as $category)
                    <button type="button" @click="selected = '{{ $category->{'name_'.app()->getLocale()} }}'; $refs.categoryId.value = '{{ $category->id }}'; $el.closest('form').submit(); open = false" class="w-full px-5 py-3 text-right text-xs font-bold hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-colors">{{ $category->{'name_'.app()->getLocale()} }}</button>
                    @endforeach
                </div>
            </div>
            <!-- Consolidated Date Range Pill -->
            <div class="flex items-center bg-white dark:bg-[#1A1A31] border border-slate-50 dark:border-white/5 rounded-2xl px-3 h-11 shadow-sm gap-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    <div class="flex items-center gap-1 text-[11px] font-bold text-[#1A1A31] dark:text-white whitespace-nowrap">
                        <span>{{ \Carbon\Carbon::parse($data['filters']['date_from'])->locale('ar')->translatedFormat('j F Y') }}</span>
                        <span class="text-slate-300">-</span>
                        <span>{{ \Carbon\Carbon::parse($data['filters']['date_to'])->locale('ar')->translatedFormat('j F Y') }}</span>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-[#1A1A31] flex items-center justify-center text-white relative cursor-pointer overflow-hidden">
                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <input type="date" name="date_from" value="{{ $data['filters']['date_from'] }}" onchange="this.form.submit()" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>
            </div>

        
              <!-- Download Button -->
            <a href="{{ route('admin.reports.download', request()->query()) }}" class="h-11 px-6 rounded-2xl bg-[#1A1A31] text-white flex items-center gap-3 text-xs font-black shadow-lg shadow-[#1A1A31]/20 hover:bg-[#252545] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                {{ __('Download') }}
            </a>


            <input type="hidden" name="user_type" value="{{ $data['filters']['user_type'] }}">
            <input type="hidden" name="revenue_type" value="{{ $data['filters']['revenue_type'] }}">
            <input type="hidden" name="settlement_type" value="{{ $data['filters']['settlement_type'] }}">
            <input type="hidden" name="categories_type" value="{{ $data['filters']['categories_type'] }}">
            <input type="hidden" name="tech_type" value="{{ $data['filters']['tech_type'] }}">
            <input type="hidden" name="date_to" value="{{ $data['filters']['date_to'] }}">
        </div>

    </form>

    <!-- Charts Grid Layer 1: Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Trends Chart -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Total Users') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-black text-primary">{{ number_format($data['users']['total']) }}</span>
                        <span class="text-[10px] font-bold text-green-500 bg-green-50 dark:bg-green-500/10 px-2 py-0.5 rounded-full">+12.5%</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#1A1A31]"></span>
                        <span class="text-[10px] font-bold text-slate-400">{{ __('Active') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                        <span class="text-[10px] font-bold text-slate-400">{{ __('Blocked') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-100"></span>
                        <span class="text-[10px] font-bold text-slate-400">{{ __('New') }}</span>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="usersTrendChart"></canvas>
            </div>
        </div>

        <!-- City Distribution Chart -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('User Distribution by City') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-black text-primary">{{ number_format($data['users']['total']) }}</span>
                        <span class="text-[10px] font-bold text-slate-400">{{ __('Total Users') }}</span>
                    </div>
                </div>
                <div></div>
            </div>
            <div class="h-64">
                <canvas id="cityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Grid Layer 2: Revenue & Settlements -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Chart -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Total Revenue') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-black text-[#1A1A31] dark:text-white text-xl">{{ number_format($data['financials']['total_revenue']) }} <span class="text-xs font-bold text-slate-400">ريال</span></span>
                        <span class="text-[10px] font-bold text-green-500 bg-green-50 dark:bg-green-500/10 px-2 py-0.5 rounded-full">+12.5%</span>
                    </div>
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="h-8 px-3 rounded-xl border border-slate-100 dark:border-white/5 text-[10px] font-bold text-slate-400 flex items-center gap-2">
                        @php
                            $revLabel = [
                                'individual' => __('Individual Customers'),
                                'corporate_customer' => __('Corporate Customers'),
                            ][$data['filters']['revenue_type']] ?? __('All Customers');
                        @endphp
                        <span>{{ $revLabel }}</span>
                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-32 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-50 shadow-xl overflow-hidden z-30">
                        <button type="button" @click="document.querySelector('input[name=revenue_type]').value = ''; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('All Customers') }}</button>
                        <button type="button" @click="document.querySelector('input[name=revenue_type]').value = 'individual'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Individual Customers') }}</button>
                        <button type="button" @click="document.querySelector('input[name=revenue_type]').value = 'corporate_customer'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Corporate Customers') }}</button>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Settlements Chart -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Total Settlements') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-black text-[#1A1A31] dark:text-white text-xl">{{ number_format($data['financials']['total_settlements']) }} <span class="text-xs font-bold text-slate-400">ريال</span></span>
                        <span class="text-[10px] font-bold text-green-500 bg-green-50 dark:bg-green-500/10 px-2 py-0.5 rounded-full">+12.5%</span>
                    </div>
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="h-8 px-3 rounded-xl border border-slate-100 dark:border-white/5 text-[10px] font-bold text-slate-400 flex items-center gap-2">
                        @php
                            $setLabel = [
                                'company' => __('Companies'),
                                'technician' => __('Technicians'),
                            ][$data['filters']['settlement_type']] ?? __('All');
                        @endphp
                        <span>{{ $setLabel }}</span>
                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-32 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-50 shadow-xl overflow-hidden z-30">
                        <button type="button" @click="document.querySelector('input[name=settlement_type]').value = ''; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('All') }}</button>
                        <button type="button" @click="document.querySelector('input[name=settlement_type]').value = 'company'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Companies') }}</button>
                        <button type="button" @click="document.querySelector('input[name=settlement_type]').value = 'technician'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Technicians') }}</button>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="settlementsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Grid Layer 3: Categories & Quality -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Categories Chart -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Service Categories') }}</h3>
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="h-8 px-3 rounded-xl border border-slate-100 dark:border-white/5 text-[10px] font-bold text-slate-400 flex items-center gap-2">
                        @php
                            $catLabel = [
                                'individual' => __('Clients'),
                                'corporate_customer' => __('Companies'),
                            ][$data['filters']['categories_type']] ?? __('All');
                        @endphp
                        <span>{{ $catLabel }}</span>
                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-32 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-50 shadow-xl overflow-hidden z-30">
                        <button type="button" @click="document.querySelector('input[name=categories_type]').value = ''; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('All') }}</button>
                        <button type="button" @click="document.querySelector('input[name=categories_type]').value = 'individual'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Clients') }}</button>
                        <button type="button" @click="document.querySelector('input[name=categories_type]').value = 'corporate_customer'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Companies') }}</button>
                    </div>
                </div>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="categoriesChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-2">
                    <span class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ $data['categories']['total_orders'] }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Orders') }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-y-3 mt-6">
                @foreach($data['categories']['labels'] as $index => $label)
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ ['#1A1A31', '#7C7C9B', '#B8B8CB', '#E2E2EB'][$index % 4] }}"></span>
                    <span class="text-[10px] font-bold text-slate-400">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Service Quality / Gauge -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-8">{{ __('Service Level') }}</h3>
            
            <div class="flex flex-col items-center">
                <div class="relative w-48 h-24 mb-6">
                    <canvas id="gaugeChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-end justify-center pt-8">
                        <span class="text-4xl font-black text-[#1A1A31] dark:text-white">{{ $data['service_quality']['level'] }}%</span>
                        <p class="text-[10px] font-bold text-slate-400 mt-1">{{ __('Based on customer feedback') }}</p>
                    </div>
                </div>

                <div class="w-full space-y-3 mt-4">
                    @php $stars = [5, 4, 3, 2, 1]; @endphp
                    @foreach($data['service_quality']['breakdown'] as $index => $count)
                    <div class="flex items-center gap-4">
                        <span class="text-[10px] font-bold text-slate-400 w-2">{{ 5 - $index }}</span>
                        <div class="flex-1 h-1.5 bg-slate-50 dark:bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full" style="width: {{ $data['service_quality']['total_count'] > 0 ? ($count / $data['service_quality']['total_count'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                    <div class="flex items-center justify-between pt-2">
                        <div class="flex flex-col">
                            <span class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ $data['service_quality']['avg'] }}</span>
                            <div class="flex items-center gap-0.5 mt-1">
                                @for($i=1; $i<=5; $i++)
                                <svg class="w-3 h-3 {{ $i <= floor($data['service_quality']['avg']) ? 'text-amber-400 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endfor
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 mt-1">{{ number_format($data['service_quality']['total_count']) }} {{ __('evaluation') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid Layer 4: Technician Performance & Top Technicians -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Technicians List -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Top Technicians') }}</h3>
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="h-8 px-3 rounded-xl border border-slate-100 dark:border-white/5 text-[10px] font-bold text-slate-400 flex items-center gap-2">
                        @php
                            $techLabel = [
                                'platform' => __('Platform Tech'),
                                'company' => __('Company Tech'),
                            ][$data['filters']['tech_type']] ?? __('All');
                        @endphp
                        <span>{{ $techLabel }}</span>
                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-32 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-50 shadow-xl overflow-hidden z-30">
                        <button type="button" @click="document.querySelector('input[name=tech_type]').value = ''; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('All') }}</button>
                        <button type="button" @click="document.querySelector('input[name=tech_type]').value = 'platform'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Platform Tech') }}</button>
                        <button type="button" @click="document.querySelector('input[name=tech_type]').value = 'company'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Company Tech') }}</button>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                @foreach($data['technicians']['top'] as $index => $tech)
                <div class="flex items-center justify-between p-4 rounded-3xl bg-slate-50/50 dark:bg-white/5 border border-slate-50 dark:border-white/5">
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-black text-[#1A1A31] dark:text-white">{{ $index + 1 }}</span>
                        <div class="w-12 h-12 rounded-2xl overflow-hidden bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 flex items-center justify-center">
                            @if($tech->user && $tech->user->avatar)
                                <img src="{{ Storage::url($tech->user->avatar) }}" class="w-full h-full object-cover">
                            @else
                                <div class="text-primary font-black">{{ mb_substr($tech->user->name ?? $tech->name_ar, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-black text-[#1A1A31] dark:text-white">{{ $tech->user->name ?? $tech->name_ar }}</p>
                            <p class="text-[10px] font-bold text-slate-400">{{ $tech->service->name_ar ?? '' }}</p>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path></svg>
                                    <span class="text-[10px] font-black text-[#1A1A31] dark:text-white">{{ round($tech->reviews_avg_rating ?? 0, 1) }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    <span class="text-[10px] font-black text-[#1A1A31] dark:text-white">{{ $tech->orders_count }} {{ __('Order') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.technicians.show', $tech->id) }}" class="px-4 py-2 rounded-xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 text-[10px] font-bold text-slate-600 dark:text-slate-300 hover:border-primary transition-all">{{ __('Details') }}</a>
                </div>
                @endforeach
            </div>
            <a href="{{ route('admin.technicians.index') }}" class="w-full mt-6 py-4 rounded-2xl bg-[#1A1A31] text-white text-xs font-black hover:bg-[#252545] transition-all flex items-center justify-center">{{ __('Show All') }}</a>
        </div>

        <!-- Technician Performance Area Chart -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Technician Performance') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-black text-primary">90%</span>
                        <span class="text-[10px] font-bold text-green-500 bg-green-50 dark:bg-green-500/10 px-2 py-0.5 rounded-full">+4.5%</span>
                    </div>
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="h-8 px-3 rounded-xl border border-slate-100 dark:border-white/5 text-[10px] font-bold text-slate-400 flex items-center gap-2">
                        @php
                            $techPerfLabel = [
                                'platform' => __('Platform Tech'),
                                'company' => __('Company Tech'),
                            ][$data['filters']['tech_type']] ?? __('All');
                        @endphp
                        <span>{{ $techPerfLabel }}</span>
                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-32 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-50 shadow-xl overflow-hidden z-30">
                        <button type="button" @click="document.querySelector('input[name=tech_type]').value = ''; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('All') }}</button>
                        <button type="button" @click="document.querySelector('input[name=tech_type]').value = 'platform'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Platform Tech') }}</button>
                        <button type="button" @click="document.querySelector('input[name=tech_type]').value = 'company'; document.getElementById('filterForm').submit(); open = false" class="w-full px-4 py-2 text-right text-[10px] font-bold hover:bg-slate-50 transition-colors">{{ __('Company Tech') }}</button>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="techPerformanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Grid Layer 5: Spare Parts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Spare Parts Cost -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Spare Parts Cost') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-black text-[#1A1A31] dark:text-white text-xl">{{ number_format($data['spare_parts']['total_cost']) }} <span class="text-xs font-bold text-slate-400">ريال</span></span>
                        <span class="text-[10px] font-bold text-green-500 bg-green-50 dark:bg-green-500/10 px-2 py-0.5 rounded-full">+2.5%</span>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="sparePartsCostChart"></canvas>
            </div>
        </div>

        <!-- Spare Parts Usage Trend -->
        <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Most Used Spare Parts') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-black text-primary">50</span>
                        <span class="text-[10px] font-bold text-green-500 bg-green-50 px-2 py-0.5 rounded-full">+12</span>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="sparePartsUsageChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.classList.contains('dark');
    const primaryColor = '#4F46E5';
    const darkColor = '#1A1A31';
    const lightText = isDark ? '#B8B8CB' : '#64748b';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.02)';

    // Common Chart Config
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { 
                grid: { display: false },
                ticks: { color: lightText, font: { family: 'Cairo', weight: 'bold', size: 10 } }
            },
            y: {
                grid: { color: gridColor },
                ticks: { color: lightText, font: { family: 'Cairo', weight: 'bold', size: 10 } }
            }
        }
    };

    // 1. Users Trend Chart
    new Chart(document.getElementById('usersTrendChart'), {
        type: 'bar',
        data: {
            labels: @json($data['labels']),
            datasets: [
                { data: @json($data['users']['active']), backgroundColor: '#1A1A31', barThickness: 10, borderRadius: 5 },
                { data: @json($data['users']['blocked']), backgroundColor: '#7C7C9B', barThickness: 10, borderRadius: 5 },
                { data: @json($data['users']['new']), backgroundColor: '#E2E2EB', barThickness: 10, borderRadius: 5 }
            ]
        },
        options: commonOptions
    });

    // 2. City Distribution Chart (Stacked)
    new Chart(document.getElementById('cityChart'), {
        type: 'bar',
        data: {
            labels: @json($data['cities']['labels']),
            datasets: [
                { data: @json($data['cities']['counts']).map(c => Math.round(c * 0.7)), backgroundColor: '#1A1A31', barThickness: 12, borderRadius: 6 },
                { data: @json($data['cities']['counts']).map(c => Math.round(c * 0.3)), backgroundColor: '#B8B8CB', barThickness: 12, borderRadius: 6 }
            ]
        },
        options: { ...commonOptions, scales: { ...commonOptions.scales, x: { ...commonOptions.scales.x, stacked: true }, y: { ...commonOptions.scales.y, stacked: true } } }
    });

    // 3. Revenue Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: @json($data['labels']),
            datasets: [{ data: @json($data['financials']['revenue']), backgroundColor: '#1A1A31', barThickness: 24, borderRadius: 8 }]
        },
        options: commonOptions
    });

    // 4. Settlements Chart
    new Chart(document.getElementById('settlementsChart'), {
        type: 'bar',
        data: {
            labels: @json($data['labels']),
            datasets: [{ data: @json($data['financials']['settlements']), backgroundColor: '#B8B8CB', barThickness: 24, borderRadius: 8 }]
        },
        options: commonOptions
    });

    // 5. Categories Donut Chart
    new Chart(document.getElementById('categoriesChart'), {
        type: 'doughnut',
        data: {
            labels: @json($data['categories']['labels']),
            datasets: [{ 
                data: @json($data['categories']['counts']), 
                backgroundColor: ['#1A1A31', '#7C7C9B', '#B8B8CB', '#E2E2EB'],
                borderWidth: 8,
                borderColor: isDark ? '#1A1A31' : '#fff'
            }]
        },
        options: { cutout: '85%', plugins: { legend: { display: false } } }
    });

    // 6. Gauge Chart
    new Chart(document.getElementById('gaugeChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [{{ $data['service_quality']['level'] }}, {{ 100 - $data['service_quality']['level'] }}],
                backgroundColor: ['#1A1A31', '#E2E2EB'],
                borderWidth: 0,
                circumference: 180,
                rotation: 270,
                cutout: '85%'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { tooltips: { enabled: false }, legend: { display: false } } }
    });

    // 7. Tech Performance Chart
    new Chart(document.getElementById('techPerformanceChart'), {
        type: 'line',
        data: {
            labels: @json($data['labels']),
            datasets: [
                { 
                    data: @json($data['technicians']['active_trend']), 
                    borderColor: '#1A1A31', 
                    fill: true, 
                    backgroundColor: 'rgba(26, 26, 49, 0.05)',
                    tension: 0.4,
                    pointRadius: 0
                },
                { 
                    data: @json($data['technicians']['busy_trend']), 
                    borderColor: '#B8B8CB', 
                    fill: false, 
                    tension: 0.4,
                    pointRadius: 0
                }
            ]
        },
        options: commonOptions
    });

    // 8. Spare Parts Cost Chart
    new Chart(document.getElementById('sparePartsCostChart'), {
        type: 'bar',
        data: {
            labels: @json($data['labels']),
            datasets: [{ data: @json($data['labels']).map(() => Math.floor(Math.random() * 5000) + 1000), backgroundColor: '#E2E2EB', barThickness: 15, borderRadius: 5 }]
        },
        options: { ...commonOptions, indexAxis: 'y' }
    });

    // 9. Spare Parts Usage Chart
    new Chart(document.getElementById('sparePartsUsageChart'), {
        type: 'line',
        data: {
            labels: @json($data['labels']),
            datasets: [{ 
                data: @json($data['labels']).map(() => Math.floor(Math.random() * 100) + 20), 
                borderColor: '#1A1A31', 
                fill: true, 
                backgroundColor: 'rgba(26, 26, 49, 0.05)', 
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#1A1A31'
            }]
        },
        options: commonOptions
    });
});
</script>

<style>
    canvas { width: 100% !important; height: 100% !important; }
</style>
@endsection
