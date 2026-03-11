@extends('layouts.admin')

@section('title', __('Content Management'))
@section('page_title', __('Content Management'))

@section('content')
<div class="space-y-8" dir="rtl">
    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Content Management') }}</h2>

        <div class="flex items-center gap-4">
            <form action="{{ route('admin.contents.index') }}" method="GET" class="relative group flex-1 md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}" class="w-full pr-12 pl-6 py-4 rounded-2xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/5 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none shadow-sm text-right">
                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-[#1A1A31] dark:group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>

            <a href="{{ route('admin.contents.create') }}" class="px-8 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/10 dark:shadow-white/5 hover:scale-[1.02] transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('Add Content') }}
            </a>
        </div>
    </div>

    {{-- Content List --}}
    <div class="space-y-8">
        @forelse($items as $content)
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 space-y-8 relative group hover:shadow-xl hover:shadow-[#1A1A31]/5 transition-all duration-500">
            {{-- Top Info --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $content->is_visible ? 'bg-green-50 text-green-500 dark:bg-green-500/10' : 'bg-red-50 text-red-500 dark:bg-red-500/10' }}">
                        {{ $content->is_visible ? __('Visible') : __('Hidden') }}
                    </span>
                    <span class="text-sm font-bold text-slate-400">{{ $content->created_at->format('j/n/Y') }}</span>
                </div>
                <h3 class="text-xl font-black text-[#1A1A31] dark:text-white">{{ app()->getLocale() == 'ar' ? $content->title_ar : $content->title_en }}</h3>
            </div>

            {{-- Slider Preview --}}
            <div x-data="{ 
                activeSlide: 0, 
                slides: {{ json_encode($content->items) }},
                get translation() {
                    return this.activeSlide * 100;
                }
            }" class="relative overflow-hidden group/slider">
                <div class="flex transition-transform duration-500 ease-out h-[350px] gap-6" 
                     :style="'transform: translateX(' + translation + '%)'">
                    @foreach($content->items as $item)
                    <div class="w-full md:w-[600px] flex-shrink-0 relative rounded-[2.5rem] overflow-hidden bg-slate-50 dark:bg-white/5 shadow-2xl group/card">
                        @if($item->image)
                            <img src="{{ $item->full_image_url }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-110">
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-[#1A1A31] to-slate-800 flex items-center justify-center">
                                <svg class="w-20 h-20 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        
                        {{-- New Overlay Layer --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent p-10 flex flex-col justify-end text-right">
                            <h4 class="text-2xl font-black text-white leading-tight drop-shadow-lg">{{ app()->getLocale() == 'ar' ? $item->title_ar : $item->title_en }}</h4>
                            <p class="text-sm font-bold text-white/80 max-w-sm mt-2 drop-shadow-md">{{ app()->getLocale() == 'ar' ? $item->description_ar : $item->description_en }}</p>
                            
                            <div class="mt-6 flex items-center justify-between">
                                @if($item->button_text_ar || $item->button_text_en)
                                <button class="px-8 py-3 bg-white text-[#1A1A31] rounded-2xl font-black text-xs shadow-2xl hover:bg-[#1A1A31] hover:text-white transition-all">
                                    {{ app()->getLocale() == 'ar' ? $item->button_text_ar : $item->button_text_en }}
                                </button>
                                @endif
                                <div class="w-10 h-10 rounded-full bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center text-white opacity-0 group-hover/card:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Slider Dots --}}
                <div class="flex items-center justify-center gap-2 pt-6">
                    <template x-for="(slide, index) in slides" :key="index">
                        <button @click="activeSlide = index" :class="activeSlide === index ? 'w-6 bg-[#1A1A31] dark:bg-white' : 'w-2 bg-slate-200 dark:bg-white/10'" class="h-2 rounded-full transition-all duration-300"></button>
                    </template>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-50 dark:border-white/5">
                <form action="{{ route('admin.contents.destroy', $content->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-8 h-12 flex items-center justify-center gap-3 bg-gray-200 dark:bg-white/5 text-slate-500 dark:text-white hover:text-red-500 rounded-2xl font-black text-sm transition-all whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.895-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        {{ __('Delete') }}
                    </button>
                </form>
                <a href="{{ route('admin.contents.edit', $content->id) }}" class="px-8 h-12 flex items-center justify-center gap-3 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm hover:scale-[1.02] transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    {{ __('Edit') }}
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 bg-white dark:bg-[#1A1A31] rounded-[2.5rem] text-center border border-slate-50 dark:border-white/5">
            <div class="w-20 h-20 bg-[#F8F9FE] dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h5 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('No content found') }}</h5>
            <p class="text-sm font-bold text-slate-400 mt-2">{{ __('Try adding a new app banner or content section') }}</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="flex items-center justify-center pt-8">
        {{ $items->links('vendor.pagination.custom-admin') }}
    </div>
    @endif
</div>
@endsection