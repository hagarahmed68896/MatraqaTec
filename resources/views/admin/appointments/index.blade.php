@extends('layouts.admin')

@section('title', __('Appointments'))

@section('styles')
<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: 80px repeat(7, 1fr);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1.5rem;
        overflow: hidden;
    }
    .dark .calendar-grid {
        background: #1e1e38;
        border-color: rgba(255, 255, 255, 0.05);
    }
    .calendar-header {
        background: white;
        text-align: center;
        padding: 1rem 0.5rem;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
        border-left: 1px solid #e2e8f0;
    }
    .dark .calendar-header {
        background: #1A1A31;
        border-color: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
    }
    .calendar-time-cell {
        background: white;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
        color: #64748b;
    }
    .dark .calendar-time-cell {
        background: #1A1A31;
        border-color: rgba(255, 255, 255, 0.05);
        color: #64748b;
    }
    .calendar-slot {
        height: 100px;
        border-bottom: 1px solid #f1f5f9;
        border-left: 1px solid #f1f5f9;
        position: relative;
        background: white;
    }
    .dark .calendar-slot {
        background: #1A1A31;
        border-color: rgba(255, 255, 255, 0.05);
    }
    .calendar-slot:hover {
        background: #f8fafc;
    }
    .dark .calendar-slot:hover {
        background: rgba(255, 255, 255, 0.02);
    }
    .appointment-card {
        position: absolute;
        inset: 4px;
        border-radius: 1rem;
        padding: 0.75rem;
        font-size: 0.65rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 10;
        overflow: hidden;
    }
    .appointment-card:hover {
        transform: scale(1.02);
        z-index: 20;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
    }
</style>
@endsection

