@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        #tech-map { height: 500px; width: 100%; border-radius: 2rem; }
    </style>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderManagementShow', () => ({
                activeTab: '{{ ($item->technician || $item->maintenanceCompany) ? 'technician' : 'order' }}',
                showAcceptModal: false,
                showRefuseModal: false,
                selectedOrder: {{ $item->id }},
                viewMode: 'list',
                map: null,
                markers: [],
                selectedMapTech: null,
                searchMap: '',
                technicians: [],
                companies: [],
                loadingTechs: false,
                loadingCompanies: false,
                modalTab: 'platform',
                selectedTechId: null,
                selectedCompanyId: null,
                rejectionReason: '',

                async openAcceptModal() {
                    this.showAcceptModal = true;
                    this.selectedTechId = null;
                    this.selectedCompanyId = null;
                    this.modalTab = 'platform';
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

                    let data = this.modalTab === 'platform' ? this.technicians : this.companies;
                    if (this.searchMap.trim()) {
                        const query = this.searchMap.toLowerCase();
                        data = data.filter(item => 
                            item.name.toLowerCase().includes(query) || 
                            (item.specialty && item.specialty.toLowerCase().includes(query)) ||
                            (item.service_name && item.service_name.toLowerCase().includes(query))
                        );
                    }

                    data.forEach(item => {
                        if (item.lat && item.lng) {
                            const marker = L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div class="w-10 h-10 rounded-full bg-[#1A1A31] border-[3px] border-white flex items-center justify-center shadow-2xl">
                                         <div class="w-4 h-4 rounded-full border-2 border-white/50"></div>
                                       </div>`,
                                iconSize: [40, 40],
                                iconAnchor: [20, 40]
                            });

                            const m = L.marker([item.lat, item.lng], { icon: marker })
                                .addTo(this.map)
                                .on('click', () => {
                                    this.selectedMapTech = item;
                                    this.selectedMapTech.type = this.modalTab === 'platform' ? 'technician' : 'company';
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
                    try {
                        const response = await fetch(`/admin/orders/${this.selectedOrder}/available-technicians`);
                        const result = await response.json();
                        if (result.status) {
                            this.technicians = result.data;
                            if (this.viewMode === 'map') this.renderMarkers();
                        }
                    } catch (error) { console.error(error); } finally { this.loadingTechs = false; }
                },

                async fetchCompanies() {
                    this.loadingCompanies = true;
                    try {
                        const response = await fetch(`/admin/orders/${this.selectedOrder}/available-companies`);
                        const result = await response.json();
                        if (result.status) {
                            this.companies = result.data;
                            if (this.viewMode === 'map') this.renderMarkers();
                        }
                    } catch (error) { console.error(error); } finally { this.loadingCompanies = false; }
                }
            }));
        });
    </script>
@endsection

@section('title', __('Order Details') . ' #' . $item->id)

@section('content')
<div class="space-y-8" x-data="orderManagementShow()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-100 dark:border-white/10 text-[#1A1A31] dark:text-white hover:bg-slate-50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app()->getLocale() == 'ar' ? 'M14 5l7 7m0 0l-7 7m7-7H3' : 'M10 19l-7-7m0 0l7-7m-7 7h18' }}"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Order') }} - #{{ $item->id }}</h1>
        </div>

        <div class="flex items-center gap-3">
            @if($item->status == 'new')
                <button @click="showRefuseModal = true" class="px-6 py-2 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs hover:bg-slate-200 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    {{ __('Refuse Order') }}
                </button>
                <button @click="openAcceptModal()" class="px-8 py-2 rounded-xl bg-[#1A1A31] text-white font-bold text-xs shadow-lg shadow-[#1A1A31]/20 hover:scale-[1.02] transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('Accept Order') }}
                </button>
            @endif
        </div>
    </div>

    <!-- Tabs switcher -->
    <div class="flex items-center justify-center">
        <div class="flex p-1.5 bg-white dark:bg-[#1A1A31] rounded-2xl border border-slate-100 dark:border-white/5">
            <button @click="activeTab = 'order'" 
                    :class="activeTab === 'order' ? 'bg-[#1A1A31] text-white' : 'text-slate-400'"
                    class="px-8 py-3 rounded-xl font-bold text-sm transition-all">
                {{ __('Order Data') }}
            </button>
            <button @click="activeTab = 'customer'" 
                    :class="activeTab === 'customer' ? 'bg-[#1A1A31] text-white' : 'text-slate-400'"
                    class="px-8 py-3 rounded-xl font-bold text-sm transition-all">
                {{ __('Customer Data') }}
            </button>
            @if($item->technician || $item->maintenanceCompany)
            <button @click="activeTab = 'technician'" 
                    :class="activeTab === 'technician' ? 'bg-[#1A1A31] text-white' : 'text-slate-400'"
                    class="px-8 py-3 rounded-xl font-bold text-sm transition-all">
                {{ __('Technician Data') }}
            </button>
            @endif
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Order Data Tab -->
        <div x-show="activeTab === 'order'" class="space-y-6">
            <!-- Details Card -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Order Details') }}</h3>
@php
    $profileRoute = in_array($item->user->type, ['individual', 'client']) 
        ? 'admin.individual-customers.show' 
        : 'admin.corporate-customers.show';
@endphp
                    <!-- <a href="{{ route($profileRoute, $item->user_id) }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        {{ __('View Customer Profile') }}
                    </a> -->
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                    <!-- Customer Name -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Customer Name') }}: <span class="text-slate-400 font-bold ml-1">{{ $item->user->name }}</span></span>
                        </div>
                    </div>

                    <!-- Service -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Service') }}: <span class="text-slate-400 font-bold ml-1">{{ $item->service->name_ar }} ({{ $item->service->parent->name_ar ?? '' }})</span></span>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Address') }}: <span class="text-slate-400 font-bold ml-1">{{ __('Address') }}</span></span>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0 font-black text-xs">
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Price') }}: <span class="text-slate-400 font-bold ml-1">{{ $item->total_price ?? '-' }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span></span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Payment Method') }}: <span class="text-slate-400 font-bold ml-1">{{ $item->payment_method ?? __('Payment Method') }}</span></span>
                        </div>
                    </div>

                    <!-- Assigned Technician/Company -->
                    @if($item->technician || $item->maintenanceCompany)
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">
                                {{ $item->technician ? __('Technician') : __('Maintenance Company') }}: 
                                <a href="#" @click.prevent="activeTab = 'technician'" class="text-primary font-bold ml-1 hover:underline">
                                    {{ $item->technician ? (optional($item->technician->user)->name ?? '-') : (optional($item->maintenanceCompany->user)->name ?? '-') }}
                                </a>
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Status -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Status') }}: 
                                <span class="px-3 py-1 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300 text-[10px] font-bold ml-2">
                                    {{ __(ucfirst(str_replace('_', ' ', $item->status))) }}
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Date') }}: <span class="text-slate-400 font-bold ml-1">{{ $item->created_at->format('j/n/2025') }}</span></span>
                        </div>
                    </div>

                    <!-- Time -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Time') }}: <span class="text-slate-400 font-bold ml-1">{{ $item->created_at->format('g:i A') }}</span></span>
                        </div>
                    </div>
                </div>


                <!-- Rejection Reason -->
                @if($item->status == 'rejected' && $item->rejection_reason)
                <div class="mt-12 p-8 rounded-[2rem] bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20">
                    <h4 class="text-md font-black text-red-600 mb-2">{{ __('Rejection Reason') }}</h4>
                    <p class="text-sm font-bold text-red-500/80 leading-relaxed">{{ $item->rejection_reason }}</p>
                </div>
                @endif
            </div>

            <!-- Problem Overview Card -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-6">{{ __('Problem Overview') }}</h3>
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/5">
                    <p class="text-xs font-bold text-slate-400 leading-relaxed">{{ $item->description ?? __('No description provided') }}</p>
                </div>
            </div>

            <!-- Attachments Card -->
            @if($item->attachments && $item->attachments->count() > 0)
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-8">{{ __('Attachments') }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4">
                    @foreach($item->attachments as $attachment)
                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="aspect-square rounded-2xl overflow-hidden border border-slate-100 dark:border-white/10 group relative">
                            <img src="{{ Storage::url($attachment->file_path) }}" alt="Attachment" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($item->status == 'completed')
            
            <!-- Work Documentation -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-6">{{ __('Work Documentation') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Before -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold text-slate-400">{{ __('Before Start') }}</h4>
                        <div class="grid grid-cols-3 gap-3">
                            @forelse($item->attachments->where('type', 'before') as $photo)
                                <a href="{{ Storage::url($photo->file_path) }}" target="_blank" class="aspect-square rounded-xl overflow-hidden border border-slate-100 dark:border-white/10 relative group">
                                    <img src="{{ Storage::url($photo->file_path) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </a>
                            @empty
                                <div class="col-span-3 py-8 text-center text-xs text-slate-300 border-2 border-dashed border-slate-100 dark:border-white/10 rounded-xl">
                                    {{ __('No photos recorded') }}
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- After -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold text-slate-400">{{ __('After Completion') }}</h4>
                        <div class="grid grid-cols-3 gap-3">
                            @forelse($item->attachments->where('type', 'after') as $photo)
                                <a href="{{ Storage::url($photo->file_path) }}" target="_blank" class="aspect-square rounded-xl overflow-hidden border border-slate-100 dark:border-white/10 relative group">
                                    <img src="{{ Storage::url($photo->file_path) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </a>
                            @empty
                                <div class="col-span-3 py-8 text-center text-xs text-slate-300 border-2 border-dashed border-slate-100 dark:border-white/10 rounded-xl">
                                    {{ __('No photos recorded') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spare Parts -->
            @if(!empty($item->spare_parts_metadata))
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    {{ __('Used Spare Parts') }}
                </h3>
                <div class="space-y-4">
                    @foreach($item->spare_parts_metadata as $part)
                    <!-- Single Part Card -->
                    <div class="flex flex-col md:flex-row items-center justify-between p-6 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                        
                        <!-- Part Info -->
                        <div class="flex items-center gap-4 w-full md:w-auto mb-4 md:mb-0">
                            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                            </div>
                            <div class="flex-1">
                                <h5 class="text-lg font-black text-[#1A1A31] dark:text-white mb-1">{{ $part['name'] }}</h5>
                            </div>
                        </div>
                        
                        <!-- Price Details -->
                        <div class="flex flex-wrap items-center gap-4 md:gap-8 w-full md:w-auto">
                            <div class="flex flex-col items-center p-3 rounded-xl bg-white dark:bg-white/5 min-w-[3rem]">
                                <span class="text-xs text-slate-400 font-bold mb-1">{{ __('Qty') }}</span>
                                <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ $part['qty'] }}</span>
                            </div>
                            <div class="flex flex-col items-center p-3 rounded-xl bg-white dark:bg-white/5 min-w-[3rem]">
                                <span class="text-xs text-slate-400 font-bold mb-1">{{ __('Price') }}</span>
                                <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ $part['price'] }}</span>
                            </div>
                            <div class="flex flex-col items-end pl-4 border-l-2 border-slate-100 dark:border-white/10 ml-auto md:ml-0">
                                <span class="text-xs text-slate-400 font-bold mb-1">{{ __('Total') }}</span>
                                <span class="text-lg font-black text-primary">{{ $part['total'] }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span>
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Client Signature -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    {{ __('Client Signature') }}
                </h3>
                <div class="h-48 rounded-2xl bg-slate-50 dark:bg-white/5 border-2 border-dashed border-slate-100 dark:border-white/10 flex items-center justify-center overflow-hidden relative">
                    @if($item->client_signature)
                        @if(Str::startsWith($item->client_signature, 'data:image'))
                            <img src="{{ $item->client_signature }}" alt="Signature" class="max-h-full max-w-full object-contain">
                        @else
                            <img src="{{ Storage::url($item->client_signature) }}" alt="Signature" class="max-h-full max-w-full object-contain">
                        @endif
                    @else
                        <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">{{ __('Not Signed') }}</span>
                    @endif
                </div>
            </div>

            <!-- Rating -->
            @php
                $review = $item->reviews->first();
            @endphp
            @if($review)
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    {{ __('Client Rating') }}
                </h3>
                <div class="p-8 rounded-2xl bg-slate-50 dark:bg-white/5">
                    <div class="flex flex-col items-center justify-center text-center gap-4">
                        <span class="text-5xl font-black text-[#1A1A31] dark:text-white">{{ number_format($review->rating, 1) }}</span>
                        <div class="flex gap-1">
                            @for($i=1; $i<=5; $i++)
                                <svg class="w-6 h-6 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-slate-200 dark:text-white/10' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endfor
                        </div>
                        <p class="text-lg font-bold text-slate-400 italic max-w-2xl mt-2">"{{ $review->comment ?? __('No comment') }}"</p>
                    </div>
                </div>
            </div>
            @endif
            @endif

        </div>

        <!-- Customer Data Tab -->
        <div x-show="activeTab === 'customer'" class="space-y-6" x-cloak>
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <div class="relative mb-12">
                    <!-- Customer Header Card -->
                <div class="p-12 relative overflow-hidden bg-[#1A1A31] rounded-[3rem] text-white">
                    <!-- Decor -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-32 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-primary/10 rounded-full translate-y-32 -translate-x-32 blur-3xl"></div>

                    <div class="flex flex-col md:flex-row items-center md:items-end gap-8 relative">
                        <div class="w-32 h-32 rounded-3xl overflow-hidden border-4 border-white/10 shadow-2xl shrink-0">
                            @if($item->user->avatar)
                                <img src="{{ Storage::url($item->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-white/10 text-white text-5xl font-black">
                                    {{ mb_substr($item->user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 text-center md:text-right pb-2 space-y-3">
                            <div class="space-y-1">
                                <h2 class="text-3xl font-black mb-1">{{ $item->user->name }}</h2>
                                <span class="px-4 py-1 rounded-xl bg-white/10 backdrop-blur-md text-white/60 text-[10px] font-black uppercase tracking-widest">
                                    {{ in_array($item->user->type, ['individual', 'client']) ? __('Individual') : __('Corporate') }}
                                </span>
                            </div>
                        </div>
                        <div class="pb-2">
                            @php
                                $route = in_array($item->user->type, ['individual', 'client']) 
                                    ? 'admin.individual-customers.show' 
                                    : 'admin.corporate-customers.show';
                            @endphp
                            <a href="{{ route($route, $item->user_id) }}" class="px-8 py-3 rounded-2xl bg-white/10 text-black text-xs font-black shadow-xl shadow-white/5 hover:scale-105 transition-all uppercase tracking-widest">
                                {{ __('View Customer Profile') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12 pt-8">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Name') }}: <span class="text-slate-400 ml-1">{{ $item->user->name }}</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Customer Type') }}: <span class="text-slate-400 ml-1">{{ $item->user->type == 'client' ? __('Individual') : __('Corporate') }}</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg></div>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Phone') }}: <span class="text-slate-400 ml-1">{{ $item->user->phone ?? '-' }}</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Email') }}: <span class="text-slate-400 ml-1">{{ $item->user->email ?? '-' }}</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></div>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Address') }}: <span class="text-slate-400 ml-1">{{ __('Address') }}</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg></div>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Orders Count') }}: <span class="text-slate-400 ml-1">{{ $item->user->orders()->count() }} {{ __('Orders') }}</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Status') }}: <span class="px-3 py-1 rounded-lg bg-green-50 text-green-600 dark:bg-green-900/10 dark:text-green-400 text-[10px] font-bold ml-2">{{ __('Active') }}</span></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Date') }}: <span class="text-slate-400 ml-1">{{ $item->user->created_at->format('j/n/Y') }}</span></span>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Technician Data Tab -->
        @if($item->technician || $item->maintenanceCompany)
        
        <div x-show="activeTab === 'technician'" class="space-y-6">
        
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <div class="relative mb-12">
                    <!-- Technician Header Card -->
                    <div class="p-12 relative overflow-hidden bg-[#1A1A31] rounded-[3rem] text-white">
                        <!-- Decor -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-32 blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-64 h-64 bg-primary/10 rounded-full translate-y-32 -translate-x-32 blur-3xl"></div>

                        <div class="flex flex-col md:flex-row items-center md:items-end gap-8 relative">
                            <div class="w-32 h-32 rounded-3xl overflow-hidden border-4 border-white/10 shadow-2xl shrink-0">
                                @if($item->technician && (optional($item->technician->user)->avatar || $item->technician->image))
                                    @php
                                        $avatarUrl = $item->technician->user->avatar ?? $item->technician->image;
                                    @endphp
                                    <img src="{{ Storage::url($avatarUrl) }}" alt="Avatar" class="w-full h-full object-cover">
                                @elseif($item->maintenanceCompany && optional($item->maintenanceCompany->user)->avatar)
                                    <img src="{{ Storage::url($item->maintenanceCompany->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-white/10 text-white text-5xl font-black">
                                        {{ mb_substr($item->technician ? (optional(optional($item->technician)->user)->name ?? $item->technician->name ?? '-') : (optional(optional($item->maintenanceCompany)->user)->name ?? $item->maintenanceCompany->name ?? '-'), 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 text-center md:text-right pb-2 space-y-3">
                                <div class="space-y-1">
                                    <h2 class="text-3xl font-black mb-1">{{ $item->technician ? (optional($item->technician->user)->name ?? $item->technician->name ?? __('Unknown Technician')) : (optional($item->maintenanceCompany->user)->name ?? $item->maintenanceCompany->name ?? __('Unknown Company')) }}</h2>
                                    <span class="px-4 py-1 rounded-xl bg-white/10 backdrop-blur-md text-white/60 text-[10px] font-black uppercase tracking-widest">
                                        {{ $item->technician ? __('Platform Technician') : __('Maintenance Company') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12 pt-8">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Name') }}: <span class="text-slate-400 ml-1">{{ $item->technician ? (optional($item->technician->user)->name ?? $item->technician->name ?? '-') : (optional($item->maintenanceCompany->user)->name ?? $item->maintenanceCompany->name ?? '-') }}</span></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg></div>
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Phone') }}: <span class="text-slate-400 ml-1">{{ $item->technician ? (optional($item->technician->user)->phone ?? '-') : (optional($item->maintenanceCompany->user)->phone ?? '-') }}</span></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Email') }}: <span class="text-slate-400 ml-1">{{ $item->technician ? (optional($item->technician->user)->email ?? '-') : (optional($item->maintenanceCompany->user)->email ?? '-') }}</span></span>
                        </div>
                        @if($item->technician && $item->technician->category)
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Specialty') }}: <span class="text-slate-400 ml-1">{{ optional($item->technician->category)->name_ar ?? '-' }}</span></span>
                        </div>
                        @else
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Specialty') }}: <span class="text-slate-400 ml-1">-</span></span>
                        </div>
                        @endif
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg></div>
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Rating') }}: <span class="text-slate-400 ml-1">{{ $item->technician ? number_format($item->technician->rating ?? 0, 1) : number_format($item->maintenanceCompany->rating ?? 0, 1) }} ⭐</span></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg></div>
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Completed Orders') }}: <span class="text-slate-400 ml-1">10 {{ __('Orders') }}</span></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                            <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Status') }}: <span class="px-3 py-1 rounded-lg bg-green-50 text-green-600 dark:bg-green-900/10 dark:text-green-400 text-[10px] font-bold ml-2">{{ __('Available') }}</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @include('admin.orders.partials.modals')
</div>

@endsection