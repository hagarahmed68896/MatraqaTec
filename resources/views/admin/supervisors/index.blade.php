@extends('layouts.admin')

@section('title', __('Supervisors Management'))
@section('page_title', __('Supervisors Management'))

@section('content')
<div x-data="{ 
    selectedItems: [],
    selectAll: false,
    showFilters: false,
    showDetailModal: false,
    selectedSupervisor: null,
    showDeleteModal: false,
    deleteTargetId: null,
    isBulkDelete: false,
    confirmDelete(id = null) {
        this.deleteTargetId = id;
        this.isBulkDelete = id === null;
        this.showDeleteModal = true;
    },
    executeDelete() {
        if (this.isBulkDelete) {
            document.getElementById('bulk-delete-form').submit();
        } else {
            document.getElementById('delete-form-' + this.deleteTargetId).submit();
        }
    },
    showBlockModal: false,
    blockTargetId: null,
    isBulkBlock: false,
    confirmBlock(id = null) {
        this.blockTargetId = id;
        this.isBulkBlock = id === null;
        this.showBlockModal = true;
    },
    executeBlock() {
        if (this.isBulkBlock) {
            document.getElementById('bulk-block-form').submit();
        } else {
            document.getElementById('block-form-' + this.blockTargetId).submit();
        }
    },
    
    // Sort & Filter state
    sortBy: '{{ request('sort', 'all') }}',
    permission: '{{ request('role_id', 'all') }}',
    status: '{{ request('status', 'all') }}',

    toggleAll() {
        this.selectAll = !this.selectAll;
        if (this.selectAll) {
            this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(el => el.value);
        } else {
            this.selectedItems = [];
        }
    },
    toggleItem(id) {
        id = id.toString();
        if (this.selectedItems.includes(id)) {
            this.selectedItems = this.selectedItems.filter(item => item !== id);
        } else {
            this.selectedItems.push(id);
        }
        this.selectAll = this.selectedItems.length === document.querySelectorAll('.row-checkbox').length;
    },
    openDetails(supervisor) {
        this.selectedSupervisor = supervisor;
        this.showDetailModal = true;
    }
}" class="space-y-6 pb-20">

    <!-- Unified Container (Matching Screenshot 1) -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-visible text-right" dir="rtl">
        
        <!-- Controls Row -->
        <div class="p-6">
            <!-- Default Controls (Show when no items selected) -->
            <div x-show="selectedItems.length === 0" x-transition class="flex flex-col md:flex-row items-center justify-between gap-6 w-full">
              <!-- Group 2: Filter & Search (Left Side in RTL) -->
                <div class="flex items-center gap-3 flex-1 md:max-w-2xl justify-end">
                    <!-- Filter Button -->
                    <div class="relative">
                        <button @click="showFilters = !showFilters" class="w-12 h-12 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 text-slate-400 hover:text-primary dark:hover:text-white transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                        </button>

                        <!-- Filter Dropdown -->
                        <div x-show="showFilters" x-cloak @click.away="showFilters = false" 
                        x-transition class="absolute right-0 mt-3 w-80 bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 
                        dark:border-white/10 z-[100] p-8">
                            <form action="{{ route('admin.supervisors.index') }}" method="GET" class="space-y-8">
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-slate-900 dark:text-white">{{ __('Sort by') }}:</label>
                                    <div class="space-y-3">
                                        @foreach(['all' => 'All', 'name' => 'Name', 'newest' => 'Newest', 'oldest' => 'Oldest'] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ __($label) }}</span>
                                            <div class="relative flex items-center">
                                                <input type="radio" name="sort" value="{{ $val }}" {{ request('sort', 'all') == $val ? 'checked' : '' }} class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="h-px bg-slate-100 dark:bg-white/5"></div>
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-slate-900 dark:text-white">{{ __('Permission') }}:</label>
                                    <div class="space-y-3">
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ __('All') }}</span>
                                            <input type="radio" name="role_id" value="all" {{ request('role_id', 'all') == 'all' ? 'checked' : '' }} class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @foreach($roles as $role)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ $role->name_ar ?? $role->name }}</span>
                                            <input type="radio" name="role_id" value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'checked' : '' }} class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="h-px bg-slate-100 dark:bg-white/5"></div>
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-slate-900 dark:text-white">{{ __('Status') }}:</label>
                                    <div class="space-y-3">
                                        @foreach(['all' => 'All', 'active' => 'Active', 'blocked' => 'Blocked'] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-sm font-bold text-slate-500 dark:text-white transition-colors">{{ __($label) }}</span>
                                            <input type="radio" name="status" value="{{ $val }}" {{ request('status', 'all') == $val ? 'checked' : '' }} class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex gap-3 pt-6">
                                    <button type="submit" class="flex-1 py-3 bg-[#1A1A31] text-white text-xs font-black dark:bg-white/5 rounded-xl hover:bg-slate-200 transition-all shadow-lg shadow-black/20 uppercase tracking-widest">{{ __('Apply') }}</button>
                                    <a href="{{ route('admin.supervisors.index') }}" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-white text-xs font-black rounded-xl hover:bg-slate-200 text-center transition-all uppercase tracking-widest">{{ __('Reset') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <form action="{{ route('admin.supervisors.index') }}" method="GET" class="relative md:w-80 flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}" class="w-full pr-12 pl-4 py-3 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </form>
                </div>  
            <!-- Group 1: Add & Download (Right Side in RTL) -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('admin.supervisors.create') }}"
                     class="flex items-center gap-2 px-6 py-3 bg-[#1A1A31]
                      text-white text-md font-bold rounded-xl dark:bg-white/5
                       transition shadow-sm dark:border dark:border-4 dark:border-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('Add Supervisor') }}
                    </a>
                    <a href="{{ route('admin.supervisors.download') }}" 
                    class="flex items-center gap-2 px-6 py-3
                     border border-slate-200 dark:border-white/10
                      text-slate-800 dark:text-white text-md font-bold rounded-xl  transition shadow-sm bg-white dark:bg-white/5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        {{ __('Download') }}
                    </a>
                </div>

              
            </div>

            <!-- Bulk Actions (Show when items are selected) -->
            <div x-show="selectedItems.length > 0" x-cloak x-transition class="flex items-center justify-between w-full bg-slate-50 dark:bg-white/5 p-2 rounded-2xl border border-primary/20">
                <div class="flex items-center gap-4 px-4 text-primary">
                    <div class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center font-black shadow-lg shadow-primary/20">
                        <span x-text="selectedItems.length"></span>
                    </div>
                    <span class="text-sm font-black dark:text-white uppercase tracking-widest">{{ __('Selected Items') }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <form id="bulk-delete-form" action="{{ route('admin.supervisors.bulk-destroy') }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selectedItems" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                    </form>
                    <button type="button" @click="confirmDelete()" class="px-6 py-3 bg-rose-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-rose-600 transition-all flex items-center gap-2 shadow-lg shadow-rose-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Delete Selected') }}
                    </button>

                    <form id="bulk-block-form" action="{{ route('admin.supervisors.bulk-block') }}" method="POST" class="hidden">
                        @csrf
                        <template x-for="id in selectedItems" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                    </form>
                    <button type="button" @click="confirmBlock()" class="px-6 py-3 bg-red-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-600 transition-all flex items-center gap-2 shadow-lg shadow-red-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        {{ __('Block') }}
                    </button>

                    <form action="{{ route('admin.supervisors.bulk-unblock') }}" method="POST" class="inline">
                        @csrf
                        <template x-for="id in selectedItems" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" class="px-6 py-3 bg-green-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-green-600 transition-all flex items-center gap-2 shadow-lg shadow-green-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ __('Unblock') }}
                        </button>
                    </form>

                    <button @click="selectedItems = []; selectAll = false" class="px-6 py-3 bg-white dark:bg-[#1A1A31] text-slate-500 dark:text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 dark:hover:bg-white/10 transition-all border border-slate-200 dark:border-white/10">
                        {{ __('Cancel Selection') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Body -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="py-5 px-6 text-center w-12">
                            <input type="checkbox" @click="toggleAll()" :checked="selectAll" class="w-5 h-5 border-2 border-slate-200 rounded-lg text-primary focus:ring-primary transition-all cursor-pointer">
                        </th>
                        <th class="py-5 px-6 font-black">#</th>
                        <th class="py-5 px-6 font-black">{{ __('Supervisor Name') }}</th>
                        <th class="py-5 px-6 font-black">{{ __('Phone') }}</th>
                        <th class="py-5 px-6 font-black">{{ __('Email') }}</th>
                        <th class="py-5 px-6 font-black">{{ __('Permission') }}</th>
                        <th class="py-5 px-6 font-black text-center">{{ __('Status') }}</th>
                        <th class="py-5 px-6 font-black">{{ __('Date') }}</th>
                        <th class="py-5 px-6 font-black text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-white/5 text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr @click="openDetails({{ json_encode($item->load('roles')) }})"
                     class="hover:bg-slate-50/50 dark:hover:bg-white/5  transition-all
                      group cursor-pointer">
                        <td class="py-5 px-6 text-center" @click.stop>
                            <input type="checkbox" :value="{{ $item->id }}" @click="toggleItem('{{ $item->id }}')" :checked="selectedItems.includes('{{ $item->id }}')" class="row-checkbox w-5 h-5 border-2 border-slate-200 rounded-lg text-primary focus:ring-primary transition-all cursor-pointer">
                        </td>
                        <td class="py-5 px-6 text-[12px] opacity-50">#{{ $item->id }}</td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-white font-black overflow-hidden group-hover:ring-4 ring-primary/20 transition-all shadow-md">
                                    {{ mb_substr($item->name, 0, 1) }}
                                </div>
                                <span class="text-slate-900 dark:text-white font-black group-hover:text-primary dark:hover:text-white transition-colors text-sm">{{ $item->name }}</span>
                            </div>
                        </td>
                        <td class="py-5 px-6 font-mono opacity-80 text-[12px]">{{ $item->phone }}</td>
                        <td class="py-5 px-6 opacity-70 text-[12px]">{{ $item->email }}</td>
                        <td class="py-5 px-6">
                            <span class="text-sm font-bold opacity-80">
                                {{ $item->roles->pluck('name_ar')->first() ?? $item->roles->pluck('name')->first() ?? __('Supervisor') }}
                            </span>
                        </td>
                        <td class="py-5 px-6 text-center">
                            <span class="px-3 py-1 rounded-full text-[11px]  uppercase tracking-wider
                                {{ $item->status == 'active' ? 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">
                                {{ $item->status == 'active' ? __('Active') : __('Blocked') }}
                            </span>
                        </td>
                        <td class="py-5 px-6 opacity-50 text-[12px]">{{ $item->created_at->format('j/n/2025') }}</td>
                        <td class="py-5 px-6 text-center" @click.stop>
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.supervisors.edit', $item->id) }}" title="{{ __('Edit') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-primary dark:hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form id="block-form-{{ $item->id }}" action="{{ route('admin.supervisors.toggle-block', $item->id) }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                                @if($item->status == 'active')
                                    <button type="button" @click="confirmBlock({{ $item->id }})" title="{{ __('Block') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-red-500 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    </button>
                                @else
                                    <button type="submit" form="block-form-{{ $item->id }}" title="{{ __('Unblock') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-green-500 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                @endif
                                <form id="delete-form-{{ $item->id }}" action="{{ route('admin.supervisors.destroy', $item->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button type="button" @click="confirmDelete({{ $item->id }})" title="{{ __('Delete') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-20 text-center text-slate-400 font-bold">{{ __('No supervisors found') }}</td>
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

    <!-- Detail Modal -->
    <div x-show="showDetailModal" 
         x-cloak 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto">
        
        <!-- Backdrop with blur -->
        <div x-show="showDetailModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             @click="showDetailModal = false" 
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-md"></div>
        
        <!-- Modal Content Container -->
        <div x-show="showDetailModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
             class="relative bg-white dark:bg-[#1A1A31] w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden border border-white/20 dark:border-white/5 flex flex-col" 
             dir="rtl">
            
            <!-- Dynamic Modal Header (Gradient) -->
            <div class="h-32 bg-gradient-to-br from-[#1A1A31] via-[#2A2A4D] to-primary shrink-0 relative p-6">
                <div class="flex justify-between items-start">
                    <button @click="showDetailModal = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20 transition-all border border-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="text-white/80">
                        <h3 class="text-xl font-black">{{ __('Supervisor Profile') }}</h3>
                        <p class="text-[10px] opacity-60 uppercase tracking-widest">{{ __('Administrative Access') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Profile Content Area -->
            <div class="flex-1 relative px-8 pb-10">
                
                <!-- Floating Avatar Overlap -->
                <div class="absolute -top-16 left-8">
                    <div class="relative">
                        <div class="w-32 h-32 rounded-3xl border-[6px] border-white dark:border-[#1A1A31] shadow-2xl overflow-hidden bg-gradient-to-br from-primary to-primary-light flex items-center justify-center ring-1 ring-black/5">
                            <template x-if="selectedSupervisor && selectedSupervisor.avatar_url">
                                <img :src="selectedSupervisor.avatar_url" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!(selectedSupervisor && selectedSupervisor.avatar_url)">
                                <span class="text-5xl text-white font-black drop-shadow-lg" x-text="selectedSupervisor ? selectedSupervisor.name.substring(0,1) : ''"></span>
                            </template>
                        </div>
                        <!-- Status Indicator on Avatar -->
                        <div class="absolute bottom-0 -right-2 w-10 h-10 rounded-2xl border-4 border-white dark:border-[#1A1A31] shadow-lg flex items-center justify-center"
                             :class="selectedSupervisor && selectedSupervisor.status === 'active' ? 'bg-green-500' : 'bg-red-500'">
                            <template x-if="selectedSupervisor && selectedSupervisor.status === 'active'">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </template>
                            <template x-if="selectedSupervisor && selectedSupervisor.status !== 'active'">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Name & Title section -->
                <div class="pt-20 mb-8">
                    <h4 class="text-2xl font-black text-slate-900 dark:text-white mb-1" x-text="selectedSupervisor ? selectedSupervisor.name : ''"></h4>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-primary/10 text-primary text-xs font-black uppercase tracking-widest" 
                              x-text="selectedSupervisor && selectedSupervisor.roles.length > 0 ? selectedSupervisor.roles[0].name_ar || selectedSupervisor.roles[0].name : '{{ __('Supervisor') }}'"></span>
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-200 dark:bg-white/10"></div>
                        <span class="text-[11px] text-slate-400 font-bold" x-text="'ID: #' + (selectedSupervisor ? selectedSupervisor.id : '')"></span>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 gap-4 overflow-y-auto max-h-[300px] pr-2 custom-scrollbar">
                    
                    <!-- Phone -->
                    <div class="group flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-transparent hover:border-primary/20 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white dark:bg-white/10 shadow-sm flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-0.5">{{ __('Phone Number') }}</p>
                                <p class="text-sm font-black text-slate-800 dark:text-white font-mono" x-text="selectedSupervisor ? selectedSupervisor.phone : ''" dir="ltr"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="group flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-transparent hover:border-primary/20 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white dark:bg-white/10 shadow-sm flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-0.5">{{ __('Email Address') }}</p>
                                <p class="text-sm font-black text-slate-800 dark:text-white" x-text="selectedSupervisor ? selectedSupervisor.email : ''"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Joined Date -->
                    <div class="group flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-transparent hover:border-primary/20 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white dark:bg-white/10 shadow-sm flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-0.5">{{ __('Joined Date') }}</p>
                                <p class="text-sm font-black text-slate-800 dark:text-white" x-text="selectedSupervisor ? new Date(selectedSupervisor.created_at).toLocaleDateString('en-GB') : ''"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="group flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-transparent hover:border-primary/20 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white dark:bg-white/10 shadow-sm flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-0.5">{{ __('Current Status') }}</p>
                                <span class="px-3 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest" 
                                      :class="selectedSupervisor && selectedSupervisor.status === 'active' ? 'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400'"
                                      x-text="selectedSupervisor && selectedSupervisor.status === 'active' ? '{{ __('Active') }}' : '{{ __('Blocked') }}'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Action Buttons) -->
            <div class="px-8 py-6 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-white/5 flex gap-3">
                <a :href="'{{ route('admin.supervisors.edit', ':id') }}'.replace(':id', selectedSupervisor ? selectedSupervisor.id : '')" 
                   class="flex-1 py-3.5 bg-primary text-white text-[11px] font-black uppercase tracking-widest rounded-2xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/25 text-center">
                    {{ __('Edit Supervisor') }}
                </a>
                <button @click="showDetailModal = false" class="flex-1 py-3.5 bg-white dark:bg-white/5 text-slate-500 dark:text-white text-[11px] font-black uppercase tracking-widest rounded-2xl border border-slate-200 dark:border-white/10 hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                    {{ __('Close Details') }}
                </button>
            </div>
        </div>
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
                
                <div class="p-10 text-center text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="mx-auto w-24 h-24 bg-rose-50 dark:bg-rose-500/10 rounded-full flex items-center justify-center mb-8 relative">
                        <div class="absolute inset-0 rounded-full bg-rose-500/10 animate-ping"></div>
                        <svg class="w-10 h-10 text-rose-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-4 text-center">{{ __('Confirm Deletion') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold leading-relaxed mb-10 text-center">
                        <template x-if="isBulkDelete">
                            <span>{{ __('Are you sure you want to delete the selected supervisors?') }}</span>
                        </template>
                        <template x-if="!isBulkDelete">
                            <span>{{ __('Are you sure you want to delete this supervisor?') }}</span>
                        </template>
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
                
                <div class="p-10 text-center text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="mx-auto w-24 h-24 bg-amber-50 dark:bg-amber-500/10 rounded-full flex items-center justify-center mb-8 relative">
                        <div class="absolute inset-0 rounded-full bg-amber-500/10 animate-ping"></div>
                        <svg class="w-10 h-10 text-amber-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-4 text-center">{{ __('Confirm Blocking') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold leading-relaxed mb-10 text-center">
                        <template x-if="isBulkBlock">
                            <span>{{ __('Are you sure you want to block the selected supervisors?') }}</span>
                        </template>
                        <template x-if="!isBulkBlock">
                            <span>{{ __('Are you sure you want to block this supervisor?') }}</span>
                        </template>
                    </p>
                    
                    <div class="flex gap-4">
                        <button type="button" @click="showBlockModal = false" 
                                class="flex-1 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all uppercase tracking-widest text-md">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="executeBlock()" 
                                class="flex-1 py-4 bg-amber-500 text-white rounded-2xl font-black hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 uppercase tracking-widest text-md">
                            {{ __('Block') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
