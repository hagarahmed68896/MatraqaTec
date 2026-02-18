@extends('layouts.admin')

@section('title', __('Service Management'))
@section('page_title', __('Service Management'))

@section('content')
<div class="space-y-8" dir="rtl">
    
    {{-- Top Bar: Title, Search, Add Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <h2 class="text-2xl font-black text-[#1A1A31]">{{ __('Service Management') }}</h2>
        
        <div class="flex flex-1 max-w-2xl items-center gap-4">
            {{-- Search Bar --}}
            <div class="relative flex-1 group">
                <form action="{{ route('admin.services.index') }}" method="GET">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Search...') }}"
                           class="w-full pl-5 pr-12 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-bold shadow-sm focus:border-[#1A1A31] focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1A1A31] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
            </div>
            
            {{-- Add Button --}}
            <a href="{{ route('admin.services.create') }}" 
               class="flex items-center gap-2 px-6 py-3 bg-[#1A1A31] text-white rounded-2xl font-black text-sm shadow-lg shadow-[#1A1A31]/20 hover:scale-[1.02] transition-all whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add Service') }}
            </a>
        </div>
    </div>

    {{-- Services Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($items as $item)
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] overflow-hidden shadow-sm border border-slate-50 dark:border-white/5 group hover:shadow-xl dark:hover:shadow-white/5 transition-all duration-500 flex flex-col h-full">
            {{-- Image Section --}}
            <div class="relative h-72 overflow-hidden">
                @if($item->image)
                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                @else
                    <div class="w-full h-full bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-300 dark:text-slate-700">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
            </div>

            {{-- Content Section --}}
            <div class="p-6 flex-1 flex flex-col gap-4">
                {{-- Title & Price Row --}}
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 truncate">
                        @if($item->icon)
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center flex-shrink-0 border border-slate-100 dark:border-white/5">
                                <img src="{{ asset($item->icon) }}" class="w-6 h-6 object-contain" alt="icon">
                            </div>
                        @endif
                        <h3 class="text-lg font-black text-[#1A1A31] dark:text-white line-clamp-1 truncate">
                            {{ app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en }}
                        </h3>
                    </div>
                    
                    <div class="flex items-center gap-1.5 flex-shrink-0 bg-slate-50 dark:bg-white/5 px-3 py-1.5 rounded-xl border border-slate-100 dark:border-white/5">
                        <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ number_format($item->price) }}</span>
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-3.5 h-3.5 opacity-40 dark:invert">
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-2 gap-3">
                    {{-- الشركات - Companies --}}
                    <div class="bg-slate-50/80 dark:bg-white/5 rounded-2xl p-3 flex items-center justify-center gap-2 border border-transparent hover:border-slate-100 dark:hover:border-white/10 transition-colors">
                        <div class="w-5 h-5 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-md font-black text-[#1A1A31] dark:text-white">{{ $item->companies_count }}</span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-400 font-bold">{{ __('Companies') }}</span>
                        </div>
                    </div>

                    {{-- الفنيين - Technicians --}}
                    <div class="bg-slate-50/80 dark:bg-white/5 rounded-2xl p-3 flex items-center justify-center gap-2 border border-transparent hover:border-slate-100 dark:hover:border-white/10 transition-colors">
                        <div class="w-5 h-5 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-md font-black text-[#1A1A31] dark:text-white">{{ $item->technicians_count }}</span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-400 font-bold">{{ __('Technicians') }}</span>
                        </div>
                    </div>

                    {{-- الخدمات الفرعية - Sub-services --}}
                    <div class="bg-slate-50/80 dark:bg-white/5 rounded-2xl p-3 flex items-center justify-center gap-2 border border-transparent hover:border-slate-100 dark:hover:border-white/10 transition-colors">
                        <div class="w-5 h-5 rounded-lg bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-md font-black text-[#1A1A31] dark:text-white">{{ $item->children_count }}</span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-400 font-bold">{{ __('Sub-services') }}</span>
                        </div>
                    </div>

                    {{-- الطلبات - Orders --}}
                    <div class="bg-slate-50/80 dark:bg-white/5 rounded-2xl p-3 flex items-center justify-center gap-2 border border-transparent hover:border-slate-100 dark:hover:border-white/10 transition-colors">
                        <div class="w-5 h-5 rounded-lg bg-green-50 dark:bg-green-500/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-md font-black text-[#1A1A31] dark:text-white">{{ $item->orders_count }}</span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-400 font-bold">{{ __('Orders') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <a href="{{ route('admin.services.edit', $item->id) }}" 
                       class="flex-1 py-3 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-xs flex items-center justify-center gap-2 hover:bg-[#2a2a4d] dark:hover:bg-slate-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        {{ __('Edit') }}
                    </a>
                    
                    <form action="{{ route('admin.services.destroy', $item->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-500 transition-all border border-transparent hover:border-red-100 dark:hover:border-red-500/20" 
                                onclick="return confirm('{{ __('Are you sure?') }}')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.895-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 bg-white rounded-[3rem] border border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400">
            <div class="w-24 h-24 rounded-full bg-slate-50 flex items-center justify-center mb-6">
                <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <p class="text-xl font-black">{{ __('No services found') }}</p>
            <p class="text-sm font-bold mt-2 opacity-60">{{ __('Try adjusting your search or add a new service') }}</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="pt-8">
        {{ $items->links() }}
    </div>
    @endif
</div>
@endsection