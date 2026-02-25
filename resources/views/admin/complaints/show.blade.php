@extends('layouts.admin')

@section('title', __('Complaint Details') . ' - ' . __('MatraqaTec'))

@section('content')
<div x-data="{
    actionModal: false,
    actionType: '',
    actionLabel: '',
    openAction(type, label) {
        this.actionType = type;
        this.actionLabel = label;
        this.actionModal = true;
    }
}" class="space-y-8 min-h-screen pb-12" dir="rtl">

    {{-- ─── Header ─── --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.complaints.index') }}"
           class="w-12 h-12 bg-white dark:bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 hover:text-primary hover:bg-primary/5 transition-all shadow-sm border border-slate-100 dark:border-white/10 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1 class="text-[26px] font-black text-[#1A1A31] dark:text-white">
            {{ __('Ticket') }} - #{{ $item->ticket_number }}
        </h1>
    </div>

    {{-- ─── Main Details Card ─── --}}
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-sm border border-slate-100 dark:border-white/5 overflow-hidden">
        <div class="divide-y divide-slate-50 dark:divide-white/5">

            {{-- Row Macro: icon label | value --}}
            {{-- User Name --}}
            <div class="flex items-center justify-between px-8 py-5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                    <span class="text-sm font-black text-slate-400">{{ __('User Name') }}</span>
                </div>
                <span class="text-[15px] font-bold text-[#1A1A31] dark:text-white">{{ $item->user->name ?? '-' }}</span>
            </div>

            {{-- Account Type --}}
            <div class="flex items-center justify-between px-8 py-5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                    </span>
                    <span class="text-sm font-black text-slate-400">{{ __('Account Type') }}</span>
                </div>
                <span class="text-[15px] font-bold text-slate-500 dark:text-slate-300">{{ __($item->account_type) }}</span>
            </div>

            {{-- Phone --}}
            <div class="flex items-center justify-between px-8 py-5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </span>
                    <span class="text-sm font-black text-slate-400">{{ __('Phone Number') }}</span>
                </div>
                <span class="text-[15px] font-bold text-slate-500 dark:text-slate-300 font-mono" dir="ltr">{{ $item->phone }}</span>
            </div>

            {{-- Ticket Type --}}
            <div class="flex items-center justify-between px-8 py-5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="text-sm font-black text-slate-400">{{ __('Ticket Type') }}</span>
                </div>
                <span class="text-[15px] font-bold text-slate-500 dark:text-slate-300">
                    @php
                        $typeLabel = match($item->type) {
                            'technical'  => __('Complaint against a technician'),
                            'payment'    => __('Payment issue'),
                            'suggestion' => __('Suggestion / Note'),
                            default      => __('General inquiry'),
                        };
                    @endphp
                    {{ $typeLabel }}
                </span>
            </div>

            {{-- Order --}}
            <div class="flex items-center justify-between px-8 py-5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                    <span class="text-sm font-black text-slate-400">{{ __('Order') }}</span>
                </div>
                @if($item->order_id && $item->order)
                    <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-primary font-black text-[15px] hover:underline">
                        {{ __('Order') }} - #{{ $item->order->order_number }}
                    </a>
                @else
                    <span class="text-[15px] font-bold text-slate-300">-</span>
                @endif
            </div>

            {{-- Status --}}
            <div class="flex items-center justify-between px-8 py-5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                    <span class="text-sm font-black text-slate-400">{{ __('Status') }}</span>
                </div>
                @php
                    $statusStyles = [
                        'pending'     => ['bg' => 'bg-amber-50 text-amber-500',    'label' => __('Pending')],
                        'in_progress' => ['bg' => 'bg-slate-100 text-slate-500',   'label' => __('Under Review')],
                        'resolved'    => ['bg' => 'bg-emerald-50 text-emerald-500','label' => __('Solved')],
                        'rejected'    => ['bg' => 'bg-rose-50 text-rose-500',      'label' => __('Rejected')],
                    ];
                    $s = $statusStyles[$item->status] ?? $statusStyles['pending'];
                @endphp
                <span class="px-5 py-1.5 rounded-xl text-[11px] font-black {{ $s['bg'] }}">{{ $s['label'] }}</span>
            </div>

            {{-- Date --}}
            <div class="flex items-center justify-between px-8 py-5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="text-sm font-black text-slate-400">{{ __('Date') }}</span>
                </div>
                <span class="text-[15px] font-bold text-slate-500 dark:text-slate-300 font-mono">{{ $item->created_at->format('j/n/Y') }}</span>
            </div>

        </div>
    </div>

    {{-- ─── Attachment / Image ─── --}}
    @if($item->attachment)
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-sm border border-slate-100 dark:border-white/5 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-50 dark:border-white/5">
            <h2 class="text-[15px] font-black text-[#1A1A31] dark:text-white text-right">{{ __('Issue Image') }}</h2>
        </div>
        <div class="p-8 flex justify-end">
            <a href="{{ asset('storage/' . $item->attachment) }}" target="_blank" class="group">
                <div class="w-28 h-28 rounded-2xl overflow-hidden border-2 border-slate-100 dark:border-white/10 shadow-sm group-hover:scale-105 transition-transform">
                    <img src="{{ asset('storage/' . $item->attachment) }}" alt="{{ __('Issue Image') }}" class="w-full h-full object-cover">
                </div>
            </a>
        </div>
    </div>
    @endif

    {{-- ─── Description ─── --}}
    @if($item->description)
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-sm border border-slate-100 dark:border-white/5 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-50 dark:border-white/5">
            <h2 class="text-[15px] font-black text-[#1A1A31] dark:text-white text-right">{{ __('Issue Description') }}</h2>
        </div>
        <div class="px-8 py-6">
            <p class="text-[15px] font-bold text-slate-500 dark:text-slate-400 leading-loose text-right">{{ $item->description }}</p>
        </div>
    </div>
    @endif

    {{-- ─── Action Buttons ─── --}}
    <div class="flex items-center gap-4 flex-wrap">
        {{-- Send Alert to Technician --}}
        <button @click="openAction('warning', '{{ __('Send Alert to Technician') }}')"
                class="h-12 px-8 bg-primary text-white rounded-2xl font-black text-sm hover:opacity-90 transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            {{ __('Send Alert to Technician') }}
        </button>

        {{-- Suspend Account --}}
        <button @click="openAction('suspension', '{{ __('Suspend Account Temporarily') }}')"
                class="h-12 px-8 bg-white dark:bg-white/5 text-[#1A1A31] dark:text-white border border-slate-200 dark:border-white/10 rounded-2xl font-black text-sm hover:bg-slate-50 dark:hover:bg-white/10 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            {{ __('Suspend Account Temporarily') }}
        </button>

        {{-- Reject --}}
        <button @click="openAction('rejection', '{{ __('Reject') }}')"
                class="h-12 px-8 bg-rose-500 text-white rounded-2xl font-black text-sm hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/20 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ __('Reject') }}
        </button>
    </div>

    {{-- ─── Action Confirmation Modal ─── --}}
    <template x-teleport="body">
        <div x-show="actionModal"
             class="fixed inset-0 z-[200] flex items-center justify-center p-4"
             x-cloak>
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="actionModal = false"></div>
            <div class="relative bg-white dark:bg-[#1A1A31] w-full max-w-sm rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/10 p-8 z-10"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 dir="rtl">

                <h3 class="text-xl font-black text-[#1A1A31] dark:text-white mb-2" x-text="actionLabel"></h3>
                <p class="text-sm font-bold text-slate-400 mb-6">{{ __('Add optional notes below.') }}</p>

                <form action="{{ route('admin.complaints.take-action', $item->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action_type" :value="actionType">
                    <textarea name="notes" rows="3"
                              placeholder="{{ __('Notes (optional)') }}"
                              class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl px-5 py-4 text-sm font-bold text-[#1A1A31] dark:text-white placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-primary/20 resize-none text-right"></textarea>
                    <div class="flex gap-3">
                        <button type="button" @click="actionModal = false"
                                class="flex-1 h-12 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-500 font-black text-sm hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="flex-1 h-12 rounded-2xl bg-primary text-white font-black text-sm hover:opacity-90 transition-all">
                            {{ __('Confirm') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection