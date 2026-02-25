@extends('layouts.admin')

@section('title', __('FAQ Details') . ' - ' . __('MatraqaTec'))

@section('content')
<div x-data="{ 
    deleteModal: false, 
    confirmUrl: '',
    confirmDelete(url) {
        this.confirmUrl = url;
        this.deleteModal = true;
    }
}" class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.faqs.index') }}" class="w-12 h-12 bg-white dark:bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 hover:text-primary hover:bg-primary/5 transition-all shadow-sm">
                <svg class="w-6 h-6 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('FAQ Details') }}</h1>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.faqs.edit', $item->id) }}" class="px-6 py-3 bg-white dark:bg-white/5 text-slate-600 dark:text-slate-400 border border-slate-100 dark:border-white/5 rounded-xl text-sm font-black hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                {{ __('Edit') }}
            </a>
            <button @click="confirmDelete('{{ route('admin.faqs.destroy', $item->id) }}')" class="px-6 py-3 bg-rose-50 dark:bg-rose-500/10 text-rose-500 rounded-xl text-sm font-black hover:bg-rose-500 hover:text-white transition-all flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                {{ __('Delete') }}
            </button>
        </div>
    </div>

    <!-- Content Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-slate-100 dark:border-white/5 relative overflow-hidden">
        
        <div class="relative space-y-12">
            <!-- Header Info -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-50 dark:border-white/5 pb-10">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-8 bg-primary rounded-full"></span>
                        <h2 class="text-3xl font-black text-slate-800 dark:text-white">{{ $item->question_ar }}</h2>
                    </div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="px-4 py-2 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-wider">
                            {{ __('Target Group:') }} {{ __($item->target_group) }}
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full {{ $item->status == 'active' ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                            <span class="px-4 py-2 {{ $item->status == 'active' ? 'bg-green-50 text-green-600 shadow-emerald-500/20' : 'bg-red-50 text-red-600 shadow-rose-500/20' }} rounded-xl text-[10px] font-black uppercase tracking-wider shadow-lg">
                                {{ __($item->status == 'active' ? 'Active' : 'Inactive') }}
                            </span>
                        </div>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ $item->created_at->format('j/n/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content Grid (AR/EN) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Arabic Version -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-primary/10 text-primary rounded-md text-[10px] font-black uppercase">AR</span>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Arabic Content') }}</h3>
                    </div>
                    
                    <div class="space-y-8">
                        <div>
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Question') }}</h4>
                            <p class="text-lg font-bold text-slate-700 dark:text-slate-200 leading-relaxed">{{ $item->question_ar }}</p>
                        </div>
                        <div>
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Answer') }}</h4>
                            <div class="bg-slate-50 dark:bg-white/5 p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 text-slate-600 dark:text-slate-400 leading-relaxed font-bold text-lg italic">
                                {!! nl2br(e($item->answer_ar)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- English Version -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-primary/10 text-primary rounded-md text-[10px] font-black uppercase">EN</span>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('English Content') }}</h3>
                    </div>
                    
                    <div class="space-y-8">
                        <div>
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Question') }}</h4>
                            <p class="text-lg font-bold text-slate-700 dark:text-slate-200 leading-relaxed">{{ $item->question_en }}</p>
                        </div>
                        <div>
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Answer') }}</h4>
                            <div class="bg-slate-50 dark:bg-white/5 p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 text-slate-600 dark:text-slate-400 leading-relaxed font-bold text-lg italic">
                                {!! nl2br(e($item->answer_en)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Delete Modal -->
    <template x-teleport="body">
        <div x-show="deleteModal" 
             class="fixed inset-0 z-[150] flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto"
             x-cloak>
            
            <!-- Backdrop -->
            <div x-show="deleteModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="deleteModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content -->
            <div x-show="deleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white dark:bg-[#1A1A31] w-full max-w-md rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden transform transition-all">
                
                <div class="p-10 text-center">
                    <!-- Icon Area -->
                    <div class="mx-auto w-24 h-24 bg-rose-50 dark:bg-rose-500/10 rounded-full flex items-center justify-center mb-8 relative">
                        <div class="absolute inset-0 rounded-full bg-rose-500/10 animate-ping"></div>
                        <svg class="w-10 h-10 text-rose-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>

                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-4">{{ __('Confirm Deletion') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold leading-relaxed mb-10">
                        {{ __('Are you sure you want to delete this item?') }}
                    </p>

                    <div class="flex gap-4">
                        <button @click="deleteModal = false" 
                                class="flex-1 py-4 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 rounded-2xl text-sm font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                            {{ __('Cancel') }}
                        </button>
                        <form :action="confirmUrl" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" 
                                    class="w-full py-4 bg-rose-500 text-white rounded-2xl text-sm font-black shadow-lg shadow-rose-500/30 hover:bg-rose-600 transition-all">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection