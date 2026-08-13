@extends('layouts.admin')

@section('title', __('Individual Customers'))
@section('page_title', __('Individual Customers'))

@section('content')
<div class="space-y-6 pb-20" x-data="{ 
    selectedIds: [], 
    showFilters: false,
    showDeleteModal: false,
    deleteTargetId: null,
    isBulkDelete: false,
    showBlockModal: false,

    confirmDelete(id = null) {
        this.deleteTargetId = id;
        this.isBulkDelete = id === null;
        this.showDeleteModal = true;
    },

    toggleAll(e) {
        if (e.target.checked) {
            this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(el => el.value);
        } else {
            this.selectedIds = [];
        }
    },

    async executeDelete() {
        if (this.isBulkDelete) {
            await this.performBulkDelete();
        } else {
            document.getElementById('delete-form-' + this.deleteTargetId).submit();
        }
    },

    async performBulkDelete() {
        try {
            const response = await fetch('{{ route('admin.individual-customers.bulk-destroy') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: this.selectedIds })
            });
            const result = await response.json();
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.message || 'Error occurred');
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred while deleting.');
        }
    },

    async executeBlock() {
        this.showBlockModal = false;
        try {
            const response = await fetch('{{ route('admin.individual-customers.bulk-block') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: this.selectedIds })
            });

            const result = await response.json();
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.message || 'Error occurred');
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred.');
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('Individual Customers') }}</h2>
            <p class="text-sm text-slate-500 dark:text-white/50 mt-1">{{ __('Manage individual user accounts and profiles') }}</p>
        </div>
    </div>

    <!-- Main Container with Filters & Actions -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-visible text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <div class="p-6 flex flex-col lg:flex-row items-center justify-between gap-6 relative min-h-[5rem]">

            <!-- Bulk Actions Bar (Visible when rows selected) -->
            <div x-show="selectedIds.length > 0" x-cloak class="absolute inset-0 bg-white dark:bg-[#1A1A31] z-20 flex items-center justify-between px-6 border-b border-slate-100 dark:border-white/5 animate-fade-in-up rounded-t-[2rem]">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-slate-500 bg-slate-100 dark:bg-white/5 dark:text-slate-300 px-4 py-3 rounded-2xl">
                        <span x-text="selectedIds.length"></span> {{ __('Selected Items') }}
                    </span>
                    <button @click="confirmDelete()" 
                            class="flex items-center gap-2 px-4 py-2 bg-red-500 text-white text-xs font-black rounded-lg hover:bg-red-600 transition-all shadow-md shadow-red-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('Delete Selected') }}
                    </button>
                    <button @click="showBlockModal = true" 
                            class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white text-xs font-black rounded-lg hover:bg-amber-600 transition-all shadow-md shadow-amber-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        {{ __('Block Selected') }}
                    </button>
                </div>
                <button @click="selectedIds = []" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Controls Row (Visible when no rows selected) -->
            <div x-show="selectedIds.length === 0" class="flex flex-col lg:flex-row items-center justify-between gap-4 w-full">

                <!-- Left Side: Filter & Search -->
                <div class="flex items-center gap-3 w-full lg:w-auto flex-1 min-w-0">
                    <!-- Filter Button -->
                    <div class="relative">
                        <button @click="showFilters = !showFilters"
                            class="w-12 h-12 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 text-slate-400 hover:text-primary dark:hover:text-white transition shadow-sm">
                           <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                        </button>

                        <!-- Filter Dropdown -->
                        <div x-show="showFilters" 
                             x-cloak
                             @click.away="showFilters = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} mt-3 w-80 bg-white dark:bg-[#1A1A31] rounded-3xl shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-6 lg:p-8">
                            
                            <form action="{{ route('admin.individual-customers.index') }}" method="GET" class="space-y-8">
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif

                                <!-- Sort By -->
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white flex items-center gap-2">
                                        {{ __('Sort by') }}:
                                    </label>
                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach(['latest' => 'Newest', 'oldest' => 'Oldest', 'name' => 'Name'] as $val => $key)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ __($key) }}</span>
                                            <div class="relative flex items-center">
                                                <input type="radio" name="sort_by" value="{{ $val }}" {{ request('sort_by', 'latest') == $val ? 'checked' : '' }}
                                                       class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-100 dark:bg-white/5"></div>

                                <!-- Status Filter -->
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-[#1A1A31] dark:text-white flex items-center gap-2">
                                        {{ __('Status') }}:
                                    </label>
                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach(['' => 'All', 'active' => 'Active', 'blocked' => 'Blocked'] as $val => $key)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ __($key) }}</span>
                                            <input type="radio" name="status" value="{{ $val }}" {{ request('status', '') == $val ? 'checked' : '' }}
                                                   class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-white/5">
                                    <a href="{{ route('admin.individual-customers.index') }}" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl text-center hover:bg-slate-200 transition-all">
                                        {{ __('Reset') }}
                                    </a>
                                    <button type="submit" class="flex-1 py-3 bg-[#1A1A31] text-white text-xs font-black rounded-xl hover:bg-[#2A2A41] transition-all shadow-lg shadow-[#1A1A31]/20">
                                        {{ __('Apply') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search -->
                    <form action="{{ route('admin.individual-customers.index') }}" method="GET" class="relative w-full max-w-[18rem] flex-1" x-data="{ search: '{{ request('search') }}' }">
                        @foreach(request()->except('search','page') as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $val)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $val }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <input type="text"
                            name="search"
                            x-model="search"
                            placeholder="{{ __('Search customer name, phone...') }}"
                            class="w-full {{ app()->getLocale() == 'ar' ? 'pr-10 pl-4' : 'pl-4 pr-10' }} py-3 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-md font-bold text-slate-800 dark:text-white placeholder-slate-400 outline-none focus:ring-2 focus:ring-primary/20 transition-all">

                        <button type="submit"
                            class="absolute {{ app()->getLocale() == 'ar' ? 'right-4' : 'right-4' }} top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary dark:hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Right Side: Actions (Add + Download) -->
                <div class="flex items-center gap-3 flex-wrap justify-center lg:justify-end shrink-0">
                    <a href="{{ route('admin.individual-customers.create') }}"
                       class="flex items-center gap-2 px-5 py-3 bg-[#1A1A31] 
                       text-white text-md font-bold rounded-xl hover:bg-black 
                       border-2 dark:border-white/10 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Add Customer') }}
                    </a>

                    <a href="{{ route('admin.individual-customers.download') . '?' . http_build_query(request()->all()) }}" 
                       class="flex items-center gap-2 px-5 py-3 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-md font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V3"/>
                        </svg>
                        {{ __('Download') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
                        <th class="py-4 px-6 text-center">
                            <input type="checkbox" @change="toggleAll($event)" class="w-5 h-5 border-2 border-slate-200 rounded-lg text-primary focus:ring-primary transition-all cursor-pointer">
                        </th>
                        <th class="py-4 px-6">{{ __('Customer') }}</th>
                        <th class="py-4 px-6">{{ __('Phone Number') }}</th>
                        <th class="py-4 px-6">{{ __('Email') }}</th>
                        <th class="py-4 px-6 text-center">{{ __('Status') }}</th>
                        <th class="py-4 px-6">{{ __('Date') }}</th>
                        <th class="py-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr @click="window.location.href = '{{ route('admin.individual-customers.show', $item->id) }}'" class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-all group cursor-pointer">
                        <td class="py-4 px-6 text-center" @click.stop>
                            <input type="checkbox" x-model="selectedIds" value="{{ $item->id }}" class="row-checkbox w-5 h-5 border-2 border-slate-200 rounded-lg text-primary focus:ring-primary transition-all cursor-pointer">
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-white font-black overflow-hidden group-hover:ring-4 ring-primary/20 transition-all shadow-md shadow-primary/20">
                                    <span class="text-md">{{ mb_substr($item->user->name, 0, 1) }}</span>
                                </div>
                                <span class="text-slate-900 dark:text-white font-black group-hover:text-primary dark:hover:text-white transition-colors">{{ $item->user->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 font-mono opacity-80 text-[12px]">{{ $item->user->phone ?? '-' }}</td>
                        <td class="py-4 px-6 opacity-70">{{ $item->user->email ?? '-' }}</td>
                        <td class="py-4 px-6 text-center">
                            <span class="px-3 py-1 rounded-full text-[12px] font-black uppercase tracking-wider whitespace-nowrap
                                {{ ($item->user->status ?? '') == 'active' ? 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">
                                {{ __($item->user->status ?? 'active') }}
                            </span>
                        </td>
                        <td class="py-4 px-6 opacity-50 text-[12px] whitespace-nowrap">{{ $item->created_at->format('Y/m/d') }}</td>
                        <td class="py-4 px-6 text-center" @click.stop>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.individual-customers.show', $item->id) }}" 
                                   class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition-all"
                                   title="{{ __('View') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('admin.individual-customers.edit', $item->id) }}" 
                                   class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition-all"
                                   title="{{ __('Edit') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.individual-customers.toggle-block', $item->user->id ?? $item->id) }}" class="inline" @click.stop onsubmit="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition-all" title="{{ $item->user->status === 'active' ? __('Block') : __('Unblock') }}">
                                        @if($item->user->status === 'active')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                                <div @click.stop>
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('admin.individual-customers.destroy', $item->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" @click="confirmDelete({{ $item->id }})" class="w-10 h-10 rounded-xl flex items-center justify-center text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all"
                                            title="{{ __('Delete') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-4">
                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p>{{ __('No customers found') }}</p>
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

    <!-- Delete Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showDeleteModal" 
             class="fixed inset-0 z-[1000] flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto"
             x-cloak>
            
            <!-- Backdrop -->
            <div x-show="showDeleteModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showDeleteModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content -->
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white dark:bg-[#1A1A31] w-full max-w-md rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden transform transition-all">
                
                <div class="p-10 text-center">
                    <div class="mx-auto w-24 h-24 bg-rose-50 dark:bg-rose-500/10 rounded-full flex items-center justify-center mb-8 relative">
                        <div class="absolute inset-0 rounded-full bg-rose-500/10 animate-ping"></div>
                        <svg class="w-10 h-10 text-rose-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-4">{{ __('Confirm Deletion') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold leading-relaxed mb-10">
                        <span x-show="isBulkDelete">{{ __('Are you sure you want to delete the selected customers?') }}</span>
                        <span x-show="!isBulkDelete">{{ __('Are you sure you want to delete this customer?') }}</span>
                    </p>
                    
                    <div class="flex gap-4">
                        <button type="button" @click="showDeleteModal = false" 
                                class="flex-1 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all uppercase tracking-widest text-md">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="executeDelete()" 
                                class="flex-1 py-4 bg-rose-500 text-white rounded-2xl font-black hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/20 uppercase tracking-widest text-md">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Block Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showBlockModal" 
             class="fixed inset-0 z-[1000] flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto"
             x-cloak>
            
            <!-- Backdrop -->
            <div x-show="showBlockModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showBlockModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content -->
            <div x-show="showBlockModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white dark:bg-[#1A1A31] w-full max-w-md rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden transform transition-all">
                
                <div class="p-10 text-center">
                    <div class="mx-auto w-24 h-24 bg-amber-50 dark:bg-amber-500/10 rounded-full flex items-center justify-center mb-8 relative">
                        <div class="absolute inset-0 rounded-full bg-amber-500/10 animate-ping"></div>
                        <svg class="w-10 h-10 text-amber-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-4">{{ __('Confirm Blocking') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold leading-relaxed mb-10">
                        <span>{{ __('Are you sure you want to block the selected customers?') }}</span>
                    </p>
                    
                    <div class="flex gap-4">
                        <button type="button" @click="showBlockModal = false" 
                                class="flex-1 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all uppercase tracking-widest text-md">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="executeBlock()" 
                                class="flex-1 py-4 bg-amber-500 text-white rounded-2xl font-black hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 uppercase tracking-widest text-md">
                            {{ __('Block Selected') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection