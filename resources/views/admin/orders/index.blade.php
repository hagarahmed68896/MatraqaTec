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
        <h1 class="text-2xl font-black text-[#1A1A31]">{{ __('الطلبات الجديدة') }}</h1>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $cardStats = [
                ['label' => 'إجمالي الطلبات', 'value' => $stats['total'], 'color' => '#10B981', 'trend' => '8.43%', 'icon' => 'orders'],
                ['label' => 'الطلبات المقبولة', 'value' => $stats['scheduled'], 'color' => '#10B981', 'trend' => '0.43%', 'icon' => 'accepted'],
                ['label' => 'الطلبات المرفوضة', 'value' => $stats['rejected'], 'color' => '#EF4444', 'trend' => '8.43%', 'icon' => 'rejected'],
                ['label' => 'الطلبات قيد المراجعة', 'value' => $stats['new'], 'color' => '#64748B', 'trend' => '0.43%', 'icon' => 'review'],
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
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">مقارنة بالأسبوع الماضي</span>
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
                ['id' => 'new', 'label' => 'الطلبات الجديدة', 'count' => $stats['new']],
                ['id' => 'scheduled', 'label' => 'الطلبات المقبولة', 'count' => $stats['scheduled']],
                ['id' => 'rejected', 'label' => 'الطلبات المرفوضة', 'count' => $stats['rejected']],
            ];
            $currentTab = request('tab', 'new');
        @endphp

        @foreach($tabs as $tab)
        <a href="{{ route('admin.orders.index', ['tab' => $tab['id']]) }}" 
           class="flex items-center gap-3 px-8 py-3 rounded-2xl font-black text-md transition-all {{ $currentTab == $tab['id'] ? 'bg-[#1A1A31] text-white shadow-lg' : 'bg-white text-[#1A1A31] border border-slate-100 hover:bg-slate-50' }}">
            {{ __($tab['label']) }}
            <span class="w-6 h-6 rounded-lg {{ $currentTab == $tab['id'] ? 'bg-white/20 text-white' : 'bg-slate-100 text-[#1A1A31]' }} flex items-center justify-center text-[11px]">{{ $tab['count'] }}</span>
        </a>
        @endforeach
    </div>

    <!-- SEARCH & FILTERS -->
    <div class="flex items-center justify-center gap-6 relative">
        <div class="w-full max-w-2xl relative">
            <input type="text" name="search" form="filterForm" value="{{ request('search') }}" 
                   class="w-full h-16 pr-14 pl-6 bg-white border border-slate-50 rounded-[1.5rem] shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/5 transition-all font-bold text-md text-[#1A1A31]">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <!-- Filter Dropdown Trigger -->
        <button @click="showFilters = !showFilters" class="w-16 h-16 flex items-center justify-center bg-white rounded-2xl shadow-sm border border-slate-50 text-[#1A1A31] hover:bg-slate-50 transition-all relative">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
            <div x-show="showFilters" class="absolute top-0 right-0 w-3 h-3 bg-red-500 border-2 border-white rounded-full"></div>
        </button>

        <!-- Filter Dropdown Panel -->
        <div x-show="showFilters" @click.away="showFilters = false" x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="absolute top-20 left-0 mt-2 w-[400px] bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 z-[100] p-10 space-y-8 text-right overflow-y-auto max-h-[80vh]">
            
            <form id="filterForm" action="{{ route('admin.orders.index') }}" method="GET" class="space-y-8">
                <input type="hidden" name="tab" value="{{ request('tab') }}">
                
                <!-- Filter Section: Status -->
                <div class="space-y-4">
                    <h4 class="text-sm font-black text-[#1A1A31] opacity-60 flex items-center gap-2">
                        {{ __('الترتيب حسب') }}
                    </h4>
                    <div class="space-y-3">
                        @foreach(['newest' => 'الأول', 'oldest' => 'الأقدم', 'name' => 'الاسم'] as $val => $label)
                        <label class="flex items-center justify-between cursor-pointer group">
                             <span class="text-md font-bold text-[#1A1A31] group-hover:text-primary transition-colors">{{ $label }}</span>
                             <div class="relative w-6 h-6 border-2 {{ request('sort_by', 'newest') == $val ? 'border-primary bg-primary' : 'border-slate-200' }} rounded-full transition-all flex items-center justify-center">
                                 <input type="radio" name="sort_by" value="{{ $val }}" class="hidden" {{ request('sort_by', 'newest') == $val ? 'checked' : '' }}>
                                 @if(request('sort_by', 'newest') == $val)
                                 <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                 @endif
                             </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="h-px bg-slate-50"></div>

                <!-- Filter Section: Customer Type -->
                <div class="space-y-4">
                    <h4 class="text-sm font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('نوع العميل') }}</h4>
                    <div class="space-y-3">
                        @foreach(['' => 'الكل', 'client' => 'فرد', 'corporate' => 'شركة'] as $val => $label)
                        <label class="flex items-center justify-between cursor-pointer group">
                             <span class="text-md font-bold text-[#1A1A31] group-hover:text-primary transition-colors">{{ $label }}</span>
                             <div class="relative w-6 h-6 border-2 {{ request('customer_type') == $val ? 'border-primary bg-primary' : 'border-slate-200' }} rounded-full transition-all flex items-center justify-center">
                                 <input type="radio" name="customer_type" value="{{ $val }}" class="hidden" {{ request('customer_type') == $val ? 'checked' : '' }}>
                                 @if(request('customer_type') == $val)
                                 <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                 @endif
                             </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="h-px bg-slate-50"></div>

                <!-- Filter Section: Service Category -->
                @php $categories = \App\Models\Service::whereNull('parent_id')->get(); @endphp
                <div class="space-y-4">
                    <h4 class="text-sm font-black text-[#1A1A31] opacity-60 uppercase tracking-widest">{{ __('فئة الخدمة') }}</h4>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between cursor-pointer group">
                             <span class="text-md font-bold text-[#1A1A31] group-hover:text-primary transition-colors">الكل</span>
                             <div class="relative w-6 h-6 border-2 {{ !request('service_category_id') ? 'border-primary bg-primary' : 'border-slate-200' }} rounded-full transition-all flex items-center justify-center">
                                 <input type="radio" name="service_category_id" value="" class="hidden" {{ !request('service_category_id') ? 'checked' : '' }}>
                                 @if(!request('service_category_id'))
                                 <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                 @endif
                             </div>
                        </label>
                        @foreach($categories as $cat)
                        <label class="flex items-center justify-between cursor-pointer group">
                             <span class="text-md font-bold text-[#1A1A31] group-hover:text-primary transition-colors">{{ $cat->name_ar }}</span>
                             <div class="relative w-6 h-6 border-2 {{ request('service_category_id') == $cat->id ? 'border-primary bg-primary' : 'border-slate-200' }} rounded-full transition-all flex items-center justify-center">
                                 <input type="radio" name="service_category_id" value="{{ $cat->id }}" class="hidden" {{ request('service_category_id') == $cat->id ? 'checked' : '' }}>
                                 @if(request('service_category_id') == $cat->id)
                                 <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                 @endif
                             </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" class="flex-1 py-4 bg-[#1A1A31] text-white rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/20 hover:scale-[1.02] transition-all">
                        {{ __('كشف') }}
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold text-sm text-center hover:bg-slate-200 transition-all">
                        {{ __('إعادة التعيين') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table Section -->
    <div class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="bg-slate-50/50 text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                        <th class="py-6 px-8 flex items-center gap-4">
                            <input type="checkbox" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-[#1A1A31] focus:ring-0">
                            <span>#</span>
                        </th>
                        <th class="py-6 px-4">{{ __('رقم الطلب') }}</th>
                        <th class="py-6 px-4">{{ __('اسم العميل') }}</th>
                        <th class="py-6 px-4">{{ __('نوع العميل') }}</th>
                        <th class="py-6 px-4">{{ __('اسم الخدمة') }}</th>
                        <th class="py-6 px-4">{{ __('نوع الخدمة') }}</th>
                        <th class="py-6 px-4 text-center">{{ __('العنوان') }}</th>
                        <th class="py-6 px-4">{{ __('التاريخ و الوقت') }}</th>
                        <th class="py-6 px-8 text-center">{{ __('الإجراءات') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50/50 transition-all group">
                        <td class="py-6 px-8 flex items-center gap-4">
                            <input type="checkbox" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-[#1A1A31] focus:ring-0">
                            <span class="text-xs font-mono opacity-40">{{ $loop->iteration }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-primary font-black text-sm">طلب - {{ $item->id }}#</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-md font-bold text-[#1A1A31]">{{ $item->user->name }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-4 py-1.5 rounded-xl bg-slate-50 text-[10px] font-bold text-slate-600">{{ $item->user->type == 'client' ? 'فرد' : 'شركة' }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-slate-500">{{ $item->service->name_ar }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-bold text-slate-500">{{ $item->service->parent->name_ar ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <span class="text-[11px] font-bold text-slate-400">{{ __('العنوان') }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-bold text-slate-400">{{ $item->created_at->format('j/n/2025 - g:i') }}</span>
                        </td>
                        <td class="py-6 px-8">
                            <div class="flex items-center justify-center gap-3">
                                <!-- Refuse Button (X) -->
                                <button type="button" @click="openRefuseModal({{ $item->id }})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <!-- Accept Button (Checkmark) -->
                                <button type="button" @click="openAcceptModal({{ $item->id }})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1A1A31] text-white shadow-lg shadow-[#1A1A31]/20 hover:scale-110 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-20 text-center text-slate-400 font-bold uppercase tracking-widest bg-white">
                            {{ __('لا توجد طلبات حالياً') }}
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
                            <h2 class="text-2xl font-black text-[#1A1A31]">{{ __('قبول الطلب') }}</h2>
                            <button @click="toggleViewMode()" class="px-6 py-2 rounded-xl border border-slate-100 font-bold text-xs text-[#1A1A31] hover:bg-slate-50 transition-all flex items-center gap-2">
                                <template x-if="viewMode === 'list'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ __('عرض الخريطة') }}
                                    </span>
                                </template>
                                <template x-if="viewMode === 'map'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                        {{ __('عرض القائمة') }}
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
                                        class="flex-1 py-3 rounded-xl font-bold text-sm transition-all whitespace-nowrap px-4">
                                    {{ __('تعيين فني من المنصة') }}
                                </button>
                                <button @click="activeTab = 'company'; fetchCompanies()" 
                                        :class="activeTab === 'company' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-400'"
                                        class="flex-1 py-3 rounded-xl font-bold text-sm transition-all whitespace-nowrap px-4">
                                    {{ __('إرسال الطلب لشركة صيانة') }}
                                </button>
                            </div>

                            <p class="text-slate-400 font-bold text-sm leading-relaxed" x-text="activeTab === 'platform' ? '{{ __('اختر الفني المتاح لتنفيذ طلب الصيانة') }}' : '{{ __('اختر شركة الصيانة المتاحة لتنفيذ الطلب') }}'"></p>

                            <!-- List Section -->
                            <div class="space-y-6 text-right">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-black text-[#1A1A31]" x-text="activeTab === 'platform' ? '{{ __('الفنيين') }}' : '{{ __('شركات الصيانة') }}'"></h3>
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
                                                                    <span x-text="`التقييم: ${tech.rating}`"></span>
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
                                                                    <span x-text="`التقييم: ${comp.rating}`"></span>
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
                                            {{ __('لا يوجد نتائج متاحـة حالياً لهـذا الطلب') }}
                                        </div>
                                    </template>
                                </div>

                                <a href="#" class="block text-center text-xs font-bold text-slate-300 hover:text-[#1A1A31] transition-colors pt-2">{{ __('عرض المزيد') }}</a>
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
                                        <input type="text" x-model="searchMap" @input="renderMarkers()" placeholder="{{ __('بحث...') }}" class="w-full h-12 pr-12 pl-4 bg-white/90 backdrop-blur-md border border-slate-100 rounded-2xl shadow-lg focus:outline-none font-bold text-sm">
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex-1 flex justify-end pointer-events-auto gap-3">
                                    <button @click="activeTab = 'platform'; fetchTechnicians()" 
                                            :class="activeTab === 'platform' ? 'bg-[#1A1A31] text-white' : 'bg-white/90 text-slate-400'"
                                            class="px-6 py-3 rounded-2xl font-black text-xs shadow-lg backdrop-blur-md transition-all">
                                        {{ __('تعيين فني من المنصة') }}
                                    </button>
                                    <button @click="activeTab = 'company'; fetchCompanies()" 
                                            :class="activeTab === 'company' ? 'bg-[#1A1A31] text-white' : 'bg-white/90 text-slate-400'"
                                            class="px-6 py-3 rounded-2xl font-black text-xs shadow-lg backdrop-blur-md transition-all">
                                        {{ __('إرسال الطلب لشركة صيانة') }}
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
                                                {{ __('نوع الخدمة:') }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span class="text-[#1A1A31]" x-text="selectedMapTech.district"></span>
                                            <span class="text-slate-400 flex items-center gap-2">
                                                {{ __('المناطق:') }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span class="text-[#1A1A31]" x-text="`${selectedMapTech.order_count} طلبات`"></span>
                                            <span class="text-slate-400 flex items-center gap-2">
                                                {{ __('عدد الطلبات:') }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                <span class="text-[#1A1A31]" x-text="selectedMapTech.rating"></span>
                                            </div>
                                            <span class="text-slate-400 flex items-center gap-2">
                                                {{ __('متوسط التقييم:') }}
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
                                            {{ __('تعيين فني') }}
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
                                        class="w-full py-5 text-white rounded-[1.5rem] font-black text-sm transition-all transform uppercase tracking-widest">
                                    <span x-text="activeTab === 'platform' ? '{{ __('إرسال التعيين للفني') }}' : '{{ __('إرسال الطلب للشركة') }}'"></span>
                                </button>
                            </form>
                            <button @click="showAcceptModal = false" class="flex-[0.5] py-5 bg-slate-100 text-slate-400 rounded-[1.5rem] font-bold text-sm hover:bg-slate-200 transition-all">
                                {{ __('إلغاء') }}
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
                            <h2 class="text-2xl font-black text-[#1A1A31]">{{ __('رفض الطلب') }}</h2>
                            <p class="text-slate-400 font-bold text-sm">{{ __('يرجى ذكر سبب الرفض لتوضيحه للعميل') }}</p>
                        </div>

                        <form :action="`/admin/orders/${selectedOrder}/refuse`" method="POST" class="space-y-8">
                            @csrf
                            <textarea name="rejection_reason" x-model="rejectionReason" required
                                      placeholder="{{ __('اكتب سبب الرفض هنا...') }}"
                                      class="w-full h-40 p-6 bg-slate-50 border-none rounded-[2rem] focus:ring-2 focus:ring-red-500/20 transition-all font-bold text-md text-[#1A1A31] resize-none"></textarea>

                            <div class="flex gap-4">
                                <button type="submit" 
                                        :disabled="!rejectionReason.trim()"
                                        :class="rejectionReason.trim() ? 'bg-red-500 shadow-xl shadow-red-500/20 hover:scale-[1.02]' : 'bg-slate-300 cursor-not-allowed'"
                                        class="flex-1 py-5 text-white rounded-[1.5rem] font-black text-sm transition-all transform capitalize tracking-widest">
                                    {{ __('تأكيد الرفض') }}
                                </button>
                                <button type="button" @click="showRefuseModal = false" class="flex-[0.5] py-5 bg-slate-100 text-slate-400 rounded-[1.5rem] font-bold text-sm hover:bg-slate-200 transition-all">
                                    {{ __('إلغاء') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div> <!-- Outer Wrapper -->
    </div> <!-- Main Alpine Container -->

    <!-- Pagination & Footer -->
    <div class="px-10 py-6 border-t border-slate-50">
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