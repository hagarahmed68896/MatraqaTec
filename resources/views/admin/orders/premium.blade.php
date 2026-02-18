@extends('layouts.admin')

@section('title', __('Orders') . ' - ' . __('MatraqaTec'))

@section('content')
<div class="space-y-8 pb-20" dir="rtl" x-data="orderManagement()">
    
    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-[#1A1A31]">{{ __('Orders') }}</h1>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $cardStats = [
                ['label' => 'Total Orders', 'value' => $stats['total'], 'color' => '#10B981', 'trend' => '8.43%', 'icon' => 'orders'],
                ['label' => 'Scheduled', 'value' => $stats['scheduled'], 'color' => '#10B981', 'trend' => '0.43%', 'icon' => 'accepted'],
                ['label' => 'In Progress', 'value' => $stats['in_progress'], 'color' => '#EF4444', 'trend' => '8.43%', 'icon' => 'rejected'],
                ['label' => 'Completed', 'value' => $stats['completed'], 'color' => '#64748B', 'trend' => '0.43%', 'icon' => 'review'],
            ];
        @endphp

        @foreach($cardStats as $index => $stat)
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-50 flex flex-col justify-between h-48 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-md font-bold text-[#1A1A31] opacity-60">{{ __($stat['label']) }}</p>
                    <div class="flex items-center gap-3">
                        <h3 class="text-3xl font-black text-[#1A1A31]">{{ $stat['value'] }}</h3>
                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
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

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-4">
        @php
            $tabs = [
                ['id' => 'scheduled', 'label' => 'Scheduled', 'count' => $stats['scheduled']],
                ['id' => 'in_progress', 'label' => 'In Progress', 'count' => $stats['in_progress']],
                ['id' => 'completed', 'label' => 'Completed', 'count' => $stats['completed']],
            ];
            $currentTab = request('tab', 'scheduled');
        @endphp

        @foreach($tabs as $tab)
        <a href="{{ request()->fullUrlWithQuery(['tab' => $tab['id']]) }}" 
           class="flex items-center gap-3 px-8 py-3 rounded-2xl font-black text-md transition-all {{ $currentTab == $tab['id'] ? 'bg-[#1A1A31] text-white shadow-lg' : 'bg-white text-[#1A1A31] border border-slate-100 hover:bg-slate-50' }}">
            {{ __($tab['label']) }}
            <span class="w-6 h-6 rounded-lg {{ $currentTab == $tab['id'] ? 'bg-white/20 text-white' : 'bg-slate-100 text-[#1A1A31]' }} flex items-center justify-center text-[11px]">{{ $tab['count'] }}</span>
        </a>
        @endforeach
    </div>

    <!-- SEARCH & FILTERS -->
    <div class="flex  justify-between gap-6 relative">
        <!-- Right Side: Search + Filter -->
        <div class="flex items-end gap-6 flex-1  relative">
            <!-- Filter Button -->
            <button @click="showFilters = !showFilters" class="w-16 h-16 flex items-center justify-center bg-white rounded-2xl shadow-sm border border-slate-50 text-[#1A1A31] hover:bg-slate-50 transition-all relative">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                <div x-show="showFilters" class="absolute top-0 right-0 w-3 h-3 bg-red-500 border-2 border-white rounded-full"></div>
            </button>

            <!-- Filter Dropdown Panel -->
            <div x-show="showFilters" @click.away="showFilters = false" x-cloak 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute top-20 right-0 mt-4 w-80 bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 z-[100] p-6 space-y-6 text-right overflow-y-auto max-h-[calc(100vh-200px)] custom-scrollbar">
                
                <form id="filterForm" action="{{ route('admin.orders.premium') }}" method="GET" class="space-y-8">
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                    <input type="hidden" name="search" :value="searchQuery">
                    
                    <!-- Filter Section: Sort -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('Sort by:') }}</h4>
                        <div class="space-y-3">
                            @foreach(['newest' => __('Newest'), 'oldest' => __('Oldest'), 'name' => __('Name')] as $val => $label)
                            <label class="flex items-center justify-between cursor-pointer group">
                                 <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                 <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                      :class="sortBy == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200'">
                                     <input type="radio" name="sort_by" value="{{ $val }}" x-model="sortBy" class="hidden">
                                     <template x-if="sortBy == '{{ $val }}'">
                                         <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                     </template>
                                 </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="h-px bg-slate-50"></div>

                    <!-- Filter Section: Customer Type -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('Customer Type:') }}</h4>
                        <div class="space-y-3">
                            @foreach(['' => __('All'), 'client' => __('Individual'), 'corporate' => __('Corporate')] as $val => $label)
                            <label class="flex items-center justify-between cursor-pointer group">
                                 <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                 <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                      :class="customerType == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200'">
                                     <input type="radio" name="customer_type" value="{{ $val }}" x-model="customerType" class="hidden">
                                     <template x-if="customerType == '{{ $val }}'">
                                         <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                     </template>
                                 </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="h-px bg-slate-50"></div>

                    <!-- Filter Section: Technician Type -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('Technician Type:') }}</h4>
                        <div class="space-y-3">
                            @foreach(['' => __('All'), 'platform' => __('Platform'), 'company' => __('Company')] as $val => $label)
                            <label class="flex items-center justify-between cursor-pointer group">
                                 <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                 <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                      :class="technicianType == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200'">
                                     <input type="radio" name="technician_type" value="{{ $val }}" x-model="technicianType" class="hidden">
                                     <template x-if="technicianType == '{{ $val }}'">
                                         <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                     </template>
                                 </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="h-px bg-slate-50"></div>

                    <!-- Filter Section: Status -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('Status:') }}</h4>
                        <div class="space-y-3">
                            @foreach(['' => __('All'), 'assigned' => __('Assigned'), 'waiting' => __('Waiting')] as $val => $label)
                            <label class="flex items-center justify-between cursor-pointer group">
                                 <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                 <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                      :class="appointmentStatus == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200'">
                                     <input type="radio" name="appointment_status" value="{{ $val }}" x-model="appointmentStatus" class="hidden">
                                     <template x-if="appointmentStatus == '{{ $val }}'">
                                         <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                     </template>
                                 </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="h-px bg-slate-50"></div>

                    <!-- Filter Section: Sub Status -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('Sub Status:') }}</h4>
                        <div class="space-y-3">
                            @foreach(['' => __('All'), 'on_way' => __('On Way'), 'arrived' => __('Arrived'), 'work_started' => __('Work Started'), 'additional_visit' => __('Additional Visit'), 'completed' => __('Completed')] as $val => $label)
                            <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                    <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                        :class="subStatus == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200'">
                                        <input type="radio" name="sub_status" value="{{ $val }}" x-model="subStatus" class="hidden">
                                        <template x-if="subStatus == '{{ $val }}'">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </template>
                                    </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="h-px bg-slate-50"></div>

                    <!-- Filter Section: Service Category -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('Service Category:') }}</h4>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between cursor-pointer group">
                                 <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ __('All') }}</span>
                                 <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                      :class="!selectedCategoryId ? 'border-primary bg-primary' : 'border-slate-200'">
                                     <input type="radio" name="service_category_id" value="" x-model="selectedCategoryId" class="hidden">
                                     <template x-if="!selectedCategoryId">
                                         <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                     </template>
                                 </div>
                            </label>
                            @foreach($categories ?? [] as $cat)
                            <label class="flex items-center justify-between cursor-pointer group" @click="selectedServiceIds = []">
                                 <span class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $cat->name_ar }}</span>
                                 <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                                      :class="selectedCategoryId == {{ $cat->id }} ? 'border-primary bg-primary' : 'border-slate-200'">
                                     <input type="radio" name="service_category_id" value="{{ $cat->id }}" x-model="selectedCategoryId" class="hidden">
                                     <template x-if="selectedCategoryId == {{ $cat->id }}">
                                         <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                     </template>
                                 </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filter Section: Service Type (Child Services) -->
                    <div class="space-y-4" x-show="filteredServices.length > 0" x-cloak>
                        <div class="h-px bg-slate-50 mb-4"></div>
                        <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('Service Type:') }}</h4>
                        <div class="space-y-3 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                            <template x-for="service in filteredServices" :key="service.id">
                                <label class="flex items-center justify-between cursor-pointer group">
                                     <span class="text-[11px] font-bold text-slate-500 group-hover:text-primary transition-colors" x-text="service.name_ar"></span>
                                     <div class="relative w-5 h-5 border-2 rounded-lg transition-all flex items-center justify-center"
                                          :class="selectedServiceIds.includes(String(service.id)) ? 'border-primary bg-primary' : 'border-slate-200'">
                                         <input type="checkbox" name="service_ids[]" :value="service.id" x-model="selectedServiceIds" class="hidden">
                                         <template x-if="selectedServiceIds.includes(String(service.id))">
                                             <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                         </template>
                                     </div>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-4">
                        <button type="submit" class="flex-1 py-3 bg-[#1A1A31] text-white rounded-xl font-black text-[10px] shadow-lg hover:scale-[1.02] transition-all">
                            {{ __('Apply') }}
                        </button>
                        <a href="{{ route('admin.orders.premium') }}" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl font-bold text-[10px] text-center hover:bg-slate-100 transition-all">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>   
        <div class="w-full max-w-2xl relative group">
                <input type="text" x-model="searchQuery" @keyup.enter="document.getElementById('filterForm').submit()"
                       class="w-full h-16 pr-14 pl-6 bg-white border border-slate-50 rounded-[1.5rem] shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/5 transition-all font-bold text-md text-[#1A1A31]">
                <div class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

        
        </div>

        <!-- Left Side: Download Button -->
        <form action="{{ route('admin.orders.premium') }}" method="GET" class="inline">
            <input type="hidden" name="download" value="1">
            <input type="hidden" name="tab" value="{{ request('tab') }}">
            @foreach(request()->except(['download', '_token']) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <button type="submit" class="w-16 h-16 flex items-center justify-center bg-white rounded-2xl shadow-sm border border-slate-50 text-[#1A1A31] hover:bg-slate-50 transition-all">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            </button>
        </form>
    </div>

    <!-- Orders Table Section -->
    <div class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm overflow-hidden min-h-[600px]">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="bg-slate-50/50 text-[12px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                        <th class="py-6 px-4"></th>
                        <th class="py-6 px-4">{{ __('Order Number') }}</th>
                        <th class="py-6 px-4">{{ __('Customer Name') }}</th>
                        <th class="py-6 px-4">{{ __('Customer Type') }}</th>
                        <th class="py-6 px-4">{{ __('Service Name') }}</th>
                        <th class="py-6 px-4">{{ __('Service Type') }}</th>
                        <th class="py-6 px-4">{{ __('Address') }}</th>
                        <th class="py-6 px-4">{{ __('Technician Name') }}</th>
                        <th class="py-6 px-4">{{ __('Technician Type') }}</th>
                        <th class="py-6 px-4">{{ __('Price') }}</th>
                        <th class="py-6 px-4">{{ __('Status') }}</th>
                        <th class="py-6 px-4">{{ __('Date/Time') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-all cursor-pointer" 
                        onclick="window.location='{{ route('admin.orders.show', ['id' => $item->id, 'from' => 'premium']) }}'">
                        <td class="py-6 px-4">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary" onclick="event.stopPropagation()">
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-xs font-bold text-[#1A1A31]">{{ $item->order_number }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 shrink-0">
                                    @if($item->user->avatar)
                                        <img src="{{ Storage::url($item->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-black">
                                            {{ mb_substr($item->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-[#1A1A31]">{{ $item->user->name }}</span>
                                    <span class="block text-[10px] font-bold text-slate-400">{{ $item->user->phone }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-3 py-1 rounded-lg bg-slate-50 text-slate-600 text-[10px] font-bold">{{ $item->user->type == 'client' ? __('Individual') : __('Corporate') }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400">{{ $item->service->name_ar }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400">{{ $item->service->parent->name_ar ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400">{{ __('Address') }}</span>
                        </td>
                        <td class="py-6 px-4">
                            @if($item->technician)
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 shrink-0">
                                        @if($item->technician->user->avatar)
                                            <img src="{{ Storage::url($item->technician->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-[10px] font-black">
                                                {{ mb_substr($item->technician->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-400">{{ $item->technician->user->name }}</span>
                                </div>
                            @elseif($item->maintenanceCompany)
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 shrink-0">
                                        @if($item->maintenanceCompany->user->avatar)
                                            <img src="{{ Storage::url($item->maintenanceCompany->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-[10px] font-black">
                                                {{ mb_substr($item->maintenanceCompany->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-400">{{ $item->maintenanceCompany->user->name }}</span>
                                </div>
                            @else
                                <span class="text-[11px] font-bold text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-3 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold">
                                {{ $item->maintenance_company_id ? __('Company') : ($item->technician_id ? __('Platform') : '-') }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <div class="flex items-center gap-1 font-black text-[#1A1A31]">
                                <span>{{ number_format($item->total_price ?? 0, 2) }}</span>
                                <span class="text-[14px] opacity-60">﷼</span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            @if($item->status == 'scheduled')
                                <span class="px-3 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold">{{ __('Scheduled') }}</span>
                            @elseif($item->status == 'in_progress')
                                @php
                                    $subStatusClass = match($item->sub_status) {
                                        'on_way' => 'bg-sky-50 text-sky-600',
                                        'arrived' => 'bg-purple-50 text-purple-600',
                                        'work_started' => 'bg-orange-50 text-orange-600',
                                        'additional_visit' => 'bg-pink-50 text-pink-600',
                                        default => 'bg-yellow-50 text-yellow-600'
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-lg {{ $subStatusClass }} text-[10px] font-bold">
                                    {{ $item->sub_status ? __('order.' . $item->sub_status) : __('In Progress') }}
                                </span>
                            @elseif($item->status == 'completed')
                                <span class="px-3 py-1 rounded-lg bg-green-50 text-green-600 text-[10px] font-bold">{{ __('Completed') }}</span>
                            @else
                                <span class="px-3 py-1 rounded-lg {{ $item->technician_id || $item->maintenance_company_id ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }} text-[10px] font-bold">
                                    {{ $item->technician_id || $item->maintenance_company_id ? __('Assigned') : __('Waiting') }}
                                </span>
                            @endif
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400">{{ $item->created_at->format('j/n/Y - g:i') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-20 text-center text-slate-300 font-bold">{{ __('No orders found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($items->hasPages())
        <div class="p-10 border-t border-slate-50 bg-slate-50/50">
            {{ $items->appends(request()->all())->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Alpine.js Component
function orderManagement() {
    return {
        showFilters: false,
        searchQuery: '{{ request('search') }}',
        sortBy: '{{ request('sort_by', 'newest') }}',
        customerType: '{{ request('customer_type', '') }}',
        technicianType: '{{ request('technician_type', '') }}',
        appointmentStatus: '{{ request('appointment_status', '') }}',
        subStatus: '{{ request('sub_status', '') }}',
        selectedCategoryId: {{ request('service_category_id') ? request('service_category_id') : 'null' }},
        selectedServiceIds: {!! json_encode(request('service_ids', [])) !!},
        allServices: @json($services ?? []),
        
        get filteredServices() {
            if (!this.selectedCategoryId) return [];
            return this.allServices.filter(s => s.parent_id == this.selectedCategoryId);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Sparkline Charts
    @foreach($cardStats as $index => $stat)
    new Chart(document.getElementById('chart-{{ $index }}'), {
        type: 'line',
        data: {
            labels: [1,2,3,4,5,6,7],
            datasets: [{
                data: [10, 15, 8, 20, 12, 25, 18],
                borderColor: '{{ $stat['color'] }}',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } },
            elements: { point: { radius: 0 } }
        }
    });
    @endforeach
});
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
table thead th { background: transparent; }
</style>
@endsection
