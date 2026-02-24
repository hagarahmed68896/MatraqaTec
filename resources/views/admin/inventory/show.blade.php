@extends('layouts.admin')

@section('title', __('Item Details'))

@section('content')
<div class="max-w-4xl mx-auto" dir="rtl">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.inventory.index') }}" class="w-10 h-10 flex items-center justify-center bg-white dark:bg-white/5 rounded-xl shadow-sm hover:bg-slate-50 dark:hover:bg-white/10 transition-colors">
                <svg class="w-6 h-6 text-slate-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
            <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Item Details') }}</h2>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.inventory.edit', $item->id) }}" class="px-6 py-3 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-xl font-black text-sm shadow-xl shadow-[#1A1A31]/20 dark:shadow-white/5 hover:scale-[1.02] transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                {{ __('Edit') }}
            </a>
            <form action="{{ route('admin.inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-3 bg-red-50 dark:bg-red-500/10 text-red-500 rounded-xl font-black text-sm hover:bg-red-100 dark:hover:bg-red-500/20 transition-all">
                    {{ __('Delete') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Details Card --}}
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-10 shadow-sm border border-slate-50 dark:border-white/5 grid grid-cols-1 md:grid-cols-3 gap-10">
        {{-- Image Column --}}
        <div class="flex flex-col items-center">
            <div class="w-full aspect-square rounded-[2rem] bg-slate-50 dark:bg-white/5 p-6 flex items-center justify-center overflow-hidden">
                @if($item->image)
                    <img src="{{ asset($item->image) }}" alt="{{ $item->name_ar }}" class="w-full h-full object-contain">
                @else
                    <svg class="w-20 h-20 text-slate-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                @endif
            </div>
            
            <div class="mt-6 text-center">
                <span class="px-6 py-2 rounded-2xl text-xs font-black uppercase tracking-widest
                    {{ $item->status == 'available' ? 'bg-green-50 text-green-500 dark:bg-green-500/10' : 'bg-red-50 text-red-500 dark:bg-red-500/10' }}">
                    {{ $item->status == 'available' ? __('Available') : __('Out of Stock') }}
                </span>
            </div>
        </div>

        {{-- Info Column --}}
        <div class="md:col-span-2 space-y-8">
            <div class="space-y-2">
                <h3 class="text-3xl font-black text-[#1A1A31] dark:text-white leading-tight">
                    {{ app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en }}
                </h3>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <div class="p-6 rounded-[2rem] bg-slate-50 dark:bg-white/5 space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Cost Price') }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ number_format($item->price, 2) }}</span>
                        <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-5 h-5 opacity-40 dark:invert">
                    </div>
                </div>

                <div class="p-6 rounded-[2rem] bg-slate-50 dark:bg-white/5 space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Quantity') }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ $item->quantity }}</span>
                        <span class="text-sm font-bold text-slate-400 dark:text-slate-500">{{ __('Pieces') }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h4 class="text-sm font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Metadata') }}</h4>
                <div class="space-y-2">
                    <div class="flex items-center justify-between py-2 border-b border-slate-50 dark:border-white/5">
                        <span class="text-xs font-bold text-slate-400">{{ __('Created At') }}</span>
                        <span class="text-xs font-bold text-[#1A1A31] dark:text-white">{{ $item->created_at->format('Y/m/d H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-50 dark:border-white/5">
                        <span class="text-xs font-bold text-slate-400">{{ __('Last Update') }}</span>
                        <span class="text-xs font-bold text-[#1A1A31] dark:text-white">{{ $item->updated_at->format('Y/m/d H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
