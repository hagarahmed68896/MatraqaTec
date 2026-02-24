@extends('layouts.admin')

@section('title', __('Invoices Management'))
@section('page_title', __('Invoices Management'))

@section('content')
<div x-data="invoiceManagement()" class="space-y-8 pb-20" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Invoices') }}</h1>
    </div>

    <!-- SEARCH & FILTERS CONTAINER -->
    <div class="relative">
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-50 dark:border-white/5 shadow-sm p-4">
            <!-- Normal State: Search and Filter Toggles -->
            <div x-show="selectedRows.length === 0" 
                 class="flex items-center justify-between transition-all duration-300 {{ app()->getLocale() == 'ar' ? 'flex-row' : 'flex-row-reverse' }}">
                
                <!-- Left Side (LTR) / Right Side (RTL): Search and Filter -->
                <div class="flex items-center gap-3 flex-1 max-w-2xl px-2">
                   <div class="relative">
                       <button @click="showFilters = !showFilters" 
                                class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 dark:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            <div x-show="showFilters" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></div>
                        </button>

                        <!-- Filter Dropdown Panel -->
                        <div x-show="showFilters" @click.away="showFilters = false" x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute top-full {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} mt-2 w-80 bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-6 space-y-6 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            
                            <form action="{{ route('admin.invoices.index') }}" method="GET" class="space-y-6">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                
                                <!-- Sort By -->
                                <div class="space-y-3">
                                    <h4 class="text-xs font-black text-[#1A1A31] dark:text-white opacity-60 uppercase tracking-widest">{{ __('Sort by:') }}</h4>
                                    <div class="space-y-2">
                                        @foreach(['' => __('All'), 'name' => __('Name'), 'newest' => __('Newest'), 'oldest' => __('Oldest')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                             <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                                  :class="sortBy == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                                 <input type="radio" name="sort_by" value="{{ $val }}" x-model="sortBy" class="hidden">
                                                 <template x-if="sortBy == '{{ $val }}'">
                                                     <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                 </template>
                                             </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                                <!-- Client Type -->
                                <div class="space-y-3">
                                    <h4 class="text-xs font-black text-[#1A1A31] dark:text-white opacity-60 uppercase tracking-widest">{{ __('Client Type:') }}</h4>
                                    <div class="space-y-2">
                                        @foreach(['' => __('All'), 'individual' => __('Individual'), 'company' => __('Corporate')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                             <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                                  :class="clientType == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                                 <input type="radio" name="client_type" value="{{ $val }}" x-model="clientType" class="hidden">
                                                 <template x-if="clientType == '{{ $val }}'">
                                                     <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                 </template>
                                             </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                                <!-- Operation Type -->
                                <div class="space-y-3">
                                    <h4 class="text-xs font-black text-[#1A1A31] dark:text-white opacity-60 uppercase tracking-widest">{{ __('Operation Type:') }}</h4>
                                    <div class="space-y-2">
                                        @foreach(['' => __('All'), 'service' => __('Service'), 'spare_parts' => __('Spare Parts')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                             <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                                  :class="operationType == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                                 <input type="radio" name="operation_type" value="{{ $val }}" x-model="operationType" class="hidden">
                                                 <template x-if="operationType == '{{ $val }}'">
                                                     <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                 </template>
                                             </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                                <!-- Status -->
                                <div class="space-y-3">
                                    <h4 class="text-xs font-black text-[#1A1A31] dark:text-white opacity-60 uppercase tracking-widest">{{ __('Status:') }}</h4>
                                    <div class="space-y-2">
                                        @foreach(['' => __('All'), 'sent' => __('Sent'), 'not_sent' => __('Not Sent')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                             <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                                  :class="status == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                                 <input type="radio" name="status" value="{{ $val }}" x-model="status" class="hidden">
                                                 <template x-if="status == '{{ $val }}'">
                                                     <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                 </template>
                                             </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex gap-2 pt-4 border-t border-slate-50 dark:border-white/5">
                                    <button type="submit" class="flex-1 py-3 bg-[#1A1A31] dark:bg-primary text-white rounded-xl font-black text-xs shadow-lg hover:scale-[1.02] transition-all">
                                        {{ __('Apply') }}
                                    </button>
                                    <button type="button" @click="resetFilters()" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-400 rounded-xl font-bold text-xs text-center hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                                        {{ __('Reset') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <div class="flex-1 relative group">
                        <form action="{{ route('admin.invoices.index') }}" method="GET" id="searchForm">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="w-full h-11 border border-slate-200 dark:border-white/10 rounded-xl px-6 {{ app()->getLocale() == 'ar' ? 'pr-10 pl-4 text-right' : 'pl-10 pr-4 text-left' }} bg-white dark:bg-white/5 font-bold text-sm text-[#1A1A31] dark:text-white focus:outline-none focus:ring-1 focus:ring-primary/20 transition-all shadow-sm"
                                   placeholder="{{ __('Search...') }}">
                            <div class="absolute {{ app()->getLocale() == 'ar' ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Side (LTR) / Left Side (RTL): Actions -->
                <div class="flex items-center gap-3">
                    <!-- Global Download Button -->
                    <a href="{{ route('admin.invoices.download', request()->query()) }}" 
                       class="h-11 px-6 flex items-center gap-2 border border-slate-200 dark:border-white/10 text-[#1A1A31] dark:text-white rounded-xl font-bold text-sm hover:bg-slate-50 dark:hover:bg-white/10 transition-all shadow-sm">
                        <span>{{ __('Download') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Selection State: Bulk Actions -->
            <div x-show="selectedRows.length > 0" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex items-center justify-between bg-primary/5 dark:bg-primary/10 p-2 rounded-xl border border-primary/20 transition-all duration-300 h-11">
                <div class="flex items-center gap-4 px-4">
                    <span class="text-sm font-black text-primary">{{ __('Selected:') }} <span x-text="selectedRows.length"></span></span>
                    <button @click="selectedRows = []; selectAll = false" class="text-xs font-bold text-slate-400 hover:text-red-500 transition-colors">{{ __('Cancel') }}</button>
                </div>
                
                <button @click="downloadSelected()"
                        class="h-8 px-6 flex items-center gap-2 bg-primary text-white rounded-lg font-black text-xs shadow-lg hover:scale-[1.05] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>{{ __('Download Selected') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-50 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-50 dark:border-white/5">
                        <th class="py-6 px-8 flex items-center gap-4">
                            <div class="relative w-5 h-5 border-2 rounded-md transition-all flex items-center justify-center cursor-pointer"
                                 :class="selectAll ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="hidden">
                                <template x-if="selectAll">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                            </div>
                            <span>{{ __('Index') }}</span>
                        </th>
                        <th class="py-6 px-4">{{ __('Transaction Number') }}</th>
                        <th class="py-6 px-4">{{ __('Customer Name') }}</th>
                        <th class="py-6 px-4">{{ __('Client Type') }}</th>
                        <th class="py-6 px-4">{{ __('Operation') }}</th>
                        <th class="py-6 px-4">{{ __('Amount') }}</th>
                        <th class="py-6 px-4">{{ __('Status') }}</th>
                        <th class="py-6 px-4">{{ __('Date') }}</th>
                        <th class="py-6 px-8">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                    @forelse($items as $index => $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all group cursor-pointer" 
                        @click="if($event.target.tagName !== 'INPUT' && !$event.target.closest('button') && !$event.target.closest('a')) window.location.href='{{ route('admin.invoices.show', $item->id) }}'"
                        :class="selectedRows.includes('{{ $item->id }}') ? 'bg-primary/5' : ''">
                        <td class="py-6 px-8 flex items-center gap-4">
                            <div class="relative w-5 h-5 border-2 rounded-md transition-all flex items-center justify-center cursor-pointer"
                                 @click.stop="if(selectedRows.includes('{{ $item->id }}')) { selectedRows = selectedRows.filter(id => id !== '{{ $item->id }}'); selectAll = false; } else { selectedRows.push('{{ $item->id }}'); }"
                                 :class="selectedRows.includes('{{ $item->id }}') ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                <input type="checkbox" value="{{ $item->id }}" class="hidden row-checkbox" :checked="selectedRows.includes('{{ $item->id }}')">
                                <template x-if="selectedRows.includes('{{ $item->id }}')">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                            </div>
                            <span class="text-sm font-bold text-slate-500">{{ $items->firstItem() + $index }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-primary font-black text-sm">{{ __('Operation') }} - #{{ $item->order->order_number ?? $item->order_id }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ $item->order->user->name ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                {{ __($item->order->user->type ?? '') }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                {{ $item->order->service_id ? __('Service') : __('Spare Parts') }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-2">
                                <span class="text-md font-black text-[#1A1A31] dark:text-white">{{ number_format($item->amount, 0) }}</span>
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-4 h-4 opacity-40">
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase
                                {{ $item->status == 'sent' ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500' }}">
                                {{ $item->status == 'sent' ? __('Sent') : __('Not Sent') }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-slate-400 font-mono">{{ $item->issue_date ? $item->issue_date->format('j/n/Y') : '-' }}</span>
                        </td>
                        <td class="py-6 px-8">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.invoices.show', $item->id) }}" class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-primary rounded-lg transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-20 text-center text-slate-400 font-bold uppercase tracking-widest">{{ __('No invoices found currently') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Custom Pagination Footer -->
        <div class="p-8 border-t border-slate-50 dark:border-white/5 flex flex-col md:flex-row items-center justify-between gap-6 bg-slate-50/30 dark:bg-white/2">
            <div class="flex items-center gap-6">
                <!-- Rows per page -->
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400">{{ __('Rows per page:') }}</span>
                    <select name="limit" form="searchForm" onchange="this.form.submit()"
                            class="bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-lg px-2 py-1 text-xs font-black text-[#1A1A31] dark:text-white focus:outline-none">
                        <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('limit') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                
                <!-- Page Info -->
                <span class="text-xs font-bold text-slate-400">
                    {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} {{ __('of') }} {{ $items->total() }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                @if($items->onFirstPage())
                    <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ app()->getLocale() == 'ar' ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7' }}"></path></svg>
                    </span>
                @else
                    <a href="{{ $items->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 text-[#1A1A31] dark:text-white hover:bg-slate-50 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ app()->getLocale() == 'ar' ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7' }}"></path></svg>
                    </a>
                @endif

                <div class="flex items-center gap-1 mx-2">
                    <span class="text-xs font-black text-[#1A1A31] dark:text-white bg-slate-100 dark:bg-white/10 w-8 h-8 flex items-center justify-center rounded-lg">{{ $items->currentPage() }}</span>
                    <span class="text-xs font-bold text-slate-400 mx-1">/</span>
                    <span class="text-xs font-bold text-slate-400">{{ $items->lastPage() }}</span>
                </div>

                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 text-[#1A1A31] dark:text-white hover:bg-slate-50 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ app()->getLocale() == 'ar' ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}"></path></svg>
                    </a>
                @else
                    <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ app()->getLocale() == 'ar' ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}"></path></svg>
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('invoiceManagement', () => ({
            showFilters: false,
            sortBy: '{{ request('sort_by', '') }}',
            clientType: '{{ request('client_type', '') }}',
            operationType: '{{ request('operation_type', '') }}',
            status: '{{ request('status', '') }}',
            selectedRows: [],
            selectAll: false,

            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedRows = Array.from(document.querySelectorAll('.row-checkbox')).map(el => el.value);
                } else {
                    this.selectedRows = [];
                }
            },

            downloadSelected() {
                if (this.selectedRows.length === 0) return;
                const url = new URL("{{ route('admin.invoices.download') }}");
                url.searchParams.set('ids', this.selectedRows.join(','));
                window.location.href = url.href;
            },

            resetFilters() {
                this.sortBy = '';
                this.clientType = '';
                this.operationType = '';
                this.status = '';
                window.location.href = "{{ route('admin.invoices.index') }}";
            }
        }));
    });
</script>
@endsection