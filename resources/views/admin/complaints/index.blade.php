@extends('layouts.admin')

@section('title', __('Customer Service and Complaints') . ' - ' . __('MatraqaTec'))

@section('content')
<div x-data="{ 
    deleteModal: false,
    confirmUrl: '',
    selectedItems: [],
    showFilters: false,
    search: '{{ request('search') }}',
    status: '{{ request('status', 'all') }}',
    type: '{{ request('type', 'all') }}',
    account_type: '{{ request('account_type', 'all') }}',
    sort_by: '{{ request('sort_by', 'all') }}',
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

    confirmBulkDelete() {
        if (this.selectedItems.length > 0) {
            this.confirmUrl = '{{ route('admin.complaints.bulk-destroy') }}';
            this.deleteModal = true;
        }
    }
}" class="space-y-8 min-h-screen pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-[28px] font-black text-[#1A1A31] dark:text-white">{{ __('Customer Service and Complaints') }}</h1>
        </div>
    </div>

    <!-- Main Container -->
    <div class="relative bg-white dark:bg-[#1A1A31] rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.02)] border border-slate-50 dark:border-white/5 overflow-visible">
        
        <div class="px-8 py-6">
            <div class="flex flex-row items-center justify-between gap-6">
              

                <!-- Right: Search and Filter Toggle -->
                <div class="flex items-center gap-4 flex-1 max-w-2xl ">
                  
                                    <!-- Circular Filter Toggle -->
                    <div class="relative">
                        <button @click="showFilters = !showFilters" 
                                :class="showFilters ? 'bg-primary text-white border-primary' : 'bg-[#FBFBFF] dark:bg-white/5 border-slate-100 dark:border-white/10 text-slate-400 dark:text-white'"
                                class="w-12 h-12 flex items-center justify-center border rounded-2xl hover:opacity-90 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        </button>
                    </div>
                      <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <input type="text" x-model="search" @keyup.enter="$refs.filterForm.submit()"
                               class="w-full h-12 border border-slate-100 dark:border-white/10 rounded-2xl px-6 pr-12 bg-[#FBFBFF] dark:bg-white/5 font-bold text-sm text-[#1A1A31] dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all shadow-inner"
                               >
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                </div>
                  <!-- Left: Download and Status Buttons -->
                <div class="flex items-center gap-4">
                    <!-- Status Dropdown (الكل) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="h-12 px-6 bg-[#1A1A31] text-white rounded-2xl flex items-center justify-between gap-4 font-bold text-sm hover:opacity-90 transition-all min-w-[120px]">
                            <span>{{ request('status', 'all') === 'all' ? __('All Status') : __(request('status')) }}</span>
                            <svg class="w-4 h-4 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             class="absolute left-0 mt-4 w-48 bg-white dark:bg-[#1A1A31] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/10 z-[110] p-2 overflow-hidden">
                            @foreach(['all' => __('All'),  'in_progress' => __('Under Review'), 'resolved' => __('Solved'), 'rejected' => __('Rejected')] as $val => $label)
                            <button @click="status = '{{ $val }}'; open = false; $nextTick(() => $refs.filterForm.submit())"
                                    class="w-full px-6 py-3 text-right hover:bg-[#F3F4FF] dark:hover:bg-white/5 transition-colors font-bold text-[13px] rounded-xl {{ request('status') == $val || ($val == 'all' && !request('status')) ? 'bg-[#F3F4FF] text-[#1A1A31]' : 'text-slate-500' }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Download Button -->
                    <a href="{{ route('admin.complaints.download') }}" 
                       class="h-12 px-6 bg-white dark:bg-transparent border border-[#1A1A31]/10 dark:border-white/10 rounded-2xl flex items-center gap-3 text-[#1A1A31] dark:text-white font-bold text-sm hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        {{ __('Download') }}
                    </a>
                </div>

                <form x-ref="filterForm" action="{{ route('admin.complaints.index') }}" method="GET" class="hidden">
                    <input type="hidden" name="status" x-model="status">
                    <input type="hidden" name="search" x-model="search">
                    <input type="hidden" name="per_page" x-model="per_page">
                    <input type="hidden" name="sort_by" x-model="sort_by">
                    <input type="hidden" name="account_type" x-model="account_type">
                    <input type="hidden" name="type" x-model="type">
                </form>
            </div>
        </div>

        <!-- Filter Panel Drawer -->
        <div x-show="showFilters"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-8"
             @click.away="showFilters = false"
             class="absolute right-4 top-[90px] z-[200] w-72 bg-white dark:bg-[#1A1A31] rounded-3xl shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden"
             x-cloak
             style="right: auto;">
            <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">

                <!-- Sort By -->
                <div>
                    <p class="text-sm font-black text-[#1A1A31] dark:text-white mb-3">{{ __('Sort By') }}:</p>
                    <div class="space-y-2">
                        @foreach(['all' => __('All'), 'name' => __('Name'), 'newest' => __('Newest'), 'oldest' => __('Oldest')] as $val => $label)
                        <label class="flex items-center gap-3 cursor-pointer group py-1">
                            <div class="relative w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                 :class="sort_by === '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/20'">
                                <div class="w-2 h-2 rounded-full bg-white" x-show="sort_by === '{{ $val }}'"></div>
                            </div>
                            <input type="radio" name="sort_by" value="{{ $val }}" x-model="sort_by" class="hidden">
                            <span :class="sort_by === '{{ $val }}' ? 'text-[#1A1A31] dark:text-white font-black' : 'text-slate-400 font-bold'" class="text-sm transition-all">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-white/5">

                <!-- Account Type -->
                <div>
                    <p class="text-sm font-black text-[#1A1A31] dark:text-white mb-3">{{ __('Account Type') }}:</p>
                    <div class="space-y-2">
                        @foreach(['all' => __('All'), 'individual' => __('Customer'), 'company' => __('Company'), 'technician' => __('Technician')] as $val => $label)
                        <label class="flex items-center gap-3 cursor-pointer group py-1">
                            <div class="relative w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                 :class="account_type === '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/20'">
                                <div class="w-2 h-2 rounded-full bg-white" x-show="account_type === '{{ $val }}'"></div>
                            </div>
                            <input type="radio" name="account_type" value="{{ $val }}" x-model="account_type" class="hidden">
                            <span :class="account_type === '{{ $val }}' ? 'text-[#1A1A31] dark:text-white font-black' : 'text-slate-400 font-bold'" class="text-sm transition-all">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-white/5">

                <!-- Ticket Type -->
                <div>
                    <p class="text-sm font-black text-[#1A1A31] dark:text-white mb-3">{{ __('Ticket Type') }}:</p>
                    <div class="space-y-2">
                        @foreach(['all' => __('All'), 'general' => __('General inquiry'), 'technical' => __('Complaint against a technician'), 'payment' => __('Payment issue'), 'suggestion' => __('Suggestion / Note')] as $val => $label)
                        <label class="flex items-center gap-3 cursor-pointer group py-1">
                            <div class="relative w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                 :class="type === '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/20'">
                                <div class="w-2 h-2 rounded-full bg-white" x-show="type === '{{ $val }}'"></div>
                            </div>
                            <input type="radio" name="type" value="{{ $val }}" x-model="type" class="hidden">
                            <span :class="type === '{{ $val }}' ? 'text-[#1A1A31] dark:text-white font-black' : 'text-slate-400 font-bold'" class="text-sm transition-all">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="p-4 border-t border-slate-50 dark:border-white/5 flex items-center gap-3">
                <button @click="sort_by = 'all'; account_type = 'all'; type = 'all'; status = 'all'; $nextTick(() => $refs.filterForm.submit())"
                        class="flex-1 h-11 rounded-2xl border border-slate-100 dark:border-white/10 text-slate-400 font-bold text-sm hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                    {{ __('Reset') }}
                </button>
                <button @click="showFilters = false; $nextTick(() => $refs.filterForm.submit())"
                        class="flex-1 h-11 rounded-2xl bg-primary text-white font-black text-sm hover:opacity-90 transition-all">
                    {{ __('Apply') }}
                </button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-slate-50 dark:bg-[#0f0f24]">
                    <tr class="text-slate-400 dark:text-slate-500 text-[11px] font-black uppercase tracking-wider border-b border-slate-100 dark:border-white/5">
                        <th class="py-6 px-8 flex items-center gap-4">
                            <div class="relative w-5 h-5 border-2 rounded-md transition-all flex items-center justify-center cursor-pointer"
                                 :class="selectedItems.length === {{ count($items) }} ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                <input type="checkbox" @click="toggleSelectAll()" :checked="selectedItems.length === {{ count($items) }}" class="hidden">
                                <template x-if="selectedItems.length === {{ count($items) }}">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                            </div>
                            <span>#</span>
                        </th>
                        <th class="py-6 px-4">{{ __('Ticket Number') }}</th>
                        <th class="py-6 px-4">{{ __('User Name') }}</th>
                        <th class="py-6 px-4">{{ __('Account Type') }}</th>
                        <th class="py-6 px-4">{{ __('Phone Number') }}</th>
                        <th class="py-6 px-4">{{ __('Ticket Type') }}</th>
                        <th class="py-6 px-4 text-center">{{ __('Order') }}</th>
                        <th class="py-6 px-4 text-center">{{ __('Issue Image') }}</th>
                        <th class="py-6 px-4 text-center">{{ __('Issue Description') }}</th>
                        <th class="py-6 px-4 text-center">{{ __('Status') }}</th>
                        <th class="py-6 px-8">{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50 dark:divide-white/[0.04]">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all group cursor-pointer"
                        onclick="window.location.href='{{ route('admin.complaints.show', $item->id) }}'"
                        :class="selectedItems.includes({{ $item->id }}) ? 'bg-primary/5' : ''">
                        <td class="py-6 px-8 flex items-center gap-4" @click.stop>
                            <div class="relative w-5 h-5 border-2 rounded-md transition-all flex items-center justify-center cursor-pointer"
                                 @click="toggleItem({{ $item->id }})"
                                 :class="selectedItems.includes({{ $item->id }}) ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                <input type="checkbox" value="{{ $item->id }}" class="hidden" :checked="selectedItems.includes({{ $item->id }})">
                                <template x-if="selectedItems.includes({{ $item->id }})">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                            </div>
                            <span class="text-xs font-bold text-slate-400">#{{ $loop->iteration }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-black text-primary dark:text-gray-300">
                                #{{ $item->ticket_number }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ $item->user->name ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ __($item->account_type) }}</span>
                        </td>
                        <td class="py-6 px-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $item->phone }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $item->type == 'technical' ? __('Complaint against a technician') : __('General inquiry') }}</span>
                        </td>
                        <td class="py-5 px-4 text-center" @click.stop>
                            @if($item->order_id && $item->order)
                            <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-primary hover:scale-110 transition-transform inline-block group/btn">
                                <span class=" text-primary dark:text-white px-3 py-1 rounded-lg text-xs font-black group-hover/btn:bg-primary group-hover/btn:text-white transition-all">#{{ $item->order->order_number }}</span>
                            </a>
                            @else
                            <span class="text-slate-200">-</span>
                            @endif
                        </td>
                        <td class="py-6 px-4 text-center" @click.stop>
                            @if($item->attachment)
                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 mx-auto border-2 border-white shadow-sm ring-1 ring-slate-100 group-hover:scale-110 transition-all cursor-zoom-in">
                                <img src="{{ asset('storage/' . $item->attachment) }}" class="w-full h-full object-cover">
                            </div>
                            @else
                            <span class="text-slate-200">-</span>
                            @endif
                        </td>
                        <td class="py-6 px-4 max-w-xs">
                            <p class="text-xs font-bold text-slate-400 leading-relaxed line-clamp-2">{{ $item->description }}</p>
                        </td>
                        <td class="py-6 px-4 text-center">
                            @php
                                $statusStyles = [
                                    'pending' => ['bg' => 'bg-amber-50 text-amber-500', 'label' => __('Pending')],
                                    'in_progress' => ['bg' => 'bg-slate-100 text-slate-500', 'label' => __('Under Review')],
                                    'resolved' => ['bg' => 'bg-emerald-50 text-emerald-500', 'label' => __('Solved')],
                                    'rejected' => ['bg' => 'bg-rose-50 text-rose-500', 'label' => __('Rejected')],
                                ];
                                $s = $statusStyles[$item->status] ?? $statusStyles['pending'];
                            @endphp
                            <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase {{ $s['bg'] }}">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td class="py-6 px-8">
                            <span class="text-sm font-bold text-slate-400 font-mono">{{ $item->created_at->format('j/n/Y') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-24 text-center">
                            <div class="flex flex-col items-center gap-3 opacity-20">
                                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <span class="text-sm font-black uppercase tracking-widest text-[#1A1A31]">{{ __('No complaints found') }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
        <div class="px-8 py-5 bg-slate-50 dark:bg-[#0f0f24] border-t border-slate-100 dark:border-white/5">
            <div class="flex flex-row items-center justify-between">
                <!-- Right: Total Info (RTL) -->
                <div class="text-sm font-bold text-slate-400">
                    {{ $items->firstItem() }} {{ __('of') }} {{ $items->total() }}
                </div>

                <!-- Middle: Rows per page -->
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-slate-400">{{ __('Rows per page:') }}</span>
                    <div class="relative group">
                        <select @change="per_page = $event.target.value; $nextTick(() => $refs.filterForm.submit())" 
                                class="h-10 pl-8 pr-4 appearance-none rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-black text-[#1A1A31] dark:text-white outline-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                            @foreach([10, 25, 50, 100] as $v)
                            <option value="{{ $v }}" {{ $items->perPage() == $v ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <!-- Left: Pagination controls (RTL) -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl p-1">
                        <!-- Next Page (Shown as > for LTR, but in RTL layout it's forward) -->
                        <a href="{{ $items->nextPageUrl() ?? '#' }}" 
                           class="w-8 h-8 rounded-lg flex items-center justify-center transition-all {{ $items->hasMorePages() ? 'bg-[#1A1A31] text-white hover:opacity-90' : 'bg-white text-slate-200 cursor-not-allowed' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                        
                        <div class="px-4 py-1 text-sm font-black text-[#1A1A31] dark:text-white bg-white dark:bg-white/10 rounded-lg shadow-sm mx-1">
                            {{ $items->currentPage() }} / {{ $items->lastPage() }}
                        </div>

                        <!-- Previous Page -->
                        <a href="{{ $items->previousPageUrl() ?? '#' }}" 
                           class="w-8 h-8 rounded-lg flex items-center justify-center transition-all {{ !$items->onFirstPage() ? 'bg-[#1A1A31] text-white hover:opacity-90' : 'bg-white text-slate-200 cursor-not-allowed' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Custom Delete Modal -->
<template x-teleport="body">
    <div x-show="deleteModal" 
         class="fixed inset-0 z-[150] flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto" 
         x-cloak>
        
        <div x-show="deleteModal" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             @click="deleteModal = false" 
             class="fixed inset-0 bg-[#1A1A31]/60 backdrop-blur-sm transition-opacity"></div>

        <div x-show="deleteModal"
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
                    <svg class="w-10 h-10 text-rose-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>

                <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-4">{{ __('Confirm Deletion') }}</h3>
                <p class="text-slate-500 dark:text-slate-400 font-bold leading-relaxed mb-10">
                    {{ __('Are you sure you want to delete these items?') }}
                </p>

                <div class="flex gap-4">
                    <button @click="deleteModal = false" 
                            class="flex-1 py-4 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 rounded-2xl text-sm font-black hover:bg-slate-200 transition-all">
                        {{ __('Cancel') }}
                    </button>
                    <form :action="confirmUrl" method="POST" class="flex-1">
                        @csrf
                        <template x-for="id in selectedItems" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
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
@endsection