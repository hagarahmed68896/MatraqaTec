@extends('layouts.admin')

@section('title', __('Roles & Permissions'))
@section('page_title', __('Roles & Permissions'))

@section('content')
<div x-data="{ 
    selectedItems: [], 
    selectAll: false,
    showFilters: false,
    
    // Delete Modal State
    showDeleteModal: false,
    deleteTargetId: null,
    isBulkDelete: false,
    
    confirmDelete(id = null) {
        this.deleteTargetId = id;
        
        this.isBulkDelete = id === null;
        this.showDeleteModal = true;
    },

    async executeDelete() {
        if (this.isBulkDelete) {
            document.getElementById('bulk-delete-form').submit();
        } else {
            document.getElementById('delete-form-' + this.deleteTargetId).submit();
        }
    }
}" class="space-y-6 pb-20">

    <!-- Unified Container (Matching Screenshot 3) -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-visible text-right" dir="rtl">
        
        <!-- Controls Row -->
        <div class="p-6">
            <!-- Default Controls -->
            <div x-show="selectedItems.length === 0" x-transition class="flex flex-col md:flex-row items-center justify-between gap-6 w-full">
                <!-- Group 1: Add & Download (Right Side in RTL) -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('admin.roles.create') }}" class="flex items-center gap-2 px-6 py-3 bg-[#1A1A31] text-white text-md
                     font-bold rounded-xl hover:bg-black transition shadow-sm dark:border-white/10 dark:bg-white/5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('Add Role') }}
                    </a>
                    <a href="{{ route('admin.roles.download') }}" class="flex items-center gap-2 px-6 py-3 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-md font-bold rounded-xl dark:hover:bg-black transition shadow-sm bg-white dark:bg-white/5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        {{ __('Download') }}
                    </a>
                </div>

                <!-- Group 2: Search (Left Side in RTL) -->
                <div class="flex items-center gap-3 flex-1 md:max-w-2xl justify-end">
                    <!-- Search Input -->
                    <form action="{{ route('admin.roles.index') }}" method="GET" class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}" class="w-full pr-12 pl-4 py-3 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Actions (Show when items are selected) -->
            <div x-show="selectedItems.length > 0" x-cloak x-transition class="flex items-center justify-between w-full bg-slate-50 dark:bg-white/5 p-2 rounded-2xl border border-primary/20">
                <div class="flex items-center gap-4 px-4 text-primary">
                    <div class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center font-black shadow-lg shadow-primary/20">
                        <span x-text="selectedItems.length"></span>
                    </div>
                    <span class="text-sm font-black uppercase tracking-widest">{{ __('Selected Items') }}</span>
                </div>
                
                <div class="flex items-center gap-2">
                    <form id="bulk-delete-form" action="{{ route('admin.roles.bulk-delete') }}" method="POST" class="inline">
                        @csrf
                        <template x-for="id in selectedItems" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="button" @click="confirmDelete()" class="px-6 py-3 bg-red-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-600 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            {{ __('Delete') }}
                        </button>
                    </form>

                    <button @click="selectedItems = []; selectAll = false" class="px-6 py-3 bg-white dark:bg-[#1A1A31] text-slate-400 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-400 text-[11px] font-black uppercase tracking-[0.2em] border-b border-slate-50 dark:border-white/5">
                        <th class="py-5 px-6 text-right w-12">
                            <input type="checkbox" x-model="selectAll" @change="selectedItems = selectAll ? {{ json_encode($items->pluck('id')) }} : []" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary/20 transition-all cursor-pointer">
                        </th>
                        <th class="py-5 px-6 text-right">#</th>
                        <th class="py-5 px-6 text-right">{{ __('Role Name') }}</th>
                        <th class="py-5 px-6 text-right">{{ __('Description') }}</th>
                        <th class="py-5 px-6 text-right">{{ __('Supervisors Count') }}</th>
                        <th class="py-5 px-6 text-right">{{ __('Date') }}</th>
                        <th class="py-5 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-all group cursor-pointer">
                        <td class="py-5 px-6" @click.stop>
                            <input type="checkbox" :value="{{ $item->id }}" x-model="selectedItems" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary/20 transition-all cursor-pointer">
                        </td>
                        <td class="py-5 px-6 font-mono opacity-50">{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-primary flex items-center justify-center text-white font-black shadow-md">
                                    {{ mb_substr($item->name_ar ?? $item->name, 0, 1) }}
                                </div>
                                <span class="text-slate-900 dark:text-white font-black transition-colors text-sm">
                                    {{ $item->name_ar ?? $item->name }}
                                </span>
                            </div>
                        </td>
                        <td class="py-5 px-6 max-w-xs truncate opacity-70">
                            {{ $item->description_ar ?? $item->description ?? '-' }}
                        </td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                                    {{ $item->supervisors_count }}
                                </div>
                                <span class="text-[11px] opacity-60">{{ __('Supervisor') }}</span>
                            </div>
                        </td>
                        <td class="py-5 px-6 opacity-50 text-[12px]">{{ $item->created_at->format('j/n/Y') }}</td>
                        <td class="py-5 px-6 text-center" @click.stop>
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.roles.edit', $item->id) }}" title="{{ __('Edit') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-primary dark:hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form id="delete-form-{{ $item->id }}" action="{{ route('admin.roles.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click="confirmDelete({{ $item->id }})" title="{{ __('Delete') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-red-500 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-20 text-center text-slate-400 font-bold uppercase tracking-widest">{{ __('No roles found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
        <div class="p-6 border-t border-slate-100 dark:border-white/5">
            {{ $items->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <!-- Custom Pagination Footer Info (Matching Screenshot 3 bottom part) -->
    <div class="flex items-center justify-between px-8 text-slate-400 text-[11px] font-bold">
        <div>
            {{ __('Showing :from to :to of :total', ['from' => $items->firstItem(), 'to' => $items->lastItem(), 'total' => $items->total()]) }}
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span>{{ __('Rows per page:') }}</span>
                <div class="relative">
                    <select class="appearance-none bg-transparent border-none focus:ring-0 cursor-pointer pr-4 text-slate-600 dark:text-white font-black">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <svg class="absolute right-0 top-1/2 -translate-y-1/2 w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-primary dark:hover:text-white transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <span class="px-2">{{ $items->currentPage() }}/{{ $items->lastPage() }}</span>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-primary dark:hover:text-white transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showDeleteModal" 
             x-cloak
             class="fixed inset-0 z-[1000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>

            <div @click.away="showDeleteModal = false"
                 class="bg-white dark:bg-[#1A1A31] w-full max-w-md rounded-[2.5rem] overflow-hidden 
                 shadow-2xl border border-slate-200 dark:border-white/10 relative z-10 opacity-100"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                
                <div class="p-8 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-rose-500/10 text-rose-500 mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-2">{{ __('Confirm Delete') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold mb-8">
                        <span x-show="isBulkDelete">{{ __('Are you sure you want to delete selected roles?') }}</span>
                        <span x-show="!isBulkDelete">{{ __('Are you sure you want to delete this role?') }}</span>
                    </p>
                    
                    <div class="flex flex-col gap-3">
                        <button @click="executeDelete()" 
                                class="w-full py-4 bg-rose-500 text-white rounded-2xl font-black hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/20 uppercase tracking-widest text-md">
                            {{ __('Confirm Delete') }}
                        </button>
                        <button type="button" @click="showDeleteModal = false" 
                                class="w-full py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all uppercase tracking-widest text-md">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection