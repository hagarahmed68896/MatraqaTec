@extends('layouts.admin')

@section('title', __('Invoice Details'))
@section('page_title', __('Invoice Details'))

@section('content')
<div class="space-y-8 pb-20" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.invoices.index') }}" class="w-10 h-10 flex items-center justify-center bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-xl text-slate-400 hover:text-primary dark:hover:text-white transition-all shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ app()->getLocale() == 'ar' ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7' }}"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Operation - #') }}{{ $item->order->order_number ?? $item->order_id }}</h1>
            <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase
                {{ $item->status == 'sent' ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500' }}">
                {{ $item->status == 'sent' ? __('Sent') : __('Not Sent') }}
            </span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.invoices.download', ['ids' => $item->id]) }}" class="h-11 px-6 flex items-center gap-2 border border-slate-200 dark:border-white/10 text-[#1A1A31] dark:text-white rounded-xl font-bold text-sm hover:bg-slate-50 dark:hover:bg-white/10 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>{{ __('Download') }}</span>
            </a>
            <form action="{{ route('admin.invoices.send', $item->id) }}" method="POST">
                @csrf
                <button type="submit" class="h-11 px-6 flex items-center gap-2 bg-white dark:bg-[#1A1A31] border border-slate-200 dark:border-white/10 text-[#1A1A31] dark:text-white rounded-xl font-bold text-sm hover:bg-slate-50 dark:hover:bg-white/10 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span>{{ $item->status == 'sent' ? __('Resend via Email') : __('Send via Email') }}</span>
                </button>
            </form>
        </div>
    </div>

    <!-- INVOICE CARD -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-xl overflow-hidden relative">
        <div class="p-12 relative z-10">
            <!-- Header Section -->
            <div class="flex justify-between items-start border-b border-slate-100 dark:border-white/5 pb-10 mb-10">
                <div class="space-y-4">
                    <h2 class="text-4xl font-black text-[#1A1A31] dark:text-white">{{ __('Invoice') }}</h2>
                    <p class="text-slate-400 font-bold tracking-widest uppercase text-xs">{{ __('Matraqa Tec Company') }}</p>
                </div>
                <div class="text-right">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-white/5 rounded-2xl flex items-center justify-center ml-auto">
                        <img src="{{ asset('assets/images/logo.png') }}" class="w-10 opacity-80" alt="Logo">
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12">
                <!-- From / To info -->
                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ __('From') }}</h4>
                        <div class="space-y-1">
                            <p class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Matraqa Tec Company') }}</p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-white tracking-wide">example@gmail.com</p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-white tracking-wide">+966 123 1234 123</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ __('To') }}</h4>
                        <div class="space-y-1">
                            <p class="text-sm font-black text-[#1A1A31] dark:text-white">{{ $item->order->user->name ?? '-' }}</p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-white tracking-wide">{{ $item->order->user->email ?? 'no-email@example.com' }}</p>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-white tracking-wide">{{ $item->order->user->phone ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Meta -->
                <div class="flex {{ app()->getLocale() == 'ar' ? 'justify-start' : 'justify-end' }} items-center gap-16">
                    <div class="space-y-4 text-center">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ __('Invoice Number:') }}</h4>
                        <p class="text-sm font-black text-primary font-mono tracking-widest">{{ $item->invoice_number ?? $item->id }}</p>
                    </div>
                    <div class="space-y-4 text-center">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ __('Invoice Date:') }}</h4>
                        <p class="text-sm font-black text-[#1A1A31] dark:text-white font-mono tracking-wider">{{ $item->issue_date ? $item->issue_date->format('j F Y') : '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="rounded-[2rem] border border-slate-50 dark:border-white/5 overflow-hidden mb-12">
                <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 dark:border-white/5">
                            <th class="py-6 px-10">{{ __('Description') }}</th>
                            <th class="py-6 px-4 text-center">{{ __('Quantity') }}</th>
                            <th class="py-6 px-8 text-center">{{ __('Unit Price') }}</th>
                            <th class="py-6 px-10 {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-right' }}">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                        @foreach($items_details as $detail)
                        <tr class="text-xs font-bold text-slate-600 dark:text-white/70">
                            <td class="py-6 px-10">
                                <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ $detail['description'] }}</span>
                            </td>
                            <td class="py-6 px-4 text-center text-slate-400">{{ $detail['quantity'] == 1 ? '-' : $detail['quantity'] }}</td>
                            <td class="py-6 px-8 text-center">
                                <div class="flex items-center justify-center gap-1.5 ml-2">
                                    <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ number_format($detail['price'], 0) }}</span>
                                    <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-3.5 h-3.5 opacity-40">
                                </div>
                            </td>
                            <td class="py-6 px-10 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ number_format($detail['total'], 0) }}</span>
                                    <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-3.5 h-3.5 opacity-40">
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Section -->
            <div class="flex justify-end">
                <div class="w-full md:w-80 space-y-6">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-400">{{ __('Subtotal') }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-[#1A1A31] dark:text-white font-black">{{ number_format($item->amount, 0) }}</span>
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-4 h-4 opacity-40">
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-400">{{ __('Fees') }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-[#1A1A31] dark:text-white font-black">0</span>
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-4 h-4 opacity-20">
                        </div>
                    </div>
                    <div class="h-px bg-slate-100 dark:bg-white/5 my-2"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Grand Total') }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-black text-primary">{{ number_format($item->amount, 0) }}</span>
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-6 h-6">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection