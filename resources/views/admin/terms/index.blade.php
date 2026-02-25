@extends('layouts.admin')

@section('title', __('Terms and Policies'))

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom duration-700" x-data="{ 
    deleteModal: false, 
    confirmUrl: '',
    selectedItems: [],
    confirmDelete(url) {
        this.confirmUrl = url;
        this.deleteModal = true;
    },
    toggleAll() {
        if (this.selectedItems.length === {{ $items->count() }}) {
            this.selectedItems = [];
        } else {
            this.selectedItems = {{ json_encode($items->pluck('id')->toArray()) }};
        }
    },
    confirmBulkDelete() {
        if (this.selectedItems.length > 0) {
            this.confirmUrl = '{{ route('admin.terms.bulk-destroy') }}';
            this.deleteModal = true;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Terms and Policies') }}</h2>
        
        <!-- Tab Switcher (Design Match) -->
        <div class="bg-white dark:bg-[#1A1A31] p-1 rounded-2xl flex items-center shadow-sm border border-slate-100 dark:border-white/5">
            <a href="{{ route('admin.terms.index') }}" class="px-8 py-2.5 rounded-xl text-sm font-black transition-all {{ request()->routeIs('admin.terms.*') ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-lg' : 'text-slate-400 hover:text-slate-600 dark:hover:text-white' }}">
                {{ __('Terms and Conditions') }}
            </a>
            <a href="{{ route('admin.privacy-policies.index') }}" class="px-8 py-2.5 rounded-xl text-sm font-black transition-all {{ request()->routeIs('admin.privacy-policies.*') ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-lg' : 'text-slate-400 hover:text-slate-600 dark:hover:text-white' }}">
                {{ __('Privacy Policy') }}
            </a>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm min-h-[600px] flex flex-col">
        
        <!-- Table Actions Bar -->
        <div class="p-6 border-b border-slate-100 dark:border-white/5 relative min-h-[88px] flex items-center">
            <!-- Search/Filter Bar (Visible when NO selection) -->
            <div x-show="selectedItems.length === 0" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="w-full flex flex-col md:flex-row items-center justify-between">
                
                <!-- Left Side: Add Button -->
                <div class="flex items-center gap-6 w-full md:w-auto">
                    <a href="{{ request()->routeIs('admin.terms.*') ? route('admin.terms.create') : route('admin.privacy-policies.create') }}" class="px-8 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-2xl text-sm font-black hover:opacity-90 transition-all flex items-center gap-3 shadow-xl shadow-primary/10 whitespace-nowrap">
                        <div class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        {{ request()->routeIs('admin.terms.*') ? __('Add Terms') : __('New Policy') }}
                    </a>
                </div>

                    <!-- Right Side: Search and Filter -->
                    <div class="flex items-center gap-3 w-full md:w-auto ">
                 <!-- Premium Filter Dropdown -->
                     <div class="relative" x-data="{ 
                         filterOpen: false,
                         sort: '{{ request('sort', 'all') }}',
                         target: '{{ request('target', 'all') }}',
                         status: '{{ request('status', 'all') }}'
                     }">
                        <button @click="filterOpen = !filterOpen" class="w-14 h-14 flex items-center justify-center bg-slate-50 dark:bg-white/5 text-slate-400 rounded-2xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all focus:ring-2 focus:ring-primary/20 relative" :class="{ 'bg-primary/10 text-primary': filterOpen }">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4.5h18m-18 5h18m-18 5h18m-18 5h18"></path></svg>
                            <!-- Red Dot Indicator -->
                            @if(request('status') || request('sort') || request('target'))
                            <div class="absolute top-3 right-4 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-slate-800"></div>
                            @endif
                        </button>

                        <div x-show="filterOpen" 
                             @click.away="filterOpen = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute {{ app()->getLocale() == 'en' ? 'right-0' : 'left-0' }} mt-4 w-[350px] bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/10 z-[110]"
                             x-cloak>
                            
                            <form action="{{ url()->current() }}" method="GET" class="p-6 space-y-6 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif

                                 <div class="space-y-4">
                                    <h4 class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-2">{{ __('Sort by:') }}</h4>
                                    <div class="space-y-3">
                                        @foreach(['all' => 'All', 'name' => 'Name', 'newest' => 'Newest', 'oldest' => 'Oldest'] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ __($label) }}</span>
                                            <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center p-0.5"
                                                 :class="sort == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300 dark:border-white/10'">
                                                 <input type="radio" name="sort" value="{{ $val }}" x-model="sort" class="absolute opacity-0 w-full h-full cursor-pointer appearance-none z-10">
                                                <template x-if="sort == '{{ $val }}'">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                </template>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                                 <div class="space-y-4">
                                    <h4 class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-2">{{ __('Target Group:') }}</h4>
                                    <div class="space-y-3">
                                        @foreach(['all' => 'All', 'clients' => 'Clients', 'companies' => 'Companies'] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ __($label) }}</span>
                                            <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center p-0.5"
                                                 :class="target == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300 dark:border-white/10'">
                                                 <input type="radio" name="target" value="{{ $val }}" x-model="target" class="absolute opacity-0 w-full h-full cursor-pointer appearance-none z-10">
                                                <template x-if="target == '{{ $val }}'">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                </template>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                                 <div class="space-y-4">
                                    <h4 class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-2">{{ __('Status:') }}</h4>
                                    <div class="space-y-3">
                                        @foreach(['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ __($label) }}</span>
                                            <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center p-0.5"
                                                 :class="status == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300 dark:border-white/10'">
                                                 <input type="radio" name="status" value="{{ $val }}" x-model="status" class="absolute opacity-0 w-full h-full cursor-pointer appearance-none z-10">
                                                <template x-if="status == '{{ $val }}'">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                </template>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Filter Footer -->
                                <div class="flex gap-3 pt-4 border-t border-slate-50 dark:border-white/5">
                                    <a href="{{ url()->current() }}" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all text-center">{{ __('Reset') }}</a>
                                    <button type="submit" class="flex-[2] py-3 bg-[#1A1A31] dark:bg-primary text-white rounded-xl text-xs font-black shadow-lg shadow-primary/20 hover:opacity-90 transition-all text-center">{{ __('Apply') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>   
                
                <div class="relative flex-1 md:min-w-[350px]">
                        <form action="{{ url()->current() }}" method="GET" id="searchForm">
                            @foreach(request()->only(['sort', 'target', 'status']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <button type="submit" class="absolute {{ app()->getLocale() == 'ar' ? 'left-4' : 'right-4' }} top-1/2 -translate-y-1/2 text-slate-300 hover:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                        </form>
                    </div>

                   
                </div>

                <!-- Spacer for Desktop -->
                <div class="hidden md:block h-10 w-px bg-slate-100 dark:bg-white/5 md:order-3"></div>
            </div>

            <!-- Bulk Selection Bar (Visible when items ARE selected) -->
            <div x-show="selectedItems.length > 0" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="w-full flex items-center justify-between bg-primary/5 dark:bg-primary/20 p-4 rounded-3xl border border-primary/20 animate-in fade-in zoom-in duration-300"
                 x-cloak>
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary text-white rounded-2xl flex items-center justify-center text-sm font-black shadow-lg shadow-primary/20">
                            <span x-text="selectedItems.length"></span>
                        </div>
                        <span class="text-sm font-black text-primary uppercase tracking-widest">{{ __('Selected Items') }}</span>
                    </div>
                    
                    <div class="h-6 w-px bg-primary/20"></div>

                    <button @click="selectedItems = []" class="text-xs font-black text-slate-400 hover:text-primary transition-all uppercase tracking-widest">
                        {{ __('Deselect All') }}
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="confirmBulkDelete()" class="px-8 py-4 bg-rose-500 text-white rounded-2xl text-sm font-black shadow-xl shadow-rose-500/20 hover:bg-rose-600 transition-all flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        {{ __('Delete Selected') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Premium Table -->
        <div class="flex-1 overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-50 dark:border-white/5">
                        <th class="py-6 px-8 w-12 text-center">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" 
                                       @click="toggleAll()" 
                                       :checked="selectedItems.length === {{ $items->count() }} && {{ $items->count() }} > 0"
                                       class="peer appearance-none w-6 h-6 rounded-lg border-2 border-slate-200 dark:border-white/10 checked:border-primary checked:bg-primary transition-all cursor-pointer">
                                <svg class="w-4 h-4 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </th>
                        <th class="py-6 px-4 w-12 text-center">#</th>
                        <th class="py-6 px-4">{{ __('Title') }}</th>
                        <th class="py-6 px-4">{{ __('Description') }}</th>
                        <th class="py-6 px-4">{{ __('Target Group') }}</th>
                        <th class="py-6 px-4">{{ __('Status') }}</th>
                        <th class="py-6 px-4">{{ __('Date') }}</th>
                        <th class="py-6 px-8 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-white/5 text-sm">
                    @forelse($items as $item)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-all" :class="{ 'bg-primary/[0.03] dark:bg-primary/[0.05]': selectedItems.includes({{ $item->id }}) }">
                        <td class="py-6 px-8 text-center">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" 
                                       value="{{ $item->id }}" 
                                       x-model.number="selectedItems"
                                       class="peer appearance-none w-6 h-6 rounded-lg border-2 border-slate-200 dark:border-white/10 checked:border-primary checked:bg-primary transition-all cursor-pointer">
                                <svg class="w-4 h-4 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </td>
                        <td class="py-6 px-4 text-center font-bold text-slate-400">{{ $loop->iteration }}</td>
                        <td class="py-6 px-4">
                            <span class="font-black text-slate-700 dark:text-white">{{ $item->title_ar }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="font-bold text-slate-400 dark:text-slate-500 whitespace-nowrap">{{ Str::limit(strip_tags($item->content_ar), 35) }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-4 py-2 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-wider">
                                {{ __($item->target_group == 'all' ? 'All' : ucfirst($item->target_group)) }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            @if($item->status == 'active')
                            <span class="px-4 py-2 bg-green-50 text-green-600 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-lg shadow-emerald-500/20">
                                {{ __('Active') }}
                            </span>
                            @else
                            <span class="px-4 py-2 bg-red-50 text-red-600 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-lg shadow-rose-500/20">
                                {{ __('Inactive') }}
                            </span>
                            @endif
                        </td>
                        <td class="py-6 px-4 font-bold text-slate-400 text-xs">
                            {{ $item->created_at->format('j/n/Y') }}
                        </td>
                        <td class="py-6 px-8">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ request()->routeIs('admin.terms.*') ? route('admin.terms.show', $item->id) : route('admin.privacy-policies.show', $item->id) }}" class="p-2.5 rounded-xl bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-primary hover:bg-primary/5 transition-all" title="{{ __('View') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ request()->routeIs('admin.terms.*') ? route('admin.terms.edit', $item->id) : route('admin.privacy-policies.edit', $item->id) }}" class="p-2.5 rounded-xl bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-primary hover:bg-primary/5 transition-all" title="{{ __('Edit') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <button type="button" @click="confirmDelete('{{ request()->routeIs('admin.terms.*') ? route('admin.terms.destroy', $item->id) : route('admin.privacy-policies.destroy', $item->id) }}')" class="p-2.5 rounded-xl bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all" title="{{ __('Delete') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-20 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-16 h-16 bg-slate-50 dark:bg-white/5 rounded-full flex items-center justify-center text-slate-200 dark:text-white/10 mb-2">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <span class="text-slate-400 font-bold uppercase tracking-widest text-xs">{{ __('No items found') }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-8 border-t border-slate-50 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs font-black text-slate-400">
                <span>{{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }} من {{ $items->total() }}</span>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 text-xs font-black text-slate-400 bg-slate-50 dark:bg-white/5 px-4 py-2 rounded-xl">
                    {{ __('صفحة') }} {{ $items->currentPage() }} {{ __('من') }} {{ $items->lastPage() }}
                </div>
                
                <div class="flex items-center gap-1">
                    @if($items->onFirstPage())
                    <button class="p-3 bg-slate-50 dark:bg-white/5 text-slate-200 cursor-not-allowed rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    @else
                    <a href="{{ $items->previousPageUrl() }}" class="p-3 bg-slate-50 dark:bg-white/5 text-slate-400 hover:bg-primary/5 hover:text-primary rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    @endif

                    @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" class="p-3 bg-slate-50 dark:bg-white/5 text-slate-400 hover:bg-primary/5 hover:text-primary rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    @else
                    <button class="p-3 bg-slate-50 dark:bg-white/5 text-slate-200 cursor-not-allowed rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    @endif
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