@extends('layouts.admin')

@section('title', __('Term Details') . ' - ' . __('MatraqaTec'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.terms.index') }}" class="w-12 h-12 bg-white dark:bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 hover:text-primary dark:hover:text-white hover:bg-primary/5 transition-all shadow-sm">
                <svg class="w-6 h-6 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Term') }}</h1>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.terms.edit', $item->id) }}" class="px-6 py-3 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 rounded-xl text-sm font-black hover:bg-slate-200 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                {{ __('Edit') }}
            </a>
        </div>
    </div>

    <!-- Content Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-12 shadow-sm border border-slate-100 dark:border-white/5 relative overflow-hidden">
        
        <div class="relative space-y-10">
            <!-- Header Info -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-50 dark:border-white/5 pb-10">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-8 bg-primary rounded-full"></span>
                        <h2 class="text-3xl font-black text-slate-800 dark:text-white">{{ $item->title_ar }}</h2>
                    </div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="px-4 py-2 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-wider">
                            {{ __('Target Group:') }} {{ __($item->target_group == 'all' ? 'All' : ucfirst($item->target_group)) }}
                        </span>
                        @if($item->status == 'active')
                        <span class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-lg shadow-emerald-500/20">
                            {{ __('Active') }}
                        </span>
                        @else
                        <span class="px-4 py-2 bg-rose-500 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-lg shadow-rose-500/20">
                            {{ __('Inactive') }}
                        </span>
                        @endif
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ $item->created_at->format('j/n/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="space-y-6">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Description') }}</h3>
                <div class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400 leading-relaxed font-bold text-lg">
                    {!! nl2br(e($item->content_ar)) !!}
                </div>
            </div>

            <!-- English Version (If needed) -->
            <div class="pt-10 border-t border-slate-50 dark:border-white/5 space-y-6">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('English Content') }}</h3>
                <div class="prose prose-slate dark:prose-invert max-w-none text-slate-500 dark:text-slate-500 leading-relaxed font-bold">
                    <h4 class="text-xl font-bold mb-4">{{ $item->title_en }}</h4>
                    {!! nl2br(e($item->content_en)) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection