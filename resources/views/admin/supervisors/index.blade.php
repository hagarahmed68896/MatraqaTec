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
                        <button @click="showFilters = !showFilters" class="w-12 h-12 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 text-slate-400 hover:text-primary transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                        </button>

                        <!-- Filter Dropdown -->
                        <div x-show="showFilters" x-cloak @click.away="showFilters = false" x-transition class="absolute left-0 mt-3 w-80 bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-8">
                            <form action="{{ route('admin.supervisors.index') }}" method="GET" class="space-y-8">
                                <div class="space-y-4">
                                    <label class="text-sm font-black text-slate-900 dark:text-white">{{ __('Sort by') }}:</label>
                                    <div class="space-y-3">
                                        @foreach(['all' => 'All', 'name' => 'Name', 'newest' => 'Newest', 'oldest' => 'Oldest'] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-sm font-bold text-slate-500 group-hover:text-primary transition-colors">{{ __($label) }}</span>
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
                                            <span class="text-sm font-bold text-slate-500 group-hover:text-primary transition-colors">{{ __('All') }}</span>
                                            <input type="radio" name="role_id" value="all" {{ request('role_id', 'all') == 'all' ? 'checked' : '' }} class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @foreach($roles as $role)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-sm font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $role->name_ar ?? $role->name }}</span>
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
                                            <span class="text-sm font-bold text-slate-500 group-hover:text-primary transition-colors">{{ __($label) }}</span>
                                            <input type="radio" name="status" value="{{ $val }}" {{ request('status', 'all') == $val ? 'checked' : '' }} class="appearance-none w-5 h-5 border-2 border-slate-200 rounded-full checked:border-primary checked:border-[6px] transition-all cursor-pointer">
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex gap-3 pt-6">
                                    <button type="submit" class="flex-1 py-3 bg-[#1A1A31] text-white text-xs font-black rounded-xl hover:bg-black transition-all shadow-lg shadow-black/20 uppercase tracking-widest">{{ __('Apply') }}</button>
                                    <a href="{{ route('admin.supervisors.index') }}" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-500 text-xs font-black rounded-xl hover:bg-slate-200 text-center transition-all uppercase tracking-widest">{{ __('Reset') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <form action="{{ route('admin.supervisors.index') }}" method="GET" class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}" class="w-full pr-12 pl-4 py-3 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </form>
                </div>  
            <!-- Group 1: Add & Download (Right Side in RTL) -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('admin.supervisors.create') }}" class="flex items-center gap-2 px-6 py-3 bg-[#1A1A31] text-white text-md font-bold rounded-xl hover:bg-black transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('Add Supervisor') }}
                    </a>
                    <a href="{{ route('admin.supervisors.download') }}" class="flex items-center gap-2 px-6 py-3 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-md font-bold rounded-xl hover:bg-slate-50 transition shadow-sm bg-white dark:bg-white/5">
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
                    <span class="text-sm font-black uppercase tracking-widest">{{ __('Selected Items') }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <form action="{{ route('admin.supervisors.bulk-block') }}" method="POST" class="inline">
                        @csrf
                        <template x-for="id in selectedItems" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" class="px-6 py-3 bg-red-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-600 transition-all flex items-center gap-2 shadow-lg shadow-red-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                            {{ __('Block') }}
                        </button>
                    </form>

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

                    <button @click="selectedItems = []; selectAll = false" class="px-6 py-3 bg-white dark:bg-[#1A1A31] text-slate-500 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 dark:hover:bg-white/10 transition-all border border-slate-200 dark:border-white/10">
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
                    <tr @click="openDetails({{ json_encode($item->load('roles')) }})" class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all group cursor-pointer">
                        <td class="py-5 px-6 text-center" @click.stop>
                            <input type="checkbox" :value="{{ $item->id }}" @click="toggleItem('{{ $item->id }}')" :checked="selectedItems.includes('{{ $item->id }}')" class="row-checkbox w-5 h-5 border-2 border-slate-200 rounded-lg text-primary focus:ring-primary transition-all cursor-pointer">
                        </td>
                        <td class="py-5 px-6 text-[12px] opacity-50">#{{ $item->id }}</td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-white font-black overflow-hidden group-hover:ring-4 ring-primary/20 transition-all shadow-md">
                                    {{ mb_substr($item->name, 0, 1) }}
                                </div>
                                <span class="text-slate-900 dark:text-white font-black group-hover:text-primary transition-colors text-sm">{{ $item->name }}</span>
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
                            <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider
                                {{ $item->status == 'active' ? 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">
                                {{ $item->status == 'active' ? __('Active') : __('Blocked') }}
                            </span>
                        </td>
                        <td class="py-5 px-6 opacity-50 text-[12px]">{{ $item->created_at->format('j/n/2025') }}</td>
                        <td class="py-5 px-6 text-center" @click.stop>
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.supervisors.edit', $item->id) }}" title="{{ __('Edit') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-primary transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('admin.supervisors.toggle-block', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="{{ $item->status == 'active' ? __('Block') : __('Unblock') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-red-500 transition-all">
                                        @if($item->status == 'active')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @endif
                                    </button>
                                </form>
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

    <!-- Detail Modal (Matching Screenshot 2) -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-[999] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div x-show="showDetailModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" @click="showDetailModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        
        <!-- Modal Content -->
        <div x-show="showDetailModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative bg-white dark:bg-[#1A1A31] w-full max-w-lg rounded-[2.5rem] overflow-hidden shadow-2xl border border-slate-100 dark:border-white/10 flex flex-col max-h-[90vh]" dir="rtl">
            <!-- Modal Header (Dark) -->
            <div class="bg-[#1A1A31] p-8 h-32 shrink-0 relative">
                <button @click="showDetailModal = false" class="absolute top-6 left-6 text-white/50 hover:text-white transition-all">
                    <svg class="w-8 h-8 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- Profile Info Overlap (Scrollable Container) -->
            <div class="relative px-8 pt-16 pb-12 overflow-y-auto">
                <!-- Profile Image -->
                <div class="absolute -top-16 left-1/2 -translate-x-1/2">
                    <div class="w-32 h-32 rounded-full border-[6px] border-white dark:border-[#1A1A31] shadow-2xl overflow-hidden bg-primary shadow-primary/20 flex items-center justify-center">
                        <template x-if="selectedSupervisor && selectedSupervisor.image">
                            <img :src="'/storage/' + selectedSupervisor.image" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!(selectedSupervisor && selectedSupervisor.image)">
                            <span class="text-4xl text-white font-black" x-text="selectedSupervisor ? selectedSupervisor.name.substring(0,1) : ''"></span>
                        </template>
                    </div>
                </div>

                <!-- Attributes List -->
                <div class="space-y-6 mt-4">
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-white/5 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('الاسم:') }}</span>
                        </div>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400" x-text="selectedSupervisor ? selectedSupervisor.name : ''"></span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-white/5 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('نوع الحساب:') }}</span>
                        </div>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400" x-text="selectedSupervisor && selectedSupervisor.roles.length > 0 ? selectedSupervisor.roles[0].name_ar || selectedSupervisor.roles[0].name : '{{ __('Supervisor') }}'"></span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-white/5 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('رقم الجوال:') }}</span>
                        </div>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400 font-mono" x-text="selectedSupervisor ? selectedSupervisor.phone : ''" dir="ltr"></span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-white/5 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('البريد الإلكتروني:') }}</span>
                        </div>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400" x-text="selectedSupervisor ? selectedSupervisor.email : ''"></span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-white/5 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('الحالة:') }}</span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider" 
                              :class="selectedSupervisor && selectedSupervisor.status === 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
                              x-text="selectedSupervisor && selectedSupervisor.status === 'active' ? '{{ __('Active') }}' : '{{ __('Blocked') }}'"></span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-white/5 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('التاريخ:') }}</span>
                        </div>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400" x-text="selectedSupervisor ? new Date(selectedSupervisor.created_at).toLocaleDateString('en-GB') : ''"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
