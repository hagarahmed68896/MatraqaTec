@extends('layouts.admin')

@section('title', __('Technicians Management'))
@section('page_title', __('Technicians Management'))

@section('content')
<div class="space-y-6 pb-20" x-data="{ 
    selectedIds: [], 
    showFilters: false,
    
    // Block Modal State
    showBlockModal: false,
    blockTargetId: null,
    blockAction: '',
    
    confirmBlock(id, currentStatus) {
        this.blockTargetId = id;
        this.blockAction = (currentStatus === 'active') ? '{{ __('Block') }}' : '{{ __('Unblock') }}';
        this.showBlockModal = true;
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('All Technicians') }}</h2>
            <p class="text-sm text-slate-500 dark:text-white/50">{{ __('View and manage all technicians in the system') }}</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Technicians -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-6 border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-4">{{ __('Total Technicians') }}</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total_technicians'] ?? 0) }}</span>
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                     <div class="mt-4 flex items-center gap-2">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-green-500/10 text-[12px] font-bold text-green-500">0.43%</span>
                        <span class="text-[12px] font-bold text-slate-400">{{ __('Compared to last week') }}</span>
                    </div>
                </div>
                <div class="w-24 h-16">
                     <svg viewBox="0 0 100 40" class="w-full h-full text-green-500 overflow-visible">
                        <path d="M0,35 Q10,10 20,30 T40,15 T60,35 T80,10 T100,25" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke" />
                        <path d="M0,35 Q10,10 20,30 T40,15 T60,35 T80,10 T100,25 L100,40 L0,40 Z" fill="currentColor" fill-opacity="0.1" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Technicians -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-6 border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group">
            <div class="flex items-start justify-between">
                <div>
                     <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-4">{{ __('Active Technicians') }}</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['active_technicians'] ?? 0) }}</span>
                         <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-green-500/10 text-[12px] font-bold text-green-500">0.43%</span>
                        <span class="text-[12px] font-bold text-slate-400">{{ __('Compared to last week') }}</span>
                    </div>
                </div>
                <div class="w-24 h-16">
                     <svg viewBox="0 0 100 40" class="w-full h-full text-indigo-500 overflow-visible">
                        <path d="M0,30 Q15,5 30,25 T60,10 T90,35 T100,20" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke" />
                         <path d="M0,30 Q15,5 30,25 T60,10 T90,35 T100,20 L100,40 L0,40 Z" fill="currentColor" fill-opacity="0.1" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-6 border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-4">{{ __('Completed Orders') }}</h3>
                     <div class="flex items-center gap-3">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['total_completed_orders'] ?? 0) }}</span>
                         <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                    </div>
                     <div class="mt-4 flex items-center gap-2">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-green-500/10 text-[12px] font-bold text-green-500">0.43%</span>
                        <span class="text-[12px] font-bold text-slate-400">{{ __('Compared to last week') }}</span>
                    </div>
                </div>
                 <div class="w-24 h-16">
                     <svg viewBox="0 0 100 40" class="w-full h-full text-blue-500 overflow-visible">
                        <path d="M0,35 Q10,5 25,25 T50,15 T75,30 T100,5" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke" />
                         <path d="M0,35 Q10,5 25,25 T50,15 T75,30 T100,5 L100,40 L0,40 Z" fill="currentColor" fill-opacity="0.1" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Average Rating -->
         <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-6 border border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group">
            <div class="flex items-start justify-between">
                <div>
                     <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-4">{{ __('Average Rating') }}</h3>
                     <div class="flex items-center gap-3">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['average_rating'] ?? 0, 1) }}</span>
                        <div class="w-8 h-8 rounded-lg bg-yellow-500/10 flex items-center justify-center text-yellow-500">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                         <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-green-500/10 text-[12px] font-bold text-green-500">0.43%</span>
                         <span class="text-[12px] font-bold text-slate-400">{{ __('Compared to last week') }}</span>
                    </div>
                </div>
                 <div class="w-24 h-16">
                     <svg viewBox="0 0 100 40" class="w-full h-full text-yellow-500 overflow-visible">
                        <path d="M0,20 Q20,35 40,10 T70,30 T100,10" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke" />
                         <path d="M0,20 Q20,35 40,10 T70,30 T100,10 L100,40 L0,40 Z" fill="currentColor" fill-opacity="0.1" />
                    </svg>
                </div>
            </div>
        </div>
    </div>


    <!-- Main Container with Filters & Actions -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-visible">
        <div class="p-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Filter & Search -->
             <div class="flex items-center gap-3 w-full md:w-auto">
                <!-- Filter Button -->
                <div class="relative">
                    <button @click="showFilters = !showFilters"
                        class="w-12 h-12 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 text-slate-400 hover:text-primary transition shadow-sm">
                       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                        </svg>
                    </button>

                    <!-- Filter Dropdown -->
                    <div x-show="showFilters" 
                         @click.away="showFilters = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-3 w-80 bg-white dark:bg-[#1A1A31] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-6 text-start">
                        
                        <form action="{{ url()->current() }}" method="GET" class="space-y-6">
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <!-- Sort By -->
                            <div x-data="{ selectedSort: '{{ request('sort_by', 'newest') }}' }">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-4">{{ __('Sort by') }}</label>
                                <div class="space-y-3">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="sort_by" value="all" x-model="selectedSort" class="w-5 h-5 border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20 bg-transparent">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">{{ __('All') }}</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="sort_by" value="name" x-model="selectedSort" class="w-5 h-5 border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20 bg-transparent">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">{{ __('Name') }}</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="sort_by" value="newest" x-model="selectedSort" class="w-5 h-5 border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20 bg-transparent">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">{{ __('Newest') }}</span>
                                    </label>
                                     <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="sort_by" value="oldest" x-model="selectedSort" class="w-5 h-5 border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20 bg-transparent">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">{{ __('Oldest') }}</span>
                                    </label>
                                </div>
                            </div>

                            <hr class="border-slate-100 dark:border-white/5">

                            <!-- Status -->
                            <div x-data="{ selectedStatus: '{{ request('status') }}' }">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-4">{{ __('Status:') }}</label>
                                <div class="space-y-3">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="status" value="" x-model="selectedStatus" class="w-5 h-5 border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20 bg-transparent">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">{{ __('All') }}</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="status" value="active" x-model="selectedStatus" class="w-5 h-5 border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20 bg-transparent">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">{{ __('Active') }}</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="status" value="inactive" x-model="selectedStatus" class="w-5 h-5 border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20 bg-transparent">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">{{ __('Inactive') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button type="submit" class="flex-1 py-3 bg-[#1A1A31] text-white text-sm font-bold rounded-xl hover:bg-black transition-all shadow-lg">{{ __('Apply') }}</button>
                                <a href="{{ url()->current() }}" class="px-4 py-3 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-white/10 transition-all text-center">{{ __('Reset') }}</a>
                            </div>
                        </form>
                    </div>
                </div>

                 <!-- Search -->
                <form action="{{ url()->current() }}" method="GET" class="relative w-72" x-data="{ search: '{{ request('search') }}' }">
                    @foreach(request()->except('search','page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <input type="text"
                        name="search"
                        x-model="search"
                        placeholder="{{ __('Search...') }}"
                        class="w-full pr-10 pl-4 py-3 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-sm font-bold text-slate-800 dark:text-white placeholder-slate-400 outline-none focus:ring-2 focus:ring-primary/20 transition-all">

                    <button type="submit"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Actions (Add + Download) -->
            <div class="flex items-center gap-3 shrink-0">
                 <a href="{{ route('admin.technicians.create') }}"
                   class="flex items-center gap-2 px-5 py-3 bg-[#1A1A31] text-white text-sm font-bold rounded-xl hover:bg-black transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add New Account') }}
                </a>

                <a href="{{ route('admin.technicians.download') . '?' . http_build_query(request()->all()) }}" 
                   class="flex items-center gap-2 px-5 py-3 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V3"/>
                    </svg>
                    {{ __('Download') }}
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[400px]">
             <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[12px] font-bold font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                        <th class="py-4 px-6 text-center">#</th>
                        <th class="py-4 px-6">{{ __('Technician') }}</th>
                        <th class="py-4 px-6 text-center">{{ __('Tech Type') }}</th>
                        <th class="py-4 px-6">{{ __('Service Name') }}</th>
                        <th class="py-4 px-6 text-center">{{ __('Orders Count') }}</th>
                        <th class="py-4 px-6">{{ __('Average Rating') }}</th>
                        <th class="py-4 px-6 text-center">{{ __('Status') }}</th>
                        <th class="py-4 px-6">{{ __('Date') }}</th>
                        <th class="py-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr @click="window.location.href = '{{ route('admin.technicians.show', $item->id) }}'" class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all group cursor-pointer">
                        <td class="py-4 px-6 text-center opacity-50">{{ $loop->iteration }}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-white font-black overflow-hidden group-hover:ring-4 ring-primary/20 transition-all shadow-md shadow-primary/20">
                                    @if($item->image)
                                        <img src="{{ asset('storage/'.$item->image) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ mb_substr($item->name_ar, 0, 1) }}
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-slate-900 dark:text-white font-black group-hover:text-primary transition-colors">{{ $item->name_ar }}</span>
                                    <span class="text-[9px] opacity-70 font-mono">{{ $item->user->phone ?? '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-center text-[10px] uppercase">
                            {{ $item->maintenanceCompany ? __('Corporate') : __('Independent') }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 text-[9px] font-black">
                                {{ $item->service->name_ar ?? '-' }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center opacity-80">{{ $item->orders_count ?? 0 }}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-1.5">
                                <span class="text-yellow-400 flex">
                                    @php $rating = round($item->reviews()->avg('rating') ?? 0); @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3.5 h-3.5 {{ $i <= $rating ? 'fill-current' : 'text-slate-100 dark:text-white/10 fill-none' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endfor
                                </span>
                                <span class="text-[10px] opacity-70">{{ number_format($item->reviews()->avg('rating') ?? 0, 1) }}</span>
                            </div>
                        </td>
                         <td class="py-4 px-6 text-center">
                            <span class="px-3 py-1 rounded-full text-[12px] font-black uppercase
                                {{ ($item->user->status ?? '') == 'active' ? 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">
                                {{ __($item->user->status ?? 'active') }}
                            </span>
                        </td>
                         <td class="py-4 px-6 opacity-50 text-[10px]">{{ $item->created_at->format('Y/m/d') }}</td>
                        <td class="py-4 px-6 text-center" @click.stop>
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" @click="confirmBlock({{ $item->id }}, '{{ $item->user->status ?? 'active' }}')" 
                                        class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                                    
                                    @if(($item->user->status ?? 'active') == 'active')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-5 h-5" viewBox="0 0 16 16">
                                            <path d="M13.879 10.414a2.501 2.501 0 0 0-3.465 3.465zm.707.707-3.465 3.465a2.501 2.501 0 0 0 3.465-3.465m-4.56-1.096a3.5 3.5 0 1 1 4.949 4.95 3.5 3.5 0 0 1-4.95-4.95ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-5 h-5" viewBox="0 0 16 16">
                                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                                            <path d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4"/>
                                        </svg>
                                    @endif
                                </button>

                                <a href="{{ route('admin.technicians.edit', $item->id) }}" 
                                   class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                         <td colspan="9" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-4">
                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <p>{{ __('No technicians found') }}</p>
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
</div>

    <!-- Block Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showBlockModal" 
             class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="showBlockModal = false"
                 class="bg-white dark:bg-[#1A1A31] w-full max-w-md rounded-[2.5rem] overflow-hidden shadow-2xl border border-slate-100 dark:border-white/10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                
                <div class="p-8 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-500/10 text-red-500 mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-2" x-text="blockAction + ' {{ __('Technician') }}'"></h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold mb-8">
                        {{ __('Are you sure you want to change the status of this technician?') }}
                    </p>
                    
                    <div class="flex flex-col gap-3">
                        <form :action="'{{ url('admin/technicians') }}/' + blockTargetId + '/toggle-block'" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-4 bg-red-500 text-white rounded-2xl font-black hover:bg-red-600 transition-all shadow-lg shadow-red-500/20 uppercase tracking-widest text-sm">
                                <span x-text="'{{ __('Confirm') }} ' + blockAction"></span>
                            </button>
                        </form>
                        <button type="button" @click="showBlockModal = false" 
                                class="w-full py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all uppercase tracking-widest text-sm">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection