@extends('layouts.admin')

@section('title', __('Reviews Management'))

@section('content')
<div class="space-y-8 pb-20" dir="rtl" x-data="reviewManagement()">
    
    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-[#1A1A31]">{{ __('Reviews Management') }}</h1>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $cardStats = [
                ['label' => 'Total Evaluations', 'value' => $stats['total']['value'], 'color' => '#10B981', 'trend' => ($stats['total']['change'] >= 0 ? '+' : '') . $stats['total']['change'] . '%', 'icon' => 'reviews', 'trend_data' => $stats['total']['trend']],
                ['label' => 'Positive Ratings', 'value' => $stats['positive']['value'], 'color' => '#10B981', 'trend' => ($stats['positive']['change'] >= 0 ? '+' : '') . $stats['positive']['change'] . '%', 'icon' => 'positive', 'trend_data' => $stats['positive']['trend']],
                ['label' => 'Negative Ratings', 'value' => $stats['negative']['value'], 'color' => '#EF4444', 'trend' => ($stats['negative']['change'] >= 0 ? '+' : '') . $stats['negative']['change'] . '%', 'icon' => 'negative', 'trend_data' => $stats['negative']['trend']],
                ['label' => 'Average Rating', 'value' => $stats['average']['value'] . '/5', 'color' => '#F59E0B', 'trend' => ($stats['average']['change'] >= 0 ? '+' : '') . $stats['average']['change'] . '%', 'icon' => 'rating', 'distribution' => $stats['average']['distribution']],
            ];
        @endphp

        @foreach($cardStats as $index => $stat)
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-50 flex flex-col justify-between h-52 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex items-start justify-between relative z-10">
                <div class="space-y-1">
                    <p class="text-md font-bold text-[#1A1A31] opacity-60">{{ __($stat['label']) }}</p>
                    <div class="flex items-center gap-3">
                        <h3 class="text-3xl font-black text-[#1A1A31]">{{ $stat['value'] }}</h3>
                        @if($stat['icon'] == 'rating')
                        <div class="flex items-center text-yellow-500 mb-1">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ (float)$stat['trend'] >= 0 ? 'bg-green-50 text-green-600 border-green-200' : 'bg-red-50 text-red-600 border-red-200' }} border opacity-80">
                            {{ $stat['trend'] }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('compared to last week') }}</span>
                    </div>
                </div>

                @if($stat['icon'] == 'rating')
                <div class="w-20 h-20 -mt-2">
                    <canvas id="doughnut-rating"></canvas>
                </div>
                @endif
            </div>
            
            @if($stat['icon'] != 'rating')
            <!-- Sparkline Chart -->
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-40 group-hover:opacity-60 transition-opacity pointer-events-none">
                <canvas id="chart-{{ $index }}" class="w-full h-full"></canvas>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Evaluations Table Section -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm overflow-visible min-h-[600px]" dir="rtl">
        <!-- Controls Row -->
        <div class="p-6 border-b border-slate-50 dark:border-white/5 relative min-h-[5rem]">
            <!-- Default Controls (Show when no items selected) -->
            <div x-show="selectedRows.length === 0" class="flex flex-col md:flex-row items-center justify-between gap-6 w-full">
                
                {{-- Right Side (Filter + Search) --}}
                <div class="flex items-center gap-3 flex-1 max-w-2xl justify-start">
                    <!-- Filter Toggle & Panel Wrapper -->
                    <div class="relative">
                        <button @click="showFilters = !showFilters" 
                                class="w-12 h-12 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 text-slate-400 hover:text-primary dark:hover:text-white transition shadow-sm relative shrink-0 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                            <div x-show="sortBy != 'newest' || clientType != '' || technicianType != '' || selectedCategoryId != ''" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></div>
                        </button>

                        <!-- Filter Dropdown Panel (Anchored to Trigger) -->
                        <div x-show="showFilters" @click.away="showFilters = false" x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute top-full right-0 mt-3 w-80 bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-6 space-y-6 text-right overflow-y-auto max-h-[calc(100vh-200px)] custom-scrollbar">
                            
                            <form id="filterForm" action="{{ route('admin.reviews.index') }}" method="GET" class="space-y-6">
                                <input type="hidden" name="search" :value="searchQuery">
                                
                                <!-- Sort -->
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Sort by:') }}</label>
                                    <div class="space-y-3">
                                        @foreach(['newest' => __('All'), 'name' => __('Name'), 'rating' => __('Rating'), 'oldest' => __('Oldest')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-sm font-bold text-slate-500 dark:text-slate-300 transition-colors">{{ $label }}</span>
                                             <input type="radio" name="sort_by" value="{{ $val }}" x-model="sortBy" 
                                                    class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-100 dark:bg-white/5"></div>

                                <!-- Customer Type -->
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Customer Type:') }}</label>
                                    <div class="space-y-3">
                                        @foreach(['' => __('All'), 'individual' => __('Individual'), 'company' => __('Corporate')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-sm font-bold text-slate-500 dark:text-slate-300 transition-colors">{{ $label }}</span>
                                             <input type="radio" name="client_type" value="{{ $val }}" x-model="clientType" 
                                                    class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-100 dark:bg-white/5"></div>

                                <!-- Service Category -->
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Service Category:') }}</label>
                                    <div class="space-y-3">
                                        <label class="flex items-center justify-between cursor-pointer group" @click="selectedServiceIds = []">
                                             <span class="text-sm font-bold text-slate-500 dark:text-slate-300 transition-colors">{{ __('All') }}</span>
                                             <input type="radio" name="service_category_id" value="" x-model="selectedCategoryId" 
                                                    class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @foreach($categories as $cat)
                                        <label class="flex items-center justify-between cursor-pointer group" @click="selectedServiceIds = []">
                                             <span class="text-sm font-bold text-slate-500 dark:text-slate-300 transition-colors">{{ app()->getLocale() == 'ar' ? $cat->name_ar : $cat->name_en }}</span>
                                             <input type="radio" name="service_category_id" value="{{ $cat->id }}" x-model="selectedCategoryId" 
                                                    class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Service Type -->
                                <div x-show="filteredServices.length > 0" x-cloak class="space-y-4">
                                    <div class="h-px bg-slate-100 dark:bg-white/5"></div>
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Service Type:') }}</label>
                                    <div class="space-y-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                        <template x-for="service in filteredServices" :key="service.id">
                                            <label class="flex items-center justify-between cursor-pointer group">
                                                 <span class="text-[11px] font-bold text-slate-500 dark:text-slate-300 transition-colors" 
                                                       x-text="service.name_{{ app()->getLocale() }}"></span>
                                                 <div class="relative w-5 h-5 border-2 rounded-lg transition-all flex items-center justify-center shrink-0"
                                                      :class="selectedServiceIds.includes(String(service.id)) ? 'border-primary bg-primary' : 'border-slate-200'">
                                                     <input type="checkbox" name="service_ids[]" :value="service.id" x-model="selectedServiceIds" class="hidden">
                                                     <template x-if="selectedServiceIds.includes(String(service.id))">
                                                         <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                     </template>
                                                 </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <div class="h-px bg-slate-100 dark:bg-white/5"></div>

                                <!-- Technician Type -->
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Technician Type:') }}</label>
                                    <div class="space-y-3">
                                        @foreach(['' => __('All'), 'platform' => __('Platform'), 'company' => __('Company')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-sm font-bold text-slate-500 dark:text-slate-300 transition-colors">{{ $label }}</span>
                                             <input type="radio" name="technician_type" value="{{ $val }}" x-model="technicianType" 
                                                    class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-6 border-t border-slate-100 dark:border-white/5">
                                    <button type="submit" class="flex-1 py-3 bg-[#1A1A31] dark:bg-white dark:text-[#1A1A31] text-white text-xs font-black rounded-xl hover:bg-slate-800 transition-all shadow-lg">
                                        {{ __('Apply') }}
                                    </button>
                                    <button type="button" @click="resetFilters()" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 text-xs font-black rounded-xl hover:bg-slate-200 transition-all text-center">
                                        {{ __('Reset') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative md:w-80 flex-1">
                        <input type="text" x-model="searchQuery" @keyup.enter="applySearch()"
                               placeholder="{{ __('Search...') }}"
                               class="w-full pr-12 pl-4 py-3 bg-[#FBFBFF] dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- Left Side (Download + Quick Filter) --}}
                <div class="flex items-center gap-3 shrink-0">
                    <!-- Quick Filter -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 px-5 py-3 bg-[#1A1A31] border border-slate-200 dark:border-white/10 text-white text-sm font-bold rounded-xl hover:bg-black transition-all">
                            <span class="font-bold text-sm">{{ __('All Evaluations') }}</span>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-[#1A1A31] rounded-xl shadow-xl border border-slate-50 dark:border-white/5 z-50 overflow-hidden py-2">
                            <a href="{{ route('admin.reviews.index') }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-white hover:bg-slate-50 dark:hover:bg-white/10">{{ __('All') }}</a>
                            <a href="{{ route('admin.reviews.index', ['status' => 'positive']) }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-white hover:bg-slate-50 dark:hover:bg-white/10">{{ __('Positive') }}</a>
                            <a href="{{ route('admin.reviews.index', ['status' => 'negative']) }}" class="block px-4 py-3 text-xs font-bold text-slate-600 dark:text-white hover:bg-slate-50 dark:hover:bg-white/10">{{ __('Negative') }}</a>
                        </div>
                    </div>

                    <!-- Download All -->
                    <a href="{{ route('admin.reviews.download', request()->query()) }}" class="flex items-center gap-2 px-5 py-3 border border-slate-200 dark:border-white/10 text-[#1A1A31] dark:text-white text-sm font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        {{ __('Download All') }}
                    </a>
                </div>
            </div>

            <!-- Bulk Actions Bar (Visible when rows selected) -->
            <div x-show="selectedRows.length > 0" x-cloak class="absolute inset-0 bg-white dark:bg-[#1A1A31] z-20 flex items-center justify-between px-6 border-b border-slate-50 dark:border-white/5 rounded-t-[2.5rem]">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-slate-500 bg-slate-100 dark:bg-white/5 dark:text-slate-300 px-4 py-2.5 rounded-xl">
                        <span x-text="selectedRows.length"></span> {{ __('selected items') }}
                    </span>
                    
                    <button @click="downloadSelected()"
                            class="px-6 py-3 bg-[#1A1A31] dark:bg-white dark:text-[#1A1A31] text-white rounded-xl text-xs font-black hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg shadow-black/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        {{ __('Download Selected') }}
                    </button>

                    <form id="bulk-delete-form" action="{{ route('admin.reviews.bulk-destroy') }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selectedRows" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                    </form>
                    
                    <button @click="deleteSelected()"
                            class="px-6 py-3 bg-rose-500 text-white rounded-xl text-xs font-black hover:bg-rose-600 transition-all flex items-center gap-2 shadow-lg shadow-rose-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Delete Selected') }}
                    </button>
                </div>
                <button @click="selectedRows = []" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right" dir="rtl">
                <thead>
                    <tr class="bg-slate-50/50 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 text-right">
                        <th class="py-6 px-6">
                            <input type="checkbox" 
                                   @change="toggleAll($event.target.checked)"
                                   :checked="selectedRows.length === itemIds.length && itemIds.length > 0"
                                   class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary bg-transparent cursor-pointer">
                        </th>
                        <th class="py-6 px-4">#</th>
                        <th class="py-6 px-4">{{ __('Customer Name') }}</th>
                        <th class="py-6 px-4">{{ __('Customer Type') }}</th>
                        <th class="py-6 px-4">{{ __('Technician Name') }}</th>
                        <th class="py-6 px-4">{{ __('Technician Type') }}</th>
                        <th class="py-6 px-4">{{ __('Service') }}</th>
                        <th class="py-6 px-4">{{ __('Remark') }}</th>
                        <th class="py-6 px-4 text-center">{{ __('Rating') }}</th>
                        <th class="py-6 px-4">{{ __('Status') }}</th>
                        <th class="py-6 px-4">{{ __('Date') }}</th>
                        <th class="py-6 px-8 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                    <tr class="group hover:bg-slate-50/50 transition-all cursor-pointer"
                        :class="selectedRows.includes({{ $item->id }}) ? 'bg-primary/5' : ''"
                        @click="toggleRow({{ $item->id }})">
                        <td class="py-6 px-6" @click.stop="">
                            <input type="checkbox" 
                                   @change="toggleRow({{ $item->id }})"
                                   :checked="selectedRows.includes({{ $item->id }})"
                                   class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary bg-transparent cursor-pointer">
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400">{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 shrink-0 border border-slate-50 shadow-sm">
                                    @if($item->user && $item->user->avatar)
                                        <img src="{{ Storage::url($item->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-black">
                                            {{ mb_substr($item->user->name ?? '?', 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <span class="text-xs font-bold text-[#1A1A31] transition-colors text-right">{{ $item->user->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-3 py-1 rounded-lg bg-slate-50 text-slate-500 dark:text-white text-[10px] font-bold border border-slate-100">
                                {{ $item->user && $item->user->type == 'individual' ? __('Individual') : __('Corporate') }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-xs font-bold text-[#1A1A31]">{{ $item->technician->user->name ?? $item->technician->name ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-3 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100">
                                {{ $item->technician && $item->technician->maintenance_company_id ? __('Company') : __('Platform') }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                           <div class="space-y-0.5">
                                <span class="block text-[11px] font-black text-[#1A1A31]">
                                    {{ app()->getLocale() == 'ar' 
                                        ? ($item->service?->parent?->name_ar ?? '-') 
                                        : ($item->service?->parent?->name_en ?? '-') 
                                    }}
                                </span>

                                <span class="block text-[9px] font-bold text-slate-400 ">
                                    (
                                    {{ app()->getLocale() == 'ar' 
                                        ? ($item->service?->name_ar ?? '-') 
                                        : ($item->service?->name_en ?? '-') 
                                    }}
                                    )
                                </span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <p class="text-[11px] font-bold text-slate-400 max-w-[150px] truncate" title="{{ $item->comment }}">{{ $item->comment ?: __('No remarks') }}</p>
                        </td>
                        <td class="py-6 px-4 text-right" dir="rtl">
                            <div class="flex items-center gap-0.5 justify-end">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= (int)$item->rating ? 'text-yellow-500 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                @endfor
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-3 py-1 rounded-lg {{ $item->rating > 3 ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100' }} border text-[10px] font-bold">
                                {{ $item->rating > 3 ? __('Positive') : __('Negative') }}
                            </span>
                        </td>
                        <td class="py-6 px-4 uppercase tracking-tighter">
                            <span class="text-[11px] font-bold text-slate-400">{{ $item->created_at->format('j/n/Y') }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.reviews.show', $item->id) }}" class="p-2 rounded-xl bg-slate-50 text-slate-400 hover:bg-[#1A1A31] hover:text-white transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                             
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-20 text-center text-slate-300 font-bold tracking-widest">{{ __('No evaluations found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($items->hasPages())
        <div class="p-10 border-t border-slate-50 bg-slate-50/50 flex items-center justify-between">
            <div class="text-[11px] font-bold text-slate-400">
                {{ __('Showing') }} {{ $items->firstItem() }} {{ __('to') }} {{ $items->lastItem() }} {{ __('of') }} {{ $items->total() }} {{ __('entries') }}
            </div>
            {{ $items->appends(request()->all())->links('vendor.pagination.custom-admin') }}
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function reviewManagement() {
    return {
        showFilters: false,
        selectedRows: [],
        itemIds: @json($items->pluck('id')),
        searchQuery: '{{ request('search') }}',
        sortBy: '{{ request('sort_by', 'newest') }}',
        clientType: '{{ request('client_type', '') }}',
        technicianType: '{{ request('technician_type', '') }}',
        selectedCategoryId: '{{ request('service_category_id', '') }}',
        selectedServiceIds: {!! json_encode(request('service_ids', [])) !!},
        allServices: @json($services),

        get filteredServices() {
            if (!this.selectedCategoryId) return [];
            return this.allServices.filter(s => s.parent_id == this.selectedCategoryId);
        },

        toggleAll(checked) {
            this.selectedRows = checked ? [...this.itemIds] : [];
        },

        toggleRow(id) {
            if (this.selectedRows.includes(id)) {
                this.selectedRows = this.selectedRows.filter(r => r !== id);
            } else {
                this.selectedRows.push(id);
            }
        },

        downloadSelected() {
            const ids = this.selectedRows.join(',');
            window.location.href = "{{ route('admin.reviews.download') }}?ids=" + ids;
        },

        applySearch() {
            let params = new URLSearchParams(window.location.search);
            params.set('search', this.searchQuery);
            window.location.search = params.toString();
        },

        resetFilters() {
            window.location.href = "{{ route('admin.reviews.index') }}";
        },

        deleteSelected() {
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('Are you sure you want to delete the selected reviews?') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "{{ __('Delete') }}",
                cancelButtonText: "{{ __('Cancel') }}",
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-2xl p-8',
                    title: 'text-xl font-black text-slate-800 dark:text-white pt-4',
                    htmlContainer: 'text-sm font-bold text-slate-500 dark:text-slate-400 mt-2',
                    confirmButton: 'px-8 py-3.5 swal2-delete-btn text-white rounded-2xl font-black text-sm transition-all mx-2 cursor-pointer shadow-lg shadow-rose-500/20',
                    cancelButton: 'px-8 py-3.5 bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 rounded-2xl font-black text-sm hover:bg-slate-200 dark:hover:bg-white/20 transition-all mx-2 cursor-pointer'
                },
                background: isDark ? '#1A1A31' : '#ffffff',
                color: isDark ? '#ffffff' : '#1e293b',
                iconColor: '#ef4444',
                heightAuto: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('bulk-delete-form').submit();
                }
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Sparkline Charts
    @foreach($cardStats as $idx => $st)
        @if($st['icon'] != 'rating')
        const canvas_{{ $idx }} = document.getElementById('chart-{{ $idx }}');
        if (canvas_{{ $idx }}) {
            new Chart(canvas_{{ $idx }}, {
                type: 'line',
                data: {
                    labels: @json($st['trend_data']),
                    datasets: [{
                        data: @json($st['trend_data']),
                        borderColor: '{{ $st['color'] }}',
                        borderWidth: 3,
                        fill: true,
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return null;
                            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                            gradient.addColorStop(0, 'white');
                            gradient.addColorStop(1, '{{ $st['color'] }}20');
                            return gradient;
                        },
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: { x: { display: false }, y: { display: false } },
                    elements: { point: { radius: 0 } }
                }
            });
        }
        @endif
    @endforeach

    // Doughnut Chart for Rating Distribution
    const doughnutCanvas = document.getElementById('doughnut-rating');
    if (doughnutCanvas) {
        new Chart(doughnutCanvas, {
            type: 'doughnut',
            data: {
                labels: ['5', '4', '3', '2', '1'],
                datasets: [{
                    data: {!! $stats['average']['distribution'] !!},
                    backgroundColor: ['#10B981', '#34D399', '#FBBF24', '#F87171', '#EF4444'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
            }
        });
    }
});
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
[x-cloak] { display: none !important; }
.swal2-delete-btn {
    background-color: #ef4444 !important;
    color: #ffffff !important;
}
.swal2-delete-btn:hover {
    background-color: #dc2626 !important;
}
</style>
@endsection