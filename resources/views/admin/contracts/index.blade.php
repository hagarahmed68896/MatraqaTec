@extends('layouts.admin')

@section('title', __('Contracts Management'))
@section('page_title', __('Contracts Management'))

@section('content')
<div x-data="contractManagement()" class="space-y-8 pb-20" dir="rtl">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-[#1A1A31]">{{ __('Contracts Management') }}</h1>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $cardStats = [
                ['label' => 'Total Contracts', 'value' => $stats['total_contracts'], 'color' => '#10B981', 'trend' => '0.43%', 'icon' => 'contract', 'prefix' => ''],
                ['label' => 'Total Companies', 'value' => $stats['total_companies'], 'color' => '#10B981', 'trend' => '8.43%', 'icon' => 'company', 'prefix' => ''],
                ['label' => 'Expired Contracts', 'value' => $stats['expired_contracts'], 'color' => '#EF4444', 'trend' => '8.43%', 'icon' => 'expired', 'prefix' => ''],
                ['label' => 'Collected Amounts', 'value' => number_format($stats['collected_amount']), 'color' => '#10B981', 'trend' => '0.41%', 'icon' => 'money', 'prefix' => '<img src="' . asset('assets/images/Vector (1).svg') . '" alt="SAR" class="inline-block w-4 h-4 align-middle">'],
            ];
