@extends('layouts.admin')

@section('title', __('Technician Registration Requests'))
@section('page_title', __('Technician Registration Requests'))

@section('content')
<div class="space-y-6 pb-20" x-data="{ 
    selectedIds: [], 
    showFilters: false,
    showSuccessModal: {{ session('success_onboarding') ? 'true' : 'false' }},

    // Accept Modal State
    showAcceptModal: false,
    acceptTargetId: null,
    isAccepting: false,

    // Reject Modal State
    showRejectModal: false,
    rejectTargetId: null,

    confirmAccept(id) {
        this.acceptTargetId = id;
        this.isAccepting = false;
        this.showAcceptModal = true;
    },

    confirmReject(id) {
        this.rejectTargetId = id;
        this.showRejectModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('Technician Registration Requests') }}</h2>
            <p class="text-sm text-slate-500 dark:text-white/50">{{ __('Review and approve new technician registration requests') }}</p>
        </div>

        <!-- Status Tabs (Refined) -->
        <div class="flex items-center gap-4">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" 
               class="flex items-center gap-2 px-6 py-2.5 rounded-full text-sm 
               font-bold transition-all {{ request('status', 'pending') === 'pending' ? 'bg-[#1A1A31] text-white' : 'bg-slate-100 text-slate-500 dark:text-[#1A1A31] dark:bg-white hover:bg-slate-200' }}">
                <span>{{ __('Technician Requests') }}</span>
                <span class="bg-slate-400/20 px-2 py-0.5 rounded text-[10px]">{{ $stats['pending_requests'] > 99 ? '99+' : $stats['pending_requests'] }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'accepted']) }}" 
               class="flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-bold transition-all {{ request('status') === 'accepted' ? 'bg-[#1A1A31] text-white' : 'bg-slate-100 text-slate-500 dark:text-[#1A1A31] dark:bg-white hover:bg-slate-200' }}">
                <span>{{ __('Accepted Requests') }}</span>
                <span class="bg-slate-400/20 px-2 py-0.5 rounded text-[10px]">{{ $stats['accepted_requests'] > 99 ? '99+' : $stats['accepted_requests'] }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}" 
               class="flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-bold transition-all {{ request('status') === 'rejected' ? 'bg-[#1A1A31] text-white' : 'bg-slate-100 text-slate-500 dark:text-[#1A1A31] dark:bg-white hover:bg-slate-200' }}">
                <span>{{ __('Rejected Requests') }}</span>
                <span class="bg-slate-400/20 px-2 py-0.5 rounded text-[10px]">{{ $stats['rejected_requests'] > 99 ? '99+' : $stats['rejected_requests'] }}</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Requests -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group hover:shadow-md transition-all h-48 flex flex-col justify-between z-0">
            <div class="flex items-center justify-between relative z-10">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-300 mb-1">{{ __('Total Requests') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total_requests'] ?? 0) }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-500 font-bold border border-indigo-500/20">+21.21%</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium mt-1">{{ __('Compared to last week') }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
                <svg viewBox="0 0 100 40" class="w-full h-full text-indigo-500 overflow-visible" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="gradient-total" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="currentColor" stop-opacity="0.2" />
                            <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <path d="M0,35 C10,35 15,10 25,10 C35,10 40,30 50,30 C60,30 65,5 75,5 C85,5 90,25 100,25" fill="none" stroke="currentColor" stroke-width="3" vector-effect="non-scaling-stroke" stroke-linecap="round" />
                    <path d="M0,35 C10,35 15,10 25,10 C35,10 40,30 50,30 C60,30 65,5 75,5 C85,5 90,25 100,25 L100,40 L0,40 Z" fill="url(#gradient-total)" />
                </svg>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group hover:shadow-md transition-all h-48 flex flex-col justify-between z-0">
            <div class="flex items-center justify-between relative z-10">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-300 mb-1">{{ __('Pending Requests') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['pending_requests'] ?? 0) }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-500 font-bold border border-blue-500/20">+0%</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium mt-1">{{ __('Compared to last week') }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
                <svg viewBox="0 0 100 40" class="w-full h-full text-blue-500 overflow-visible" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="gradient-pending" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="currentColor" stop-opacity="0.2" />
                            <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <path d="M0,30 C15,30 20,5 35,5 C50,5 55,25 70,25 C85,25 90,15 100,15" fill="none" stroke="currentColor" stroke-width="3" vector-effect="non-scaling-stroke" stroke-linecap="round" />
                    <path d="M0,30 C15,30 20,5 35,5 C50,5 55,25 70,25 C85,25 90,15 100,15 L100,40 L0,40 Z" fill="url(#gradient-pending)" />
                </svg>
            </div>
        </div>

        <!-- Accepted Requests -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group hover:shadow-md transition-all h-48 flex flex-col justify-between z-0">
            <div class="flex items-center justify-between relative z-10">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-300 mb-1">{{ __('Accepted Requests') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['accepted_requests'] ?? 0) }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 font-bold border border-emerald-500/20">+21.88%</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium mt-1">{{ __('Compared to last week') }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
                <svg viewBox="0 0 100 40" class="w-full h-full text-emerald-500 overflow-visible" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="gradient-accepted" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="currentColor" stop-opacity="0.2" />
                            <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <path d="M0,35 C10,35 20,5 35,5 C50,5 60,25 75,25 C90,25 95,10 100,10" fill="none" stroke="currentColor" stroke-width="3" vector-effect="non-scaling-stroke" stroke-linecap="round" />
                    <path d="M0,35 C10,35 20,5 35,5 C50,5 60,25 75,25 C90,25 95,10 100,10 L100,40 L0,40 Z" fill="url(#gradient-accepted)" />
                </svg>
            </div>
        </div>

        <!-- Rejected Requests -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group hover:shadow-md transition-all h-48 flex flex-col justify-between z-0">
            <div class="flex items-center justify-between relative z-10">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-300 mb-1">{{ __('Rejected Requests') }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['rejected_requests'] ?? 0) }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-500 font-bold border border-rose-500/20">+0%</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-300 font-medium mt-1">{{ __('Compared to last week') }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
                <svg viewBox="0 0 100 40" class="w-full h-full text-rose-500 overflow-visible" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="gradient-rejected" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="currentColor" stop-opacity="0.2" />
                            <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <path d="M0,20 C20,20 30,35 45,35 C60,35 70,10 85,10 C95,10 100,20 100,20" fill="none" stroke="currentColor" stroke-width="3" vector-effect="non-scaling-stroke" stroke-linecap="round" />
                    <path d="M0,20 C20,20 30,35 45,35 C60,35 70,10 85,10 C95,10 100,20 100,20 L100,40 L0,40 Z" fill="url(#gradient-rejected)" />
                </svg>
            </div>
        </div>
    </div>


    <!-- Main Container with Filters & Actions -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-visible">
        <div class="p-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Filter & Search (Refined) -->
             <div class="flex items-center gap-3 w-full md:w-auto">
                <!-- Filter Button -->
                <div class="relative">
                    <button @click="showFilters = !showFilters"
                        class="px-4 py-3 flex items-center gap-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 text-slate-500 dark:text-slate-300 font-bold transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                        </svg>
                    </button>

                    <!-- Filter Dropdown (Screenshots Match) -->
                    <div x-show="showFilters" 
                         x-cloak
                         @click.away="showFilters = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} mt-3 w-80 bg-white dark:bg-[#1A1A31] rounded-3xl shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-6 lg:p-8">
                        
                        <form action="{{ url()->current() }}" method="GET" class="space-y-8" x-data="{ selectedCategory: '{{ request('category_id') }}' }">
                            @foreach(request()->except(['sort_by', 'category_id', 'service_id']) as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $val)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $val }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach

                            <!-- Sort By (Radio Buttons) -->
                            <div class="space-y-4">
                                <label class="text-sm font-black text-[#1A1A31] dark:text-white flex items-center gap-2">
                                    {{ __('Sort by') }}:
                                </label>
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach(['all' => 'All', 'name' => 'Name', 'latest' => 'Newest', 'oldest' => 'Oldest'] as $val => $key)
                                    <label class="flex items-center justify-between cursor-pointer group">
                                        <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ __($key) }}</span>
                                        <div class="relative flex items-center">
                                            <input type="radio" name="sort_by" value="{{ $val }}" {{ request('sort_by', 'latest') == $val ? 'checked' : '' }}
                                                   class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="h-px bg-slate-100 dark:bg-white/5"></div>

                            <!-- Category (Radio Buttons) -->
                            <div class="space-y-4">
                                <label class="text-sm font-black text-[#1A1A31] dark:text-white flex items-center gap-2">
                                    {{ __('Service Category') }}:
                                </label>
                                <div class="grid grid-cols-1 gap-3">
                                    <label class="flex items-center justify-between cursor-pointer group">
                                        <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ __('All') }}</span>
                                        <input type="radio" name="category_id" value="" x-model="selectedCategory" {{ !request('category_id') ? 'checked' : '' }}
                                               class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                    </label>
                                    @foreach($categories as $cat)
                                    <label class="flex items-center justify-between cursor-pointer group">
                                        <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ $cat->name_ar }}</span>
                                        <input type="radio" name="category_id" value="{{ $cat->id }}" x-model="selectedCategory" {{ request('category_id') == $cat->id ? 'checked' : '' }}
                                               class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Service Type (Checkboxes) -->
                            <div class="space-y-4 border-t border-slate-100 dark:border-white/5 pt-6" x-show="selectedCategory && selectedCategory !== ''" x-cloak>
                                <label class="text-sm font-black text-[#1A1A31] dark:text-white flex items-center gap-2">
                                    {{ __('Service Type') }}:
                                </label>
                                <div class="grid grid-cols-2 gap-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($services as $service)
                                    <label class="flex items-center justify-between cursor-pointer group" x-show="selectedCategory == '{{ $service->parent_id }}'">
                                        <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ $service->name_ar }}</span>
                                        <input type="checkbox" name="service_id[]" value="{{ $service->id }}" {{ is_array(request('service_id')) && in_array($service->id, request('service_id')) ? 'checked' : '' }}
                                               class="w-5 h-5 border-2 border-slate-200 rounded-lg text-primary focus:ring-primary transition-all cursor-pointer">
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-white/5">
                                <a href="{{ url()->current() }}" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl text-center hover:bg-slate-200 transition-all">
                                    {{ __('Reset') }}
                                </a>
                                <button type="submit" class="flex-1 py-3 bg-[#1A1A31] text-white text-xs font-black rounded-xl hover:bg-[#2A2A41] transition-all shadow-lg shadow-[#1A1A31]/20">
                                    {{ __('Apply') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                 <!-- Search (Refined) -->
                <form action="{{ url()->current() }}" method="GET" class="relative group" x-data="{ search: '{{ request('search') }}' }">
                    @foreach(request()->except(['search', 'page']) as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $val)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $val }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <input type="text"
                        name="search"
                        x-model="search"
                        class="w-80 {{ app()->getLocale() == 'ar' ? 'pr-10 pl-4' : 'pl-10 pr-4' }} py-3 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-sm font-bold text-slate-800 dark:text-white placeholder-slate-400 outline-none focus:ring-2 focus:ring-primary/20 transition-all">

                    <div class="absolute inset-y-0 {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} px-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-primary dark:hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[400px]">
             <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                <tr class="text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                    <th class="py-4 px-6 text-center">
                        <input type="checkbox" @change="if($el.checked) selectedIds = @json($items->pluck('id')); else selectedIds = []" class="rounded border-slate-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-4 px-6">{{ __('Technician') }}</th>
                    <th class="py-4 px-6">{{ __('Phone Number') }}</th>
                    <th class="py-4 px-6">{{ __('Email') }}</th>
                    <th class="py-4 px-6">{{ __('Company') }}</th>
                    <th class="py-4 px-6">{{ __('Primary Service') }}</th>
                    <th class="py-4 px-6">{{ __('Service Type') }}</th>
                    <th class="py-4 px-6 text-center">{{ __('Experience') }}</th>
                    <th class="py-4 px-6">{{ __('Date') }}</th>
                    @if(request('status') !== 'accepted' && request('status') !== 'rejected')
                    <th class="py-4 px-6 text-center">{{ __('Actions') }}</th>
                    @endif
                </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr @click="window.location.href = '{{ route('admin.technician-requests.show', $item->id) }}'" class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-all group cursor-pointer">
                        <td class="py-4 px-6 text-center" @click.stop>
                            <input type="checkbox" x-model="selectedIds" value="{{ $item->id }}" class="rounded border-slate-300 text-primary focus:ring-primary">
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black overflow-hidden shadow-sm">
                                    @if($item->photo)
                                        <img src="{{ $item->photo }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-md">{{ mb_substr($item->name_ar ?? $item->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-slate-900 dark:text-white font-black group-hover:text-primary dark:hover:text-white transition-colors">{{ $item->name_ar ?? $item->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-600 dark:text-slate-300 font-medium" dir="ltr">{{ $item->phone }}</td>
                        <td class="py-4 px-6 opacity-70">{{ $item->email }}</td>
                        <td class="py-4 px-6">
                            @if($item->maintenanceCompany)
                                <span class="text-slate-600 dark:text-slate-400 font-bold uppercase text-[10px]">{{ $item->maintenanceCompany->user->name ?? $item->company_name }}</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600 italic">{{ __('Independent') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-slate-600 dark:text-slate-400">{{ $item->category->name_ar ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-slate-600 dark:text-slate-400">{{ $item->service->name_ar ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="px-2 py-1 rounded-lg bg-indigo-50 dark:bg-[#1A1A31] text-indigo-600 dark:text-indigo-400 font-black">{{ $item->years_experience }} {{ __('Years') }}</span>
                        </td>
                         <td class="py-4 px-6 opacity-50 text-[12px] whitespace-nowrap">{{ $item->created_at->format('d/m/Y') }}</td>
                        @if(request('status') !== 'accepted' && request('status') !== 'rejected')
                        <td class="py-4 px-6 text-center" @click.stop anchor="top">
                            <div class="flex items-center justify-center gap-1">
                                @if($item->status == 'pending')
                                    <button type="button" @click.stop="confirmAccept({{ $item->id }})" 
                                            class="w-10 h-10 rounded-xl flex items-center justify-center text-green-500 hover:bg-green-50 dark:hover:bg-white/10 transition-all">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </button>
                                    <button type="button" @click.stop="confirmReject({{ $item->id }})" 
                                            class="w-10 h-10 rounded-xl flex items-center justify-center text-red-500 hover:bg-red-50 dark:hover:bg-white/10 transition-all">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    </button>
                                @endif
                          
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                         <td colspan="11" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-4">
                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <p>{{ __('No requests found') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
         @if($items->hasPages())
        <div class="p-6 border-t border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
            {{ $items->links() }}
        </div>
        @endif
    </div>


    <!-- Accept Confirmation Modal -->
    <div x-show="showAcceptModal" 
         x-cloak
         class="fixed inset-0 z-[1000] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAcceptModal = false"></div>
        
        <div class="bg-white dark:bg-[#1A1A31] w-full max-w-2xl rounded-[1.5rem] overflow-hidden shadow-2xl relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <button @click="showAcceptModal = false" class="absolute top-6 {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} text-slate-400 hover:text-slate-600 dark:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="p-10">
                <div class="mb-10 {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                    <h3 class="text-2xl font-black text-[#1A1A31] dark:text-white mb-2">{{ __('Accept Technician Request') }}</h3>
                    <p class="text-slate-400 font-bold text-sm">
                        {{ __('will_create_account_email_hint') }}
                    </p>
                </div>

                <form x-ref="acceptForm" 
                      :action="'{{ url('admin/technician-requests') }}/' + acceptTargetId + '/accept'" 
                      method="POST" 
                      class="space-y-8" 
                      @submit="if(!acceptTargetId) { $event.preventDefault(); return; } isAccepting = true;">
                    @csrf
                    <div>
                        <label class="text-sm font-black text-[#1A1A31] dark:text-white block mb-3">{{ __('Password') }}</label>
                        <input type="password" name="password" required minlength="8" placeholder="{{ __('Enter password') }}"
                               class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-md font-bold text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary/20 transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showAcceptModal = false" :disabled="isAccepting"
                                class="px-8 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-xl font-black hover:bg-slate-200 transition-all uppercase tracking-widest text-sm disabled:opacity-50">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" 
                                :disabled="isAccepting"
                                :class="isAccepting ? 'opacity-50 cursor-not-allowed' : ''"
                                class="px-8 py-4 bg-[#1A1A31] text-white rounded-xl font-black hover:bg-[#2A2A41] transition-all shadow-xl shadow-[#1A1A31]/20 uppercase tracking-widest text-sm flex items-center gap-2">
                            <span x-show="!isAccepting">{{ __('Confirm & Create Account') }}</span>
                            <span x-show="isAccepting" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ __('Processing...') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div x-show="showRejectModal" 
         x-cloak
         class="fixed inset-0 z-[1000] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRejectModal = false"></div>
        
        <div class="bg-white dark:bg-[#1A1A31] w-full max-w-2xl rounded-[1.5rem] overflow-hidden shadow-2xl relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <button @click="showRejectModal = false" class="absolute top-6 {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} text-slate-400 hover:text-slate-600 dark:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="p-10">
                <div class="mb-10 {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                    <h3 class="text-2xl font-black text-[#1A1A31] dark:text-white mb-2">{{ __('Reject Technician Request') }}</h3>
                    <p class="text-slate-400 font-bold text-sm">
                        {{ __('please_specify_rejection_reason_hint') }}
                    </p>
                </div>

                <form :action="'{{ url('admin/technician-requests') }}/' + rejectTargetId + '/refuse'" method="POST" class="space-y-8">
                    @csrf
                    <div>
                        <label class="text-sm font-black text-[#1A1A31] dark:text-white block mb-3">{{ __('Rejection Reason') }}</label>
                        <textarea name="rejection_reason" required rows="4" placeholder="{{ __('please_specify_rejection_reason_placeholder') }}"
                                  class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-md font-bold text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-primary/20 transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}"></textarea>
                    </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="showRejectModal = false" 
                            class="px-8 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-xl font-black hover:bg-slate-200 transition-all uppercase tracking-widest text-sm">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" 
                            class="px-8 py-4 bg-[#1A1A31] text-white rounded-xl font-black hover:bg-[#2A2A41] transition-all shadow-xl shadow-[#1A1A31]/20 uppercase tracking-widest text-sm">
                        {{ __('Send') }}
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div x-show="showSuccessModal" 
         x-cloak
         class="fixed inset-0 z-[1100] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showSuccessModal = false"></div>
        
        <div class="bg-white dark:bg-[#1A1A31] w-full max-w-2xl rounded-[1.5rem] overflow-hidden shadow-2xl relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <button @click="showSuccessModal = false" class="absolute top-6 {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} text-slate-400 hover:text-slate-600 dark:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="p-16 text-center">
                <div class="w-48 h-48 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-10 shadow-2xl shadow-green-500/40">
                    <svg class="w-32 h-32 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-3xl font-black text-[#1A1A31] dark:text-white mb-4">{{ __('Technician account activated') }}</h3>
                <p class="text-slate-400 font-bold text-lg">
                    {{ __('Technician account created and credentials sent successfully') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
```