@extends('layouts.admin')

@section('title', __('Content Details'))
@section('page_title', __('Content Details'))

@section('content')
<div class="max-w-6xl mx-auto space-y-12 pb-20" dir="rtl">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-6">
            <a href="{{ route('admin.contents.index') }}" class="w-14 h-14 flex items-center justify-center rounded-[1.5rem] bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white shadow-sm border border-slate-100 dark:border-white/5 hover:scale-110 hover:shadow-xl transition-all duration-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path></svg>
            </a>
            <div class="space-y-1">
                <h2 class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ app()->getLocale() == 'ar' ? $item->title_ar : $item->title_en }}</h2>
                <div class="flex items-center gap-3">
                    <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $item->is_visible ? 'bg-green-50 text-green-500 dark:bg-green-500/10' : 'bg-red-50 text-red-500 dark:bg-red-500/10' }}">
                        {{ $item->is_visible ? __('Visible') : __('Hidden') }}
                    </span>
                    <span class="text-xs font-bold text-slate-400">
                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $item->created_at->format('j/n/Y - H:i') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.contents.edit', $item->id) }}" class="px-8 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/10 dark:shadow-white/5 hover:scale-[1.02] transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                {{ __('Edit Content') }}
            </a>
        </div>
    </div>

    {{-- Items Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach($item->items as $index => $banner)
        <div class="group relative bg-white dark:bg-[#1A1A31] rounded-[3rem] overflow-hidden shadow-sm border border-slate-50 dark:border-white/5 hover:shadow-2xl transition-all duration-500">
            {{-- Aspect Ratio Container for Image --}}
            <div class="aspect-[16/10] relative overflow-hidden">
                @if($banner->image)
                    <img src="{{ $banner->full_image_url }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                @else
                    <div class="absolute inset-0 bg-slate-100 dark:bg-white/5 flex items-center justify-center">
                        <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif

                {{-- Overlay for text --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent p-10 flex flex-col justify-end text-right">
                    <h4 class="text-2xl font-black text-white leading-tight mb-2">{{ app()->getLocale() == 'ar' ? $banner->title_ar : $banner->title_en }}</h4>
                    <p class="text-sm font-bold text-white/70 max-w-md line-clamp-2">{{ app()->getLocale() == 'ar' ? $banner->description_ar : $banner->description_en }}</p>
                </div>
            </div>

            {{-- Detail Info Section --}}
            <div class="p-10 space-y-8">
                <div class="grid grid-cols-2 gap-8 text-right">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('English Title') }}</label>
                        <p class="text-sm font-bold text-[#1A1A31] dark:text-white" dir="ltr">{{ $banner->title_en ?: '---' }}</p>
                    </div>
                    @if($banner->button_text_ar || $banner->button_text_en)
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('Action Button') }}</label>
                        <div class="flex items-center gap-2 justify-end">
                            <span class="px-4 py-1.5 bg-[#F8F9FE] dark:bg-white/5 text-[#1A1A31] dark:text-white rounded-xl text-xs font-black border border-slate-100 dark:border-white/10">
                                {{ app()->getLocale() == 'ar' ? $banner->button_text_ar : $banner->button_text_en }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Full Description Split --}}
                <div class="space-y-4 pt-4 border-t border-slate-50 dark:border-white/5">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('Arabic Description') }}</label>
                        <p class="text-sm font-bold text-[#1A1A31]/70 dark:text-white/60 leading-relaxed">{{ $banner->description_ar ?: '---' }}</p>
                    </div>
                    <div class="space-y-2" dir="ltr">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 text-left">{{ __('English Description') }}</label>
                        <p class="text-sm font-bold text-[#1A1A31]/70 dark:text-white/60 leading-relaxed text-left">{{ $banner->description_en ?: '---' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Empty State --}}
    @if($item->items->count() === 0)
    <div class="py-32 bg-white dark:bg-[#1A1A31] rounded-[3rem] text-center border border-slate-50 dark:border-white/5 shadow-sm">
        <div class="w-24 h-24 bg-[#F8F9FE] dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-8 text-slate-300">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <h5 class="text-xl font-black text-[#1A1A31] dark:text-white">{{ __('No banner items found') }}</h5>
        <p class="text-slate-400 mt-3 font-bold">{{ __('This content group is currently empty') }}</p>
    </div>
    @endif
</div>
@endsection