@endphp

        @foreach($cardStats as $index => $stat)
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-50 flex flex-col justify-between h-48 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-md font-bold text-[#1A1A31] opacity-60">{{ __($stat['label']) }}</p>
                    <div class="flex items-center gap-3">
                        <h3 class="text-3xl font-black text-[#1A1A31]">{{ $stat['value'] }} <span class="text-sm font-bold text-slate-400">{!! $stat['prefix'] !!}</span></h3>
                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center">
                            @if($stat['icon'] == 'contract')
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            @elseif($stat['icon'] == 'company')
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            @elseif($stat['icon'] == 'expired')
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @elseif($stat['icon'] == 'money')
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $stat['trend'] > 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} border border-current opacity-60">
                            {{ $stat['trend'] }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Compared to last week') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Sparkline Chart -->
            <div class="absolute bottom-0 left-0 right-0 h-16 opacity-30 group-hover:opacity-50 transition-opacity">
                <canvas id="chart-{{ $index }}" class="w-full h-full"></canvas>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Actions Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 relative z-50">
      
         <!-- Bulk Download Button (Visible only when rows selected) -->
        <div x-show="selectedRows.length > 0" x-cloak class="flex items-center gap-4 animate-fade-in-up">
            <span class="text-sm font-bold text-slate-500 bg-slate-100 px-4 py-3 rounded-2xl">
                <span x-text="selectedRows.length"></span> {{ __('Selected') }}
            </span>
            <button @click="bulkDownload()" class="px-6 py-4 bg-[#1A1A31] text-white rounded-2xl font-black text-xs shadow-lg shadow-[#1A1A31]/20 hover:scale-[1.02] transition-all flex items-center gap-2 uppercase tracking-widest">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                {{ __('Download Selected') }}
            </button>
        </div>

   

        <!-- Search and Filter (Visible when no rows selected) -->
        <div x-show="selectedRows.length === 0" class="flex items-center gap-4 relative">
            
        

            <!-- Filter Button & Dropdown -->
            <div class="relative">
                <button @click="showFilters = !showFilters" class="w-14 h-14 flex items-center justify-center bg-white rounded-2xl shadow-sm border border-slate-50 text-[#1A1A31] hover:bg-slate-50 transition-all relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                     <!-- Red Dot Indicator -->
                     @if(request('status') || request('sort_by'))
                     <div class="absolute top-3 right-4 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></div>
                     @endif
                </button>

                <!-- Filter Dropdown Panel -->
<div x-show="showFilters" 
     @click.away="showFilters = false" 
     x-cloak 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="scale-95 translate-y-2" 
     x-transition:enter-end="scale-100 translate-y-0"
     class="absolute top-16 right-0 w-72 bg-white rounded-[2rem] shadow-2xl border border-slate-100 z-[100] p-6 space-y-6 text-right">
     
   
                    
                    <form id="filterForm" action="{{ route('admin.contracts.index') }}" method="GET" class="space-y-6">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        
                        <!-- Sort By -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest text-right">{{ __('Sort By:') }}</h4>
                            <div class="space-y-3">
                                @foreach(['' => __('All'), 'name' => __('Name'), 'newest' => __('Newest'), 'oldest' => __('Oldest')] as $val => $label)
                                <label class="flex items-center justify-between cursor-pointer group">
                                     <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                     <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                          :class="sortBy == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300'">
                                         <input type="radio" name="sort_by" value="{{ $val }}" x-model="sortBy" class="hidden">
                                         <template x-if="sortBy == '{{ $val }}'">
                                             <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                         </template>
                                     </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest text-right">{{ __('Status:') }}</h4>
                            <div class="space-y-3">
                                @foreach(['' => __('All'), 'active' => __('Active'), 'expired' => __('Expired')] as $val => $label)
                                <label class="flex items-center justify-between cursor-pointer group">
                                        <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                        <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                             :class="status == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300'">
                                            <input type="radio" name="status" value="{{ $val }}" x-model="status" class="hidden">
                                            <template x-if="status == '{{ $val }}'">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                            </template>
                                        </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                            <a href="{{ route('admin.contracts.index') }}" class="flex-1 py-3 bg-gray-200 text-slate-500 rounded-xl font-bold text-xs text-center hover:bg-slate-300 transition-all">
                                {{ __('Reset') }}
                            </a>
                            <button type="submit" class="flex-[2] p-3 bg-[#1A1A31] text-white rounded-xl font-black text-xs shadow-lg hover:scale-[1.02] transition-all">
                                {{ __('Apply') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
     
    <!-- Search -->
            <div class="relative w-full max-w-md group">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="{{ __('Search...') }}" 
                       onkeydown="if(event.key === 'Enter') { window.location.href = '{{ route('admin.contracts.index') }}?search=' + this.value }"
                       class="w-full h-14 pr-12 pl-6 bg-white border border-slate-50 rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/5 transition-all font-bold text-sm text-[#1A1A31]">
                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
               </div>
                 <!-- Add/Download Buttons (Visible when no rows selected) -->
        <div x-show="selectedRows.length === 0" class="flex items-center gap-4">
            <a href="{{ route('admin.contracts.create') }}" class="px-6 py-4 bg-[#1A1A31] text-white rounded-2xl font-black text-xs shadow-lg shadow-[#1A1A31]/20 hover:scale-[1.02] transition-all flex items-center gap-2 uppercase tracking-widest">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                {{ __('Add Contract') }}
            </a>
            <a href="{{ route('admin.contracts.download') }}" class="px-6 py-4 bg-white text-[#1A1A31] border border-slate-200 rounded-2xl font-black text-xs hover:bg-slate-50 transition-all flex items-center gap-2 uppercase tracking-widest">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                {{ __('Download') }}
            </a>
        </div>
    </div>
    

    <!-- Contracts Table Section -->
    <div class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm overflow-hidden min-h-[600px]">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="bg-slate-50/50 text-[12px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                        <th class="py-6 px-8 flex items-center gap-4 min-w-[80px]">
                            <div class="relative w-5 h-5 border-2 border-slate-200 rounded-lg flex items-center justify-center cursor-pointer"
                                 :class="selectAll ? 'bg-[#1A1A31] border-[#1A1A31]' : ''"
                                 @click="toggleSelectAll()">
                                <svg x-show="selectAll" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span>#</span>
                        </th>
                        <th class="py-6 px-4">{{ __('Contract Number') }}</th>
                        <th class="py-6 px-4">{{ __('Company Name') }}</th>
                        <th class="py-6 px-4">{{ __('Contract') }}</th>
                        <th class="py-6 px-4">{{ __('Project Value') }}</th>
                        <th class="py-6 px-4">{{ __('Paid Amount') }}</th>
                        <th class="py-6 px-4">{{ __('Remaining') }}</th>
                        <th class="py-6 px-4">{{ __('Contact') }}</th>
                        <th class="py-6 px-4 text-center">{{ __('Receipts') }}</th>
                        <th class="py-6 px-4">{{ __('Status') }}</th>
                        <th class="py-6 px-4">{{ __('Date') }}</th>
                        <th class="py-6 px-8 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                    <tr onclick="window.location.href='{{ route('admin.contracts.show', $item->id) }}'" class="hover:bg-slate-50/50 transition-all group cursor-pointer">
                        <td class="py-6 px-8 flex items-center gap-4" onclick="event.stopPropagation()">
                             <div class="relative w-5 h-5 border-2 border-slate-200 rounded-lg flex items-center justify-center cursor-pointer"
                                  :class="selectedRows.includes('{{ $item->id }}') ? 'bg-[#1A1A31] border-[#1A1A31]' : ''"
                                  @click="toggleRow('{{ $item->id }}')">
                                 <svg x-show="selectedRows.includes('{{ $item->id }}')" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                             </div>
                            <span class="text-xs font-mono opacity-40">{{ $loop->iteration }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[#1A1A31] font-black text-md">{{ $item->contract_number }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-3">
                                <span class="text-md font-bold text-[#1A1A31]">{{ $item->maintenanceCompany->name_ar ?? '-' }}</span>
                            </div>
                        </td>
                         <td class="py-6 px-4">
                             <a href="{{ route('admin.contracts.show', $item->id) }}" onclick="event.stopPropagation()" class="text-primary hover:text-primary-dark font-black text-md decoration-slice decoration-2 underline-offset-4 hover:underline">
                                 {{ __('The Contract') }}
                             </a>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-1">
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="{{ __('SAR') }}" class="w-4 h-4 align-middle">
                                <span class="text-md font-black text-[#1A1A31]">{{ number_format($item->project_value) }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-1">
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="{{ __('SAR') }}" class="w-4 h-4 align-middle">
                                <span class="text-md font-black text-[#1A1A31]">{{ number_format($item->paid_amount) }}</span>
                            </div>
                        </td>
                         <td class="py-6 px-4">
                            <div class="flex items-center gap-1">
                                <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="{{ __('SAR') }}" class="w-4 h-4 align-middle">
                                <span class="text-md font-black text-[#1A1A31]">{{ number_format($item->remaining_amount) }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                             <span class="text-xs font-bold text-slate-500">{{ $item->contact_numbers ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-black">{{ $item->paymentReceipts->count() }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase
                                {{ $item->status == 'active' ? 'bg-green-50 text-green-600' : ($item->status == 'expired' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600') }}">
                                {{ __($item->status) }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400">{{ $item->created_at->format('Y/m/d') }}</span>
                        </td>
                        <td class="py-6 px-8 text-center" onclick="event.stopPropagation()">
                            <a href="{{ route('admin.contracts.edit', $item->id) }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-20 text-center text-slate-400 font-bold uppercase tracking-widest bg-white">
                            {{ __('No contracts found currently') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="px-10 py-6 border-t border-slate-50 bg-white rounded-b-[2.5rem]">
         {{ $items->appends(request()->query())->links('vendor.pagination.custom-admin') }}
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('contractManagement', () => ({
            showFilters: false,
            // Initialize filter state from request
            sortBy: '{{ request('sort_by', '') }}',
            status: '{{ request('status', '') }}',
            // Initialize selectedRows from request old input if needed, or empty
            selectedRows: [],
            selectAll: false,
            
            toggleSelectAll() {
                this.selectAll = !this.selectAll;
                // We need the IDs. In a real scenario, this should be parsed from the rendered PHP array or a global variable.
                // For simplicity here we assume we can select potentially all displayed items.
                // NOTE: Since this is client-side only for the current page, we need the IDs of items on THIS page.
                // We'll use a helper on the checkbox itself or parse it from DOM if not passed.
                // Better approach: Let Blade render the IDs into a JS variable.
                const allIds = [{{ $items->pluck('id')->implode(',') }}];
                
                if (this.selectAll) {
                    this.selectedRows = allIds.map(String);
                } else {
                    this.selectedRows = [];
                }
            },

            toggleRow(id) {
                // Ensure ID is string for comparison
                id = String(id);
                if (this.selectedRows.includes(id)) {
                    this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
                } else {
                    this.selectedRows.push(id);
                }
                
                // Update selectAll status
                const allIds = [{{ $items->pluck('id')->implode(',') }}];
                this.selectAll = allIds.length > 0 && this.selectedRows.length === allIds.length;
            },

            bulkDownload() {
                if (this.selectedRows.length === 0) return;
                const ids = this.selectedRows.join(',');
                // Redirect to download with IDs
                window.location.href = "{{ route('admin.contracts.download') }}?ids=" + ids;
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        @foreach($cardStats as $index => $stat)
        new Chart(document.getElementById('chart-{{ $index }}').getContext('2d'), {
            type: 'line',
            data: {
                labels: Array(10).fill(''),
                datasets: [{
                    data: {!! json_encode(array_map(function() { return rand(10, 50); }, range(1, 10))) !!},
                    borderColor: '{{ $stat["color"] }}',
                    borderWidth: 3,
                    fill: {
                        target: 'origin',
                        above: '{{ $stat["color"] }}10'
                    },
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: { 
                    x: { display: false }, 
                    y: { display: false, min: 0 } 
                },
                elements: {
                    line: {
                        tension: 0.4
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        });
        @endforeach
    });
</script>
@endsection