@extends('layouts.admin')

@section('title', __('FAQs Management'))
@section('page_title', __('FAQs Management'))

@section('content')
<div x-data="{ 
    deleteModal: false,
    confirmUrl: '',
    selectedItems: [],
    showFilters: false,
    search: '{{ request('search') }}',
    targetGroup: '{{ request('target_group', 'all') }}',
    status: '{{ request('status', 'all') }}',
    sortBy: '{{ request('sort_by', 'newest') }}',
    per_page: '{{ request('per_page', 10) }}',

    toggleSelectAll() {
        if (this.selectedItems.length === {{ count($items) }}) {
            this.selectedItems = [];
        } else {
            this.selectedItems = [{{ implode(',', $items->pluck('id')->toArray()) }}];
        }
    },

    toggleItem(id) {
        if (this.selectedItems.includes(id)) {
            this.selectedItems = this.selectedItems.filter(i => i !== id);
        } else {
            this.selectedItems.push(id);
        }
    },

    confirmDelete(url) {
        this.confirmUrl = url;
        this.deleteModal = true;
    },

    confirmBulkDelete() {
        if (this.selectedItems.length > 0) {
            this.confirmUrl = '{{ route('admin.faqs.bulk-destroy') }}';
            this.deleteModal = true;
        }
    }
}" class="space-y-6">

    <!-- Page Header (Image Title) -->
    <div class="flex flex-col items-center justify-center py-6 text-center">
        <h2 class="text-3xl font-black text-slate-800 dark:text-white">{{ __('Frequently Asked Questions') }}</h2>
    </div>

    <!-- Actions Bar -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-8">
     

        <div class="flex items-center gap-3 w-full md:max-w-xl ">
            <!-- Filter Button & Dropdown -->
            <div class="relative" x-data="{ localSortBy: '{{ request('sort_by', 'newest') }}', localTargetGroup: '{{ request('target_group', 'all') }}', localStatus: '{{ request('status', 'all') }}' }">
                <button @click="showFilters = !showFilters" class="w-12 h-12 flex items-center justify-center bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-400 hover:text-primary transition-all relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    @if(request('target_group') || request('status') || request('sort_by'))
                    <div class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-slate-900"></div>
                    @endif
                </button>

                <!-- Floating Filter Popover -->
                <div x-show="showFilters" 
                     @click.away="showFilters = false" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="scale-95 translate-y-2 opacity-0" 
                     x-transition:enter-end="scale-100 translate-y-0 opacity-100"
                     class="absolute top-14 {{ app()->getLocale() == 'en' ? 'left-0' : 'right-0' }} w-72 bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/5 z-[100] p-6 space-y-6">
                    
                    <form action="{{ route('admin.faqs.index') }}" method="GET" class="space-y-6">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        
                        <!-- Sort By -->
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">{{ __('Sort By') }}</h4>
                            <div class="space-y-3">
                                @foreach(['newest' => __('Newest'), 'oldest' => __('Oldest'), 'name' => __('Question (AR)')] as $val => $label)
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                    <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                         :class="localSortBy == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300 dark:border-white/10'">
                                        <input type="radio" name="sort_by" value="{{ $val }}" x-model="localSortBy" class="hidden">
                                        <template x-if="localSortBy == '{{ $val }}'">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </template>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Target Group -->
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">{{ __('Target Group') }}</h4>
                            <div class="space-y-3">
                                @foreach(['all' => __('All Groups'), 'clients' => __('Clients'), 'companies' => __('Companies'), 'technicians' => __('Technicians')] as $val => $label)
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                    <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                         :class="localTargetGroup == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300 dark:border-white/10'">
                                        <input type="radio" name="target_group" value="{{ $val }}" x-model="localTargetGroup" class="hidden">
                                        <template x-if="localTargetGroup == '{{ $val }}'">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </template>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">{{ __('Status') }}</h4>
                            <div class="space-y-3">
                                @foreach(['all' => __('All Status'), 'active' => __('Active'), 'inactive' => __('Inactive')] as $val => $label)
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                    <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                         :class="localStatus == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300 dark:border-white/10'">
                                        <input type="radio" name="status" value="{{ $val }}" x-model="localStatus" class="hidden">
                                        <template x-if="localStatus == '{{ $val }}'">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </template>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-white/5">
                            <a href="{{ route('admin.faqs.index', ['search' => request('search')]) }}" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-xl font-bold text-[10px] text-center uppercase tracking-widest hover:bg-slate-200 transition-all">
                                {{ __('Reset') }}
                            </a>
                            <button type="submit" class="flex-[2] px-2 py-3 bg-[#1A1A31] dark:bg-primary text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-500/10 hover:opacity-90 transition-all">
                                {{ __('Apply') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search -->
            <form action="{{ route('admin.faqs.index') }}" method="GET" class="relative flex-1">
                <input type="text" name="search" x-model="search"  class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl pl-12 pr-6 py-3 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <div class="absolute inset-y-0 {{ app()->getLocale() == 'ar' ? 'right-4' : 'left-4' }} flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <!-- Maintain other filter states -->
                <input type="hidden" name="target_group" :value="targetGroup">
                <input type="hidden" name="status" :value="status">
                <input type="hidden" name="sort_by" :value="sortBy">
                <input type="hidden" name="per_page" :value="per_page">
            </form>
        </div>

           <div class="flex items-center gap-3 w-full ">
            <a href="{{ route('admin.faqs.create') }}" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-3 bg-[#1A1A31] dark:bg-primary text-white rounded-xl font-bold shadow-lg shadow-indigo-500/10 hover:opacity-90 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('Add Question') }}
            </a>
            <a href="{{ route('admin.faqs.download') }}" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white rounded-xl font-bold hover:bg-slate-50 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                {{ __('Download') }}
            </a>
        </div>
    </div>


    <!-- Data Table -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden relative">
        <!-- Floating Selection Bar -->
        <div x-show="selectedItems.length > 0" x-transition class="absolute top-0 left-0 right-0 bg-primary/10 backdrop-blur-md border-b border-primary/20 p-4 z-40 flex items-center justify-between animate-in slide-in-from-top duration-300">
            <div class="flex items-center gap-4">
                <span class="text-xs font-black text-primary uppercase tracking-widest"><span x-text="selectedItems.length"></span> {{ __('selected') }}</span>
                <div class="h-4 w-px bg-primary/20"></div>
                <button @click="confirmBulkDelete()" class="text-xs font-black text-rose-500 hover:text-rose-600 uppercase tracking-widest transition-colors">
                    {{ __('Delete Selected') }}
                </button>
            </div>
            <button @click="selectedItems = []" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-50 dark:border-white/5">
                        <th class="py-6 px-8 w-12">
                            <label class="relative flex items-center cursor-pointer">
                                <input type="checkbox" @change="toggleSelectAll()" :checked="selectedItems.length === {{ count($items) }} && {{ count($items) }} > 0" class="sr-only peer">
                                <div class="w-5 h-5 bg-slate-100 dark:bg-white/5 border-2 border-slate-200 dark:border-white/10 rounded-md peer-checked:bg-primary peer-checked:border-primary transition-all"></div>
                                <svg class="absolute w-3 h-3 text-white left-[4px] opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </label>
                        </th>
                        <th class="py-6 px-6">{{ __('#') }}</th>
                        <th class="py-6 px-6">{{ __('Question') }}</th>
                        <th class="py-6 px-6">{{ __('Content') }}</th>
                        <th class="py-6 px-6">{{ __('Target Group') }}</th>
                        <th class="py-6 px-6">{{ __('Status') }}</th>
                        <th class="py-6 px-6">{{ __('Date') }}</th>
                        <th class="py-6 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="group hover:bg-slate-50 dark:hover:bg-white/5 transition-all border-b border-slate-50 dark:border-white/5 last:border-0" :class="selectedItems.includes({{ $item->id }}) ? 'bg-primary/5' : ''">
                        <td class="py-5 px-8">
                            <label class="relative flex items-center cursor-pointer">
                                <input type="checkbox" @change="toggleItem({{ $item->id }})" :checked="selectedItems.includes({{ $item->id }})" class="sr-only peer">
                                <div class="w-5 h-5 bg-slate-100 dark:bg-white/5 border-2 border-slate-200 dark:border-white/10 rounded-md peer-checked:bg-primary peer-checked:border-primary transition-all"></div>
                                <svg class="absolute w-3 h-3 text-white left-[4px] opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </label>
                        </td>
                        <td class="py-5 px-6">
                            <span class="font-black text-slate-800 dark:text-white">{{ $loop->iteration }}</span>
                        </td>
                        <td class="py-5 px-6">
                            <span class="text-slate-800 dark:text-white">{{ $item->question_ar }}</span>
                        </td>
                        <td class="py-5 px-6">
                            <span class="opacity-60">{{ __('Content') }}</span>
                        </td>
                        <td class="py-5 px-6">
                            <span class="text-slate-500 dark:text-slate-400 font-black uppercase text-[10px]">{{ __($item->target_group) }}</span>
                        </td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full {{ $item->status == 'active' ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                                <span class="uppercase tracking-widest text-[10px] px-3 py-2 rounded-2xl font-black {{ $item->status == 'active' ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500' }}">
                                    {{ __($item->status == 'active' ? 'Active' : 'Inactive') }}
                                </span>
                            </div>
                        </td>
                        <td class="py-5 px-6">
                            <span class="text-slate-400 font-black text-[10px]">{{ $item->created_at->format('j/n/Y') }}</span>
                        </td>
                        <td class="py-5 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.faqs.edit', $item->id) }}" class="p-2 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:bg-primary/10 hover:text-primary transition-all group active:scale-90">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <a href="{{ route('admin.faqs.show', $item->id) }}" class="p-2 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-500 transition-all active:scale-90">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <button type="button" @click="confirmDelete('{{ route('admin.faqs.destroy', $item->id) }}')" class="p-2 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-500 transition-all active:scale-90">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-32 text-center">
                            <div class="flex flex-col items-center justify-center opacity-20">
                                <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9l-.707.707M16.243 19l-.707-.707M12 21v-1"></path></svg>
                                <p class="text-xl font-black uppercase tracking-[0.2em]">{{ __('No items found') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination -->
        <div class="p-8 bg-slate-50/50 dark:bg-white/[0.02] border-t border-slate-50 dark:border-white/5 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4 order-2 md:order-1">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    {{ __('Rows per page:') }}
                </span>
                <div class="relative inline-block" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-white/10 border border-slate-200 dark:border-white/10 rounded-lg text-[10px] font-black text-slate-600 dark:text-white transition-all">
                        <span x-text="per_page"></span>
                        <svg class="w-3 h-3 text-slate-300 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute bottom-full mb-2 left-0 w-20 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-xl shadow-2xl z-50 overflow-hidden">
                        @foreach([10, 25, 50, 100] as $val)
                        <a href="{{ request()->fullUrlWithQuery(['per_page' => $val]) }}" class="block px-4 py-2 text-[10px] font-black text-slate-600 dark:text-white hover:bg-slate-50 dark:hover:bg-white/5 transition-all {{ $items->perPage() == $val ? 'bg-primary/10 text-primary' : '' }}">
                            {{ $val }}
                        </a>
                        @endforeach
                    </div>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">
                    {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} {{ __('of') }} {{ $items->total() }}
                </span>
            </div>

            <div class="flex items-center gap-2 order-1 md:order-2">
                @if($items->onFirstPage())
                    <button disabled class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-300 cursor-not-allowed">
                        <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                @else
                    <a href="{{ $items->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white hover:bg-slate-50 transition-all shadow-sm active:scale-90">
                        <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                @endif

                <div class="px-5 py-2.5 bg-primary rounded-xl shadow-lg shadow-primary/20">
                    <span class="text-xs font-black text-white">{{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
                </div>

                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white hover:bg-slate-50 transition-all shadow-sm active:scale-90">
                        <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @else
                    <button disabled class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-300 cursor-not-allowed">
                        <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                @endif
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
                        <span x-show="selectedItems.length > 0">
                            {{ __('Are you sure you want to delete the selected') }} <span x-text="selectedItems.length"></span> {{ __('items?') }}
                        </span>
                        <span x-show="selectedItems.length === 0">
                            {{ __('Are you sure you want to delete this item?') }}
                        </span>
                        <br>
                    </p>

                    <div class="flex gap-4">
                        <button @click="deleteModal = false" 
                                class="flex-1 py-4 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 rounded-2xl text-sm font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                            {{ __('Cancel') }}
                        </button>
                        <form :action="confirmUrl" method="POST" class="flex-1">
                            @csrf
                            <template x-if="selectedItems.length === 0">
                                <input type="hidden" name="_method" value="DELETE">
                            </template>
                            <template x-if="selectedItems.length > 0">
                                <template x-for="id in selectedItems" :key="id">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>
                            </template>
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