@section('content')
<div x-data="{ 
    selectedAppointment: null,
    showDetails: false,
    openPopover(app) {
        this.selectedAppointment = app;
        this.showDetails = true;
    }
}" class="space-y-8 pb-12">
    
    <!-- Header: Title & Stats -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ __('Appointments') }}</h1>
        </div>

        <!-- Filter Stats -->
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="flex items-center gap-3 px-5 py-2.5 rounded-2xl {{ (request('status', 'all') == 'all') ? 'bg-[#1A1A31] text-white' : 'bg-white dark:bg-white/5 text-[#1A1A31] dark:text-white/60' }} transition-all shadow-sm">
                <span class="text-xs font-black">{{ __('All') }}</span>
                <span class="w-5 h-5 rounded-lg {{ (request('status', 'all') == 'all') ? 'bg-white/20' : 'bg-slate-100 dark:bg-white/10' }} flex items-center justify-center text-[10px] font-black">{{ $stats['all'] }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'scheduled']) }}" class="flex items-center gap-3 px-5 py-2.5 rounded-2xl {{ (request('status') == 'scheduled') ? 'bg-[#1A1A31] text-white' : 'bg-white dark:bg-white/5 text-[#1A1A31] dark:text-white/60' }} transition-all shadow-sm">
                <span class="text-xs font-black">{{ __('Scheduled') }}</span>
                <span class="w-5 h-5 rounded-lg {{ (request('status') == 'scheduled') ? 'bg-white/20' : 'bg-slate-100 dark:bg-white/10' }} flex items-center justify-center text-[10px] font-black">{{ $stats['scheduled'] }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'in_progress']) }}" class="flex items-center gap-3 px-5 py-2.5 rounded-2xl {{ (request('status') == 'in_progress') ? 'bg-[#1A1A31] text-white' : 'bg-white dark:bg-white/5 text-[#1A1A31] dark:text-white/60' }} transition-all shadow-sm">
                <span class="text-xs font-black">{{ __('In Progress') }}</span>
                <span class="w-5 h-5 rounded-lg {{ (request('status') == 'in_progress') ? 'bg-white/20' : 'bg-slate-100 dark:bg-white/10' }} flex items-center justify-center text-[10px] font-black">{{ $stats['in_progress'] }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}" class="flex items-center gap-3 px-5 py-2.5 rounded-2xl {{ (request('status') == 'completed') ? 'bg-[#1A1A31] text-white' : 'bg-white dark:bg-white/5 text-[#1A1A31] dark:text-white/60' }} transition-all shadow-sm">
                <span class="text-xs font-black">{{ __('Completed') }}</span>
                <span class="w-5 h-5 rounded-lg {{ (request('status') == 'completed') ? 'bg-white/20' : 'bg-slate-100 dark:bg-white/10' }} flex items-center justify-center text-[10px] font-black">{{ $stats['completed'] }}</span>
            </a>
        </div>
    </div>

    <!-- Week Selector -->
    <div class="flex items-center gap-4 bg-white dark:bg-white/5 p-4 rounded-3xl w-fit shadow-sm border border-slate-100 dark:border-white/5">
        <a href="{{ request()->fullUrlWithQuery(['start_date' => $startDate->copy()->subWeek()->toDateString()]) }}" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all text-slate-400">
            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex items-center gap-3 px-4 py-2 bg-slate-50 dark:bg-white/10 rounded-2xl">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path></svg>
            <span class="text-sm font-black text-[#1A1A31] dark:text-white">
                {{ $startDate->translatedFormat('j M Y') }} - {{ $endDate->translatedFormat('j M Y') }}
            </span>
        </div>
        <a href="{{ request()->fullUrlWithQuery(['start_date' => $startDate->copy()->addWeek()->toDateString()]) }}" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all text-slate-400">
            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
    </div>

    <!-- Calendar Grid -->
    <div class="calendar-grid relative shadow-2xl shadow-slate-200/50 dark:shadow-none">
        
        <!-- Time Labels (Placeholder for first cell) -->
        <div class="calendar-header border-none"></div>

        <!-- Day Headers -->
        @php
            $days = [];
            for($i = 0; $i < 7; $i++) {
                $day = $startDate->copy()->addDays($i);
                $days[] = $day;
            }
        @endphp

        @foreach($days as $day)
            <div class="calendar-header flex flex-col items-center gap-1">
                <span class="opacity-50 text-[10px]">{{ $day->translatedFormat('l') }}</span>
                <span class="text-sm font-black">{{ $day->format('d') }}</span>
                
                @php
                    $dayCount = $items->filter(function($item) use ($day) {
                        return $item->appointment_date->toDateString() == $day->toDateString();
                    })->count();
                @endphp
                @if($dayCount > 0)
                    <span class="w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center text-[9px] font-black mt-2">{{ $dayCount }}</span>
                @endif
            </div>
        @endforeach

        <!-- Rows: 7 AM to 11 PM -->
        @for($hour = 7; $hour <= 23; $hour++)
            <!-- Time Column -->
            <div class="calendar-time-cell h-[100px]">
                {{ date('h A', strtotime("$hour:00")) }}
            </div>

            <!-- Day Slots -->
            @foreach($days as $day)
                @php
                    $slotApps = $items->filter(function($app) use ($day, $hour) {
                        return $app->appointment_date->toDateString() == $day->toDateString() 
                               && $app->appointment_date->hour == $hour;
                    });
                @endphp
                <div class="calendar-slot group">
                    @foreach($slotApps as $app)
                        @php
                            $colorClass = match($app->status) {
                                'completed' => 'bg-green-50 text-green-700 border-green-100 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20',
                                'in_progress' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                default => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20'
                            };
                            $statusColor = match($app->status) {
                                'completed' => 'bg-green-500',
                                'in_progress' => 'bg-amber-500',
                                default => 'bg-blue-500'
                            };
                        @endphp
                        <div @click="openPopover({{ json_encode([
                            'id' => $app->id,
                            'order_number' => $app->order->order_number ?? '---',
                            'tech_name' => $app->technician->user->name ?? ($app->technician->name ?? __('Unknown')),
                            'tech_avatar' => $app->technician->user->avatar ?? null,
                            'service_name' => $app->order->service->name_ar ?? ($app->order->service->name_en ?? '---'),
                            'customer_name' => $app->order->user->name ?? '---',
                            'customer_phone' => $app->order->user->phone ?? '---',
                            'date' => $app->appointment_date->format('d/m/Y'),
                            'time' => $app->appointment_date->format('H:i'),
                            'status' => $app->status,
                            'status_label' => $app->status_label,
                            'rating' => $app->order->reviews->avg('rating') ?? 0,
                            'details_url' => route('admin.orders.show', $app->order_id)
                        ]) }})" 
                             class="appointment-card border {{ $colorClass }} flex flex-col justify-between">
                            
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full overflow-hidden bg-slate-200 border-2 border-white dark:border-white/10 flex-shrink-0">
                                    @if($app->technician && $app->technician->user->avatar)
                                        <img src="{{ asset('storage/' . $app->technician->user->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-primary text-[8px] text-white">
                                            {{ mb_substr($app->technician->user->name ?? 'T', 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-black truncate leading-tight">{{ $app->technician->user->name ?? __('Unknown') }}</p>
                                    <p class="text-[8px] opacity-70 truncate">{{ $app->order->service->name_ar ?? '---' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-auto pt-2 border-t border-current/10">
                                <span class="text-[9px] font-black">#{{ $app->order->order_number ?? '---' }}</span>
                                <div class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusColor }}"></span>
                                    <span class="text-[8px] font-bold">{{ $app->status_label }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endfor
    </div>

    <!-- Details Modal / Popover -->
    <div x-show="showDetails" 
         x-cloak
         @keydown.escape.window="showDetails = false"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        
        <div @click.away="showDetails = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-[#1A1A31] w-full max-w-md rounded-[2.5rem] shadow-2xl border border-white/20 overflow-hidden text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
            
            <!-- Modal Header -->
            <div class="p-8 relative border-b border-slate-50 dark:border-white/5">
                <button @click="showDetails = false" class="absolute top-8 {{ app()->getLocale() == 'ar' ? 'left-8' : 'right-8' }} text-slate-400 hover:text-red-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                        <template x-if="selectedAppointment && selectedAppointment.tech_avatar">
                            <img :src="'/storage/' + selectedAppointment.tech_avatar" class="w-full h-full rounded-2xl object-cover">
                        </template>
                        <template x-if="!selectedAppointment || !selectedAppointment.tech_avatar">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-[#1A1A31] dark:text-white" x-text="selectedAppointment ? selectedAppointment.tech_name : ''"></h3>
                        <p class="text-xs text-slate-400 font-bold" x-text="selectedAppointment ? selectedAppointment.service_name : ''"></p>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-8 space-y-6">
                <!-- Data Grid -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <span class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">{{ __('Order Number') }}</span>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white" x-text="'#' + (selectedAppointment ? selectedAppointment.order_number : '')"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">{{ __('Customer') }}</span>
                        <span class="text-sm font-bold text-[#1A1A31] dark:text-white" x-text="selectedAppointment ? selectedAppointment.customer_name : ''"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">{{ __('Date') }}</span>
                        <div class="flex items-center gap-2 text-sm font-bold text-[#1A1A31] dark:text-white">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path></svg>
                            <span x-text="selectedAppointment ? selectedAppointment.date : ''"></span>
                        </div>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">{{ __('Time') }}</span>
                        <div class="flex items-center gap-2 text-sm font-bold text-[#1A1A31] dark:text-white">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span x-text="selectedAppointment ? selectedAppointment.time : ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Rating -->
                <template x-if="selectedAppointment && selectedAppointment.rating > 0">
                    <div class="flex items-center gap-2 p-4 bg-amber-50 dark:bg-amber-500/10 rounded-2xl border border-amber-100 dark:border-amber-500/20">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <span class="text-sm font-black text-amber-700 dark:text-amber-400" x-text="selectedAppointment.rating"></span>
                        <span class="text-xs text-amber-600/60 font-bold">{{ __('Review Rating') }}</span>
                    </div>
                </template>

                <a :href="selectedAppointment ? selectedAppointment.details_url : '#'" class="block w-full py-5 bg-[#1A1A31] text-white rounded-3xl text-center font-black text-sm shadow-xl shadow-[#1A1A31]/20 hover:scale-[1.02] transition-all">
                    {{ __('View Details') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection