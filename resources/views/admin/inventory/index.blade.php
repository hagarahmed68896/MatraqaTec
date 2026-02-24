@extends('layouts.admin')

@section('title', __('Inventory Management'))

@section('content')
<div class="space-y-8" dir="rtl">
    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ __('Inventory Management') }}</h2>
        </div>
        
        <div class="flex items-center gap-4">
            <form action="{{ route('admin.inventory.index') }}" method="GET" class="relative group">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}" 
                    class="w-full md:w-80 px-12 py-4 rounded-2xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none group-hover:border-slate-200 dark:group-hover:border-white/20">
                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-[#1A1A31] dark:group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>

            <a href="{{ route('admin.inventory.create') }}" class="px-8 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/20 dark:shadow-white/5 hover:scale-[1.02] transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('Add Item') }}
            </a>
        </div>
    </div>

    {{-- Items Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        @forelse($items as $item)
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-6 shadow-sm border border-slate-50 dark:border-white/5 group hover:shadow-xl hover:shadow-[#1A1A31]/5 dark:hover:shadow-white/5 transition-all relative overflow-hidden">
            {{-- Status Badge --}}
            <div class="absolute top-6 right-6 z-10">
                <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest
                    {{ $item->status == 'available' ? 'bg-green-50 text-green-500 dark:bg-green-500/10' : 'bg-red-50 text-red-500 dark:bg-red-500/10' }}">
                    {{ $item->status == 'available' ? __('Available') : __('Out of Stock') }}
                </span>
            </div>

            {{-- Image & Info --}}
            <div class="flex flex-col items-center text-center mt-8 space-y-4">
                <div class="w-32 h-32 rounded-3xl bg-slate-50 dark:bg-white/5 p-4 flex items-center justify-center group-hover:scale-105 transition-transform overflow-hidden">
                    @if($item->image)
                        <img src="{{ asset($item->image) }}" alt="{{ $item->name_ar }}" class="w-full h-full object-contain">
                    @else
                        <svg class="w-12 h-12 text-slate-200 dark:text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    @endif
                </div>
                
                <div class="space-y-1">
                    <h3 class="text-xl font-black text-[#1A1A31] dark:text-white leading-tight">
                        {{ app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en }}
                    </h3>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-center gap-2 text-slate-400 dark:text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="text-xs font-bold">{{ $item->quantity }} {{ __('Pieces') }}</span>
                    </div>

                    <div class="flex items-center justify-center gap-1.5 bg-slate-50 dark:bg-white/5 px-4 py-1.5 rounded-xl border border-slate-100 dark:border-white/5">
                        <span class="text-sm mx-2 font-black text-[#1A1A31] dark:text-white">{{ number_format($item->price, 0) }}</span>
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-3 h-3 opacity-40 dark:invert">
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-8 pt-6 border-t border-slate-50 dark:border-white/5 flex items-center gap-2">
                <a href="{{ route('admin.inventory.edit', $item->id) }}" class="flex-1 flex items-center justify-center gap-2 py-3 bg-[#1A1A31] dark:bg-white/10 text-white dark:text-white rounded-2xl font-black text-sm hover:translate-y-[-2px] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    {{ __('Edit') }}
                </a>
                <form action="{{ route('admin.inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')" class="flex-shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-12 h-11 flex items-center justify-center bg-red-50 dark:bg-red-500/10 text-red-500 rounded-2xl hover:bg-red-100 dark:hover:bg-red-500/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 flex flex-col items-center justify-center text-center">
            <div class="w-24 h-24 rounded-full bg-slate-50 dark:bg-white/5 flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-slate-200 dark:text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <h3 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('No inventory items found') }}</h3>
            <p class="text-sm font-bold text-slate-400 dark:text-slate-500 mt-2">{{ __('Try adjusting your search or add a new item to the inventory') }}</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="mt-8">
        {{ $items->links() }}
    </div>
    @endif
</div>
@endsection
