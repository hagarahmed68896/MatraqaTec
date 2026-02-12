@extends('layouts.admin')

@section('title', __('Order Details') . ' #' . $item->id)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Order Details') }} #{{ $item->id }}</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-bold mt-1">{{ __('Created') }} {{ $item->created_at->diffForHumans() }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($item->status == 'new')
                <div x-data="{ openAccept: false, openRefuse: false }">
                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <button @click="openAccept = true" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-primary/20">
                            {{ __('Accept & Assign') }}
                        </button>
                        <button @click="openRefuse = true" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-red-500/20">
                            {{ __('Refuse') }}
                        </button>
                    </div>

                    <!-- Accept Modal -->
                    <div x-show="openAccept" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                        <div @click.away="openAccept = false" class="bg-white dark:bg-[#1A1A31] w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-slate-100 dark:border-white/10">
                            <div class="p-6 border-b border-slate-100 dark:border-white/5">
                                <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Accept Order') }}</h3>
                            </div>
                            <form action="{{ route('admin.orders.accept', $item->id) }}" method="POST" class="p-6 space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Assign Technician') }}</label>
                                    <select name="technician_id" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-primary text-slate-800 dark:text-white text-sm font-bold">
                                        <option value="">{{ __('Select Technician') }}</option>
                                        @foreach($technicians as $tech)
                                            <option value="{{ $tech->id }}">{{ $tech->user->name }} ({{ $tech->specialization ?? __('Technician') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="text-center text-xs font-bold text-slate-400">{{ __('OR') }}</div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Assign Maintenance Company') }}</label>
                                    <select name="maintenance_company_id" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-primary text-slate-800 dark:text-white text-sm font-bold">
                                        <option value="">{{ __('Select Company') }}</option>
                                        @foreach($companies as $comp)
                                            <option value="{{ $comp->id }}">{{ $comp->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Scheduled Date') }}</label>
                                    <input type="datetime-local" name="scheduled_at" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-primary text-slate-800 dark:text-white text-sm font-bold">
                                </div>
                                <div class="flex justify-end gap-3 pt-4">
                                    <button type="button" @click="openAccept = false" class="px-4 py-2 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-xl transition-all">{{ __('Cancel') }}</button>
                                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-primary/20">{{ __('Confirm Accept') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Refuse Modal -->
                    <div x-show="openRefuse" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                        <div @click.away="openRefuse = false" class="bg-white dark:bg-[#1A1A31] w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-slate-100 dark:border-white/10">
                            <div class="p-6 border-b border-slate-100 dark:border-white/5">
                                <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('Refuse Order') }}</h3>
                            </div>
                            <form action="{{ route('admin.orders.refuse', $item->id) }}" method="POST" class="p-6 space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase">{{ __('Rejection Reason') }}</label>
                                    <textarea name="rejection_reason" rows="4" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-white/5 border-none focus:ring-2 focus:ring-red-500 text-slate-800 dark:text-white text-sm font-bold" placeholder="{{ __('Enter reason for rejection...') }}" required></textarea>
                                </div>
                                <div class="flex justify-end gap-3 pt-4">
                                    <button type="button" @click="openRefuse = false" class="px-4 py-2 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-xl transition-all">{{ __('Cancel') }}</button>
                                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-red-500/20">{{ __('Confirm Refuse') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details (Left/Right depending on locale) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Info Card -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm">
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-4">{{ __('Order Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Status') }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $item->status === 'new' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 
                               ($item->status === 'scheduled' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 
                               ($item->status === 'in_progress' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 
                               ($item->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'))) }}">
                            {{ __(ucfirst(str_replace('_', ' ', $item->status))) }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Total Price') }}</span>
                        <p class="text-base font-black text-slate-800 dark:text-white">{{ $item->total_price ? number_format($item->total_price, 2) . ' ' . __('SAR') : __('Not set') }}</p>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Service') }}</span>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $item->service->name ?? __('Unknown Service') }}</p>
                        @if($item->service->parent)
                            <p class="text-xs text-slate-400">{{ $item->service->parent->name }}</p>
                        @endif
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ __('Scheduled At') }}</span>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $item->scheduled_at ? $item->scheduled_at->format('Y-m-d H:i A') : __('Not scheduled') }}</p>
                    </div>
                </div>
                
                @if($item->description)
                    <div class="mt-6">
                        <span class="block text-xs font-bold text-slate-400 uppercase mb-2">{{ __('Description') }}</span>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ $item->description }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Locations Card -->
            @if($item->location_latitude && $item->location_longitude)
            <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm">
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-4">{{ __('Location') }}</h3>
                <div class="h-64 rounded-xl overflow-hidden bg-slate-100 dark:bg-white/5 flex items-center justify-center">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $item->location_latitude }},{{ $item->location_longitude }}" target="_blank" class="flex items-center gap-2 text-primary font-bold hover:underline">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ __('View on Google Maps') }}
                    </a>
                </div>
            </div>
            @endif

            <!-- Attachments -->
            @if($item->attachments && $item->attachments->count() > 0)
            <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm">
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-4">{{ __('Attachments') }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($item->attachments as $attachment)
                        <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="group relative aspect-square rounded-xl overflow-hidden bg-slate-100 dark:bg-white/5 border border-slate-100 dark:border-white/10">
                            <img src="{{ Storage::url($attachment->path) }}" alt="Attachment" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar (Right/Left) -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm">
                <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase mb-4">{{ __('Customer Details') }}</h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary text-lg font-black">
                        {{ mb_substr($item->user->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-white">{{ $item->user->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->user->phone }}</p>
                    </div>
                </div>
                <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-white/5">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Type') }}</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ __(ucfirst($item->user->type)) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Email') }}</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ $item->user->email ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Technician/Company Card -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm">
                <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase mb-4">{{ __('Assigned To') }}</h3>
                
                @if($item->technician)
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 text-lg font-black">
                            {{ mb_substr($item->technician->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 dark:text-white">{{ $item->technician->user->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Technician') }}</p>
                        </div>
                    </div>
                @elseif($item->maintenanceCompany)
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg font-black">
                            {{ mb_substr($item->maintenanceCompany->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 dark:text-white">{{ $item->maintenanceCompany->user->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Maintenance Company') }}</p>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/5 text-center text-slate-400 dark:text-slate-500 text-sm italic">
                        {{ __('Not assigned yet') }}
                    </div>
                @endif
            </div>

            <!-- Appointments Card (if exists) -->
            @if($item->appointments && $item->appointments->count() > 0)
            <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm">
                <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase mb-4">{{ __('Appointments') }}</h3>
                <div class="space-y-2">
                    @foreach($item->appointments as $appointment)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/5">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-xs font-bold text-slate-700 dark:text-white">{{ __('Date') }}</p>
                                <span class="text-xs text-slate-500">{{ $appointment->date->format('Y-m-d') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-xs font-bold text-slate-700 dark:text-white">{{ __('Time') }}</p>
                                <span class="text-xs text-slate-500">{{ $appointment->time_start }} - {{ $appointment->time_end }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Payments Card (if exists) -->
            @if($item->payments && $item->payments->count() > 0)
            <div class="bg-white dark:bg-[#1A1A31] rounded-2xl p-6 border border-slate-100 dark:border-white/5 shadow-sm">
                <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase mb-4">{{ __('Payments') }}</h3>
                <div class="space-y-2">
                    @foreach($item->payments as $payment)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/5 flex justify-between items-center">
                            <div>
                                <p class="text-xs font-bold text-slate-700 dark:text-white">{{ __('Payment') }} #{{ $payment->id }}</p>
                                <p class="text-[10px] text-slate-400">{{ $payment->created_at->format('Y-m-d') }}</p>
                            </div>
                            <span class="text-sm font-black text-green-600 dark:text-green-400">{{ number_format($payment->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection