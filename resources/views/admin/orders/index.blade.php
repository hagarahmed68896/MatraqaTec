@extends('layouts.admin')

@section('title', __('Orders Management'))
@section('page_title', __('Orders Management'))

@section('content')
<div x-data="orderManagement()" class="space-y-8 pb-20" dir="rtl">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
    #tech-map { width: 100%; height: 500px; border-radius: 3rem; }
    .leaflet-container { background: #f8fafc; }
</style>
    
    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-[#1A1A31]">{{ __('New Orders') }}</h1>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $cardStats = [
                ['label' => 'Total Orders', 'value' => $stats['total'], 'color' => '#10B981', 'trend' => '8.43%', 'icon' => 'orders'],
                ['label' => 'Accepted Orders', 'value' => $stats['scheduled'], 'color' => '#10B981', 'trend' => '0.43%', 'icon' => 'accepted'],
                ['label' => 'Rejected Orders', 'value' => $stats['rejected'], 'color' => '#EF4444', 'trend' => '8.43%', 'icon' => 'rejected'],
                ['label' => 'Orders in Review', 'value' => $stats['new'], 'color' => '#64748B', 'trend' => '0.43%', 'icon' => 'review'],
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
                ['id' => 'new', 'label' => 'New Orders', 'count' => $stats['new']],
                ['id' => 'scheduled', 'label' => 'Accepted Orders', 'count' => $stats['scheduled']],
                ['id' => 'rejected', 'label' => 'Rejected Orders', 'count' => $stats['rejected']],
            ];
            $currentTab = request('tab', 'new');
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
    <div class="flex items-center justify-center gap-6 relative">
        <div class="w-full max-w-2xl relative group">
            <input type="text" x-model="searchQuery" @keyup.enter="document.getElementById('filterForm').submit()"
                   class="w-full h-16 pr-14 pl-6 bg-white border border-slate-50 rounded-[1.5rem] shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/5 transition-all font-bold text-md text-[#1A1A31]">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <!-- Filter Dropdown Trigger -->
        <button @click="showFilters = !showFilters" 
                class="w-16 h-16 flex items-center justify-center bg-white rounded-2xl shadow-sm border border-slate-50 text-[#1A1A31] hover:bg-slate-50 transition-all relative">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
            <div x-show="showFilters" class="absolute top-0 right-0 w-3 h-3 bg-red-500 border-2 border-white rounded-full"></div>
        </button>

        <!-- Filter Dropdown Panel -->
        <div x-show="showFilters" @click.away="showFilters = false" x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="absolute top-20 right-0 mt-4 w-80 bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-6 space-y-6 text-right overflow-y-auto max-h-[calc(100vh-200px)] custom-scrollbar">
            
            <form id="filterForm" action="{{ route('admin.orders.index') }}" method="GET" class="space-y-8">
                <input type="hidden" name="tab" value="{{ request('tab') }}">
                <input type="hidden" name="search" :value="searchQuery">
                
                <!-- Filter Section: Sort -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">
                        {{ __('Sort by:') }}
                    </h4>
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

                <!-- Filter Section: Appointment Status -->
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

                    <!-- Filter Section: Service Category (RADIO) -->
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
                            @foreach($categories as $cat)
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

                    <!-- Filter Section: Service Type (Child Services) - DYNAMIC MULTISELECT -->
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
                    <a href="{{ route('admin.orders.index') }}" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl font-bold text-[10px] text-center hover:bg-slate-100 transition-all">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table Section -->
    <div class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm overflow-hidden min-h-[600px] mt-8">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="bg-slate-50/50 text-[12x] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                        <th class="py-6 px-8 flex items-center gap-4">
                            <input type="checkbox" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-[#1A1A31] focus:ring-0">
                            <span>#</span>
                        </th>
                        <th class="py-6 px-4">{{ __('Order Number') }}</th>
                        <th class="py-6 px-4">{{ __('Customer Name') }}</th>
                        <th class="py-6 px-4">{{ __('Customer Type') }}</th>
                        <th class="py-6 px-4">{{ __('Service Name') }}</th>
                        <th class="py-6 px-4">{{ __('Service Type') }}</th>
                        @if($currentTab == 'scheduled')
                        <th class="py-6 px-4">{{ __('Technician Name') }}</th>
                        <th class="py-6 px-4">{{ __('Technician Type') }}</th>
                        <th class="py-6 px-4">{{ __('Status') }}</th>
                        @endif
                        <th class="py-6 px-4 text-center">{{ __('Address') }}</th>
                        <th class="py-6 px-4">{{ __('Date/Time') }}</th>
                        @if($currentTab == 'rejected')
                        <th class="py-6 px-4">{{ __('Rejection Reason') }}</th>
                        @endif
                        @if($currentTab == 'new')
                        <th class="py-6 px-8 text-center">{{ __('Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                    <tr onclick="window.location.href='{{ route('admin.orders.show', $item->id) }}'" class="hover:bg-slate-50/50 transition-all group cursor-pointer">
                        <td class="py-6 px-8 flex items-center gap-4" onclick="event.stopPropagation()">
                            <input type="checkbox" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-[#1A1A31] focus:ring-0">
                            <span class="text-xs font-mono opacity-40">{{ $loop->iteration }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <a href="{{ route('admin.orders.show', $item->id) }}" class="text-primary font-black text-md hover:underline">{{ app()->getLocale() == 'ar' ? 'طلب-' : 'Order-' }}{{ $item->order_number }}</a>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-md font-bold text-[#1A1A31]">{{ $item->user->name }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-4 py-1.5 rounded-xl bg-slate-50 text-[10px] font-bold text-slate-600">{{ $item->user->type == 'client' ? __('Individual') : __('Corporate') }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-md font-bold text-slate-500">{{ $item->service->name_ar }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-md font-bold text-slate-500">{{ $item->service->parent->name_ar ?? '-' }}</span>
                        </td>
                        @if($currentTab == 'scheduled')
                        <td class="py-6 px-4">
                            <span class="text-md font-bold text-[#1A1A31]">{{ $item->technician->user->name ?? ($item->maintenanceCompany->user->name ?? '-') }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-3 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold">
                                {{ $item->maintenance_company_id ? __('Company') : ($item->technician_id ? __('Platform') : '-') }}
                            </span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-3 py-1 rounded-lg {{ $item->technician_id || $item->maintenance_company_id ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }} text-[10px] font-bold">
                                {{ $item->technician_id || $item->maintenance_company_id ? __('Assigned') : __('Waiting') }}
                            </span>
                        </td>
                        @endif
                        <td class="py-6 px-4 text-center">
                            <span class="text-[11px] font-bold text-slate-400">{{ __('Address') }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400">{{ $item->created_at->format('j/n/2025 - g:i') }}</span>
                        </td>
                        @if($currentTab == 'rejected')
                        <td class="py-6 px-4">
                            <span class="text-md font-bold text-red-500">{{ $item->rejection_reason ?? '-' }}</span>
                        </td>
                        @endif
                        @if($currentTab == 'new')
                        <td class="py-6 px-8" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-3">
                                <!-- Refuse Button (X) -->
                                <button type="button" @click.stop="openRefuseModal({{ $item->id }})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <!-- Accept Button (Checkmark) -->
                                <button type="button" @click.stop="openAcceptModal({{ $item->id }})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1A1A31] text-white shadow-lg shadow-[#1A1A31]/20 hover:scale-110 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-20 text-center text-slate-400 font-bold uppercase tracking-widest bg-white">
                            {{ __('No orders found currently') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- ACCEPT ORDER MODAL -->
            <div x-show="showAcceptModal" x-cloak 
                 class="fixed inset-0 z-[200] flex items-center justify-center p-6 bg-[#1A1A31]/40 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                
                <div class="bg-white rounded-[3rem] w-full max-w-2xl shadow-2xl relative overflow-hidden"
                     @click.away="showAcceptModal = false"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                    
                    <!-- Close Button -->
                    <button @click="showAcceptModal = false" class="absolute top-8 left-8 text-slate-300 hover:text-slate-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div class="p-12 text-center space-y-8">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-black text-[#1A1A31]">{{ __('Accept Order') }}</h2>
                            <button @click="toggleViewMode()" class="px-6 py-2 rounded-xl border border-slate-100 font-bold text-xs text-[#1A1A31] hover:bg-slate-50 transition-all flex items-center gap-2">
                                <template x-if="viewMode === 'list'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ __('Show Map') }}
                                    </span>
                                </template>
                                <template x-if="viewMode === 'map'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                        {{ __('Show List') }}
                                    </span>
                                </template>
                            </button>
                        </div>

                        <!-- LIST VIEW -->
                        <div x-show="viewMode === 'list'" class="space-y-8">
                            <!-- Tabs -->
                            <div class="flex p-1.5 bg-slate-50 rounded-2xl">
                                <button @click="activeTab = 'platform'; fetchTechnicians()" 
                                        :class="activeTab === 'platform' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-400'"
                                        class="flex-1 py-3 rounded-xl font-bold text-md transition-all whitespace-nowrap px-4">
                                    {{ __('Assign Platform Technician') }}
                                </button>
                                <button @click="activeTab = 'company'; fetchCompanies()" 
                                        :class="activeTab === 'company' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-400'"
                                        class="flex-1 py-3 rounded-xl font-bold text-md transition-all whitespace-nowrap px-4">
                                    {{ __('Send Order to Maintenance Company') }}
                                </button>
                            </div>

                            <p class="text-slate-400 font-bold text-md leading-relaxed" x-text="activeTab === 'platform' ? '{{ __('Select an available technician to perform the maintenance request') }}' : '{{ __('Select an available maintenance company to perform the request') }}'"></p>

                            <!-- List Section -->
                            <div class="space-y-6 text-right">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-md font-black text-[#1A1A31]" x-text="activeTab === 'platform' ? '{{ __('Technicians') }}' : '{{ __('Maintenance Companies') }}'"></h3>
                                </div>

                                <!-- Items Container -->
                                <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                    <template x-if="(activeTab === 'platform' && loadingTechs) || (activeTab === 'company' && loadingCompanies)">
                                        <div class="py-10 text-center">
                                            <div class="inline-block w-8 h-8 border-4 border-[#1A1A31] border-t-transparent rounded-full animate-spin"></div>
                                        </div>
                                    </template>

                                    <template x-if="activeTab === 'platform' && !loadingTechs">
                                        <div class="space-y-4">
                                            <template x-for="tech in technicians" :key="tech.id">
                                                <label class="flex items-center gap-6 p-6 rounded-[2rem] border-2 transition-all cursor-pointer group"
                                                       :class="selectedTechId === tech.id ? 'border-primary bg-primary/5 shadow-sm' : 'border-slate-50 hover:border-slate-100'">
                                                    <div class="flex-1 flex items-center gap-4">
                                                        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100">
                                                            <img :src="tech.avatar || '/assets/admin/images/avatar-placeholder.png'" class="w-full h-full object-cover">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <h4 class="font-black text-[#1A1A31] group-hover:text-primary transition-colors" x-text="tech.name"></h4>
                                                            <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400">
                                                                <span x-text="tech.service_name"></span>
                                                                <span class="flex items-center gap-1">
                                                                    <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                                    <span x-text="`{{ __('Rating:') }} ${tech.rating}`"></span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                                         :class="selectedTechId === tech.id ? 'border-primary bg-primary' : 'border-slate-200 group-hover:border-primary/50'">
                                                        <input type="radio" x-model="selectedTechId" :value="tech.id" class="hidden">
                                                        <svg x-show="selectedTechId === tech.id" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="activeTab === 'company' && !loadingCompanies">
                                        <div class="space-y-4">
                                            <template x-for="comp in companies" :key="comp.id">
                                                <label class="flex items-center gap-6 p-6 rounded-[2rem] border-2 transition-all cursor-pointer group"
                                                       :class="selectedCompanyId === comp.id ? 'border-primary bg-primary/5 shadow-sm' : 'border-slate-50 hover:border-slate-100'">
                                                    <div class="flex-1 flex items-center gap-4">
                                                        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100">
                                                            <img :src="comp.avatar || '/assets/admin/images/avatar-placeholder.png'" class="w-full h-full object-cover">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <h4 class="font-black text-[#1A1A31] group-hover:text-primary transition-colors" x-text="comp.name"></h4>
                                                            <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400">
                                                                <span class="flex items-center gap-1">
                                                                    <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                                    <span x-text="`{{ __('Rating:') }} ${comp.rating}`"></span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                                         :class="selectedCompanyId === comp.id ? 'border-primary bg-primary' : 'border-slate-200 group-hover:border-primary/50'">
                                                        <input type="radio" x-model="selectedCompanyId" :value="comp.id" class="hidden">
                                                        <svg x-show="selectedCompanyId === comp.id" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="(!loadingTechs && activeTab === 'platform' && technicians.length === 0) || (!loadingCompanies && activeTab === 'company' && companies.length === 0)">
                                        <div class="py-10 text-center text-slate-400 font-bold italic">
                                            {{ __('No results available currently for this request') }}
                                        </div>
                                    </template>
                                </div>

                                <button type="button" @click="activeTab === 'platform' ? fetchTechnicians(true) : fetchCompanies(true)" class="w-full py-4 border-2 border-slate-100 rounded-2xl font-black text-xs text-slate-400 hover:bg-slate-50 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                                    {{ __('Show More') }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- MAP VIEW -->
                        <div x-show="viewMode === 'map'" class="relative" style="min-height: 500px;" x-effect="if(searchMap !== undefined) renderMarkers()">
                            <!-- Top Left Buttons (Close/Expand) -->
                            <div class="absolute top-6 right-6 z-[1000] flex gap-3 pointer-events-auto">
                                <button @click="viewMode = 'list'" class="w-10 h-10 rounded-xl bg-white/90 backdrop-blur-md border border-slate-100 shadow-lg flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <button class="w-10 h-10 rounded-xl bg-white/90 backdrop-blur-md border border-slate-100 shadow-lg flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                </button>
                            </div>

                            <!-- Floating Header Elements -->
                            <div class="absolute top-6 left-6 right-20 z-[1000] flex gap-4 pointer-events-none">
                                <!-- Search bar -->
                                <div class="flex-1 max-w-sm pointer-events-auto">
                                    <div class="relative">
                                        <input type="text" x-model="searchMap" @input="renderMarkers()" placeholder="{{ __('Search...') }}" class="w-full h-12 pr-12 pl-4 bg-white/90 backdrop-blur-md border border-slate-100 rounded-2xl shadow-lg focus:outline-none font-bold text-md">
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex-1 flex justify-end pointer-events-auto gap-3">
                                    <button @click="activeTab = 'platform'; fetchTechnicians()" 
                                            :class="activeTab === 'platform' ? 'bg-[#1A1A31] text-white' : 'bg-white/90 text-slate-400'"
                                            class="px-6 py-3 rounded-2xl font-black text-xs shadow-lg backdrop-blur-md transition-all">
                                        {{ __('Assign Platform Technician') }}
                                    </button>
                                    <button @click="activeTab = 'company'; fetchCompanies()" 
                                            :class="activeTab === 'company' ? 'bg-[#1A1A31] text-white' : 'bg-white/90 text-slate-400'"
                                            class="px-6 py-3 rounded-2xl font-black text-xs shadow-lg backdrop-blur-md transition-all">
                                        {{ __('Send Order to Maintenance Company') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Map Container -->
                            <div id="tech-map" class="z-[1]"></div>

                            <!-- Technician Card (Floating) -->
                            <template x-if="selectedMapTech">
                                <div class="absolute bottom-10 right-10 z-[1000] w-[340px] bg-white rounded-[2.5rem] shadow-2xl p-8 text-right space-y-6 animate-fade-in-up">
                                    <div class="flex items-start justify-between">
                                        <span class="px-3 py-1 bg-green-50 text-green-600 rounded-lg text-[10px] font-black" x-text="selectedMapTech.status_label"></span>
                                        <div class="flex items-center gap-4">
                                            <div class="text-right">
                                                <h4 class="font-black text-[#1A1A31] text-md" x-text="selectedMapTech.name"></h4>
                                                <p class="text-[10px] font-bold text-slate-400" x-text="selectedMapTech.specialty"></p>
                                            </div>
                                            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100">
                                                <img :src="selectedMapTech.avatar || '/assets/admin/images/avatar-placeholder.png'" class="w-full h-full object-cover">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4 pt-4 border-t border-slate-50">
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span class="text-[#1A1A31]" x-text="selectedMapTech.service_name"></span>
                                            <span class="text-slate-400 flex items-center gap-2">
                                                {{ __('Service Type:') }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span class="text-[#1A1A31]" x-text="selectedMapTech.district"></span>
                                            <span class="text-slate-400 flex items-center gap-2">
                                                {{ __('Regions:') }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span class="text-[#1A1A31]" x-text="`${selectedMapTech.order_count} {{ __('Orders') }}`"></span>
                                            <span class="text-slate-400 flex items-center gap-2">
                                                {{ __('Orders Count:') }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                <span class="text-[#1A1A31]" x-text="selectedMapTech.rating"></span>
                                            </div>
                                            <span class="text-slate-400 flex items-center gap-2">
                                                {{ __('Average Rating:') }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                            </span>
                                        </div>
                                    </div>

                                    <form :action="`/admin/orders/${selectedOrder}/accept`" method="POST">
                                        @csrf
                                        <template x-if="activeTab === 'platform'">
                                            <input type="hidden" name="technician_id" :value="selectedMapTech.id">
                                        </template>
                                        <template x-if="activeTab === 'company'">
                                            <input type="hidden" name="maintenance_company_id" :value="selectedMapTech.id">
                                        </template>
                                        <button type="submit" class="w-full py-4 bg-[#1A1A31] text-white rounded-2xl font-black text-xs shadow-xl shadow-[#1A1A31]/20 hover:scale-[1.02] transition-all uppercase tracking-widest">
                                            {{ __('Assign Technician') }}
                                        </button>
                                    </form>
                                </div>
                            </template>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex gap-4 pt-6">
                            <form :action="`/admin/orders/${selectedOrder}/accept`" method="POST" class="flex-1">
                                @csrf
                                <template x-if="activeTab === 'platform'">
                                    <input type="hidden" name="technician_id" :value="selectedTechId">
                                </template>
                                <template x-if="activeTab === 'company'">
                                    <input type="hidden" name="maintenance_company_id" :value="selectedCompanyId">
                                </template>
                                        <button type="submit" 
                                                :disabled="(activeTab === 'platform' && !selectedTechId) || (activeTab === 'company' && !selectedCompanyId)"
                                                :class="(activeTab === 'platform' && selectedTechId) || (activeTab === 'company' && selectedCompanyId) ? 'bg-[#1A1A31] shadow-xl shadow-[#1A1A31]/20 hover:scale-[1.02]' : 'bg-slate-300 cursor-not-allowed'"
                                                class="w-full py-5 text-white rounded-[1.5rem] font-black text-md transition-all transform uppercase tracking-widest">
                                            <span x-text="activeTab === 'platform' ? '{{ __('Send assignment to technician') }}' : '{{ __('Send order to company') }}'"></span>
                                        </button>
                            </form>
                                <button @click="showAcceptModal = false" class="flex-[0.5] py-5 bg-slate-100 text-slate-400 rounded-[1.5rem] font-bold text-md hover:bg-slate-200 transition-all">
                                    {{ __('Cancel') }}
                                </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REFUSE ORDER MODAL -->
            <div x-show="showRefuseModal" x-cloak 
                 class="fixed inset-0 z-[200] flex items-center justify-center p-6 bg-[#1A1A31]/40 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                
                <div class="bg-white rounded-[3rem] w-full max-w-xl shadow-2xl relative overflow-hidden"
                     @click.away="showRefuseModal = false"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                    
                    <button @click="showRefuseModal = false" class="absolute top-8 left-8 text-slate-300 hover:text-slate-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div class="p-12 text-center space-y-8">
                        <div class="w-20 h-20 bg-red-50 rounded-[2rem] flex items-center justify-center mx-auto text-red-500">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-2xl font-black text-[#1A1A31]">{{ __('Refuse Order') }}</h2>
                            <p class="text-slate-400 font-bold text-md">{{ __('Please state the reason for rejection to clarify to the customer') }}</p>
                        </div>

                        <form :action="`/admin/orders/${selectedOrder}/refuse`" method="POST" class="space-y-8">
                            @csrf
                            <textarea name="rejection_reason" x-model="rejectionReason" required
                                      placeholder="{{ __('Write the rejection reason here...') }}"
                                      class="w-full h-40 p-6 bg-slate-50 border-none rounded-[2rem] focus:ring-2 focus:ring-red-500/20 transition-all font-bold text-md text-[#1A1A31] resize-none"></textarea>

                            <div class="flex gap-4">
                                <button type="submit" 
                                        :disabled="!rejectionReason.trim()"
                                        :class="rejectionReason.trim() ? 'bg-red-500 shadow-xl shadow-red-500/20 hover:scale-[1.02]' : 'bg-slate-300 cursor-not-allowed'"
                                        class="flex-1 py-5 text-white rounded-[1.5rem] font-black text-md transition-all transform capitalize tracking-widest">
                                    {{ __('Confirm Rejection') }}
                                </button>
                                <button type="button" @click="showRefuseModal = false" class="flex-[0.5] py-5 bg-slate-100 text-slate-400 rounded-[1.5rem] font-bold text-md hover:bg-slate-200 transition-all">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div> <!-- Outer Wrapper -->
    </div> <!-- Main Alpine Container -->

    <!-- Pagination & Footer -->
    <div class="px-10 py-6 border-t border-slate-50 bg-white rounded-b-[2.5rem]">
        {{ $items->appends(request()->query())->links('vendor.pagination.custom-admin') }}
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('orderManagement', () => ({
            showFilters: false,
            showAcceptModal: false,
            selectedOrder: null,
            viewMode: 'list',
            map: null,
            markers: [],
            selectedMapTech: null,
            searchMap: '',
            technicians: [],
            companies: [],
            loadingTechs: false,
            loadingCompanies: false,
            activeTab: 'platform',
            selectedTechId: null,
            selectedCompanyId: null,
            showRefuseModal: false,
            rejectionReason: '',
            
            searchQuery: '{{ request('search') }}',
            sortBy: '{{ request('sort_by', 'newest') }}',
            customerType: '{{ request('customer_type', '') }}',
            technicianType: '{{ request('technician_type', '') }}',
            appointmentStatus: '{{ request('appointment_status', '') }}',
            subStatus: '{{ request('sub_status', '') }}',
            
            // New filter state
            categories: @json($categories),
            selectedCategoryId: '{{ request('service_category_id') }}',
            selectedServiceIds: @json(request('service_ids', [])),

            get filteredServices() {
                if (!this.selectedCategoryId) return [];
                const cat = this.categories.find(c => c.id == this.selectedCategoryId);
                return cat ? cat.children : [];
            },

            async openAcceptModal(orderId) {
                this.selectedOrder = orderId;
                this.showAcceptModal = true;
                this.selectedTechId = null;
                this.selectedCompanyId = null;
                this.activeTab = 'platform';
                this.viewMode = 'list';
                await this.fetchTechnicians();
            },

            async toggleViewMode() {
                this.viewMode = this.viewMode === 'list' ? 'map' : 'list';
                if (this.viewMode === 'map') {
                    this.$nextTick(() => this.initMap());
                }
            },

            initMap() {
                if (!this.map) {
                    this.map = L.map('tech-map', {
                        zoomControl: false,
                        attributionControl: false
                    }).setView([24.7136, 46.6753], 12);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
                }
                
                this.renderMarkers();
            },

            renderMarkers() {
                if (!this.map) return;
                this.markers.forEach(m => this.map.removeLayer(m));
                this.markers = [];

                let data = this.activeTab === 'platform' ? this.technicians : this.companies;
                
                if (this.searchMap.trim()) {
                    const query = this.searchMap.toLowerCase();
                    data = data.filter(item => 
                        item.name.toLowerCase().includes(query) || 
                        (item.specialty && item.specialty.toLowerCase().includes(query)) ||
                        (item.district && item.district.toLowerCase().includes(query))
                    );
                }

                data.forEach(item => {
                    if (item.lat && item.lng) {
                        const marker = L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div class="group relative flex items-center justify-center">
                                     <div class="w-10 h-10 rounded-full bg-[#007055] border-[3px] border-white flex items-center justify-center shadow-2xl transition-all duration-300 group-hover:scale-110">
                                       <div class="w-4 h-4 rounded-full border-2 border-white/50"></div>
                                     </div>
                                     <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-3 h-3 bg-[#007055] rotate-45 border-b border-r border-white/20"></div>
                                   </div>`,
                            iconSize: [40, 40],
                            iconAnchor: [20, 40]
                        });

                        const m = L.marker([item.lat, item.lng], { icon: marker })
                            .addTo(this.map)
                            .on('click', () => {
                                this.selectedMapTech = item;
                                this.map.panTo([item.lat, item.lng]);
                            });
                        
                        this.markers.push(m);
                    }
                });

                if (this.markers.length > 0) {
                    const group = new L.featureGroup(this.markers);
                    this.map.fitBounds(group.getBounds().pad(0.3));
                }
            },

            async fetchTechnicians() {
                this.loadingTechs = true;
                this.technicians = [];
                try {
                    const response = await fetch(`/admin/orders/${this.selectedOrder}/available-technicians`);
                    const result = await response.json();
                    if (result.status) {
                        this.technicians = result.data;
                        if (this.viewMode === 'map') this.renderMarkers();
                    }
                } catch (error) {
                    console.error('Error fetching technicians:', error);
                } finally {
                    this.loadingTechs = false;
                }
            },

            async fetchCompanies() {
                this.loadingCompanies = true;
                this.companies = [];
                try {
                    const response = await fetch(`/admin/orders/${this.selectedOrder}/available-companies`);
                    const result = await response.json();
                    if (result.status) {
                        this.companies = result.data;
                        if (this.viewMode === 'map') this.renderMarkers();
                    }
                } catch (error) {
                    console.error('Error fetching companies:', error);
                } finally {
                    this.loadingCompanies = false;
                }
            },

            openRefuseModal(orderId) {
                this.selectedOrder = orderId;
                this.showRefuseModal = true;
                this.rejectionReason = '';
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        // Sparklines Simulation
        @foreach($cardStats as $index => $stat)
        new Chart(document.getElementById('chart-{{ $index }}').getContext('2d'), {
            type: 'line',
            data: {
                labels: Array(10).fill(''),
                datasets: [{
                    data: [{{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}],
                    borderColor: '{{ $stat["color"] }}',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: '{{ $stat["color"] }}10',
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
        @endforeach
    });
</script>
@endsection