@extends('layouts.admin')

@section('title', __('Payment Details') . ' #' . $item->id)

@section('content')
<div class="space-y-8 pb-20" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.payments.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-100 dark:border-white/10 text-[#1A1A31] dark:text-white hover:bg-slate-50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app()->getLocale() == 'ar' ? 'M14 5l7 7m0 0l-7 7m7-7H3' : 'M10 19l-7-7m0 0l7-7m-7 7h18' }}"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Payment Details') }} - #{{ $item->id }}</h1>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-6 py-2 rounded-xl text-xs font-black uppercase
                {{ $item->status == 'completed' ? 'bg-green-100 text-green-600' : ($item->status == 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                {{ __($item->status) }}
            </span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 flex flex-col justify-center h-32 relative overflow-hidden group">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Amount') }}</p>
            <div class="flex items-center gap-2">
                <h3 class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ number_format($item->amount, 2) }}</h3>
                <span class="text-xs text-slate-400 font-bold"><img src="/assets/images/Vector (1).svg" alt="SAR" class="inline-block w-4 h-4 align-middle"></span>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 flex flex-col justify-center h-32 relative overflow-hidden group">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Payment Method') }}</p>
            <h3 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __($item->payment_method) }}</h3>
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 flex flex-col justify-center h-32 relative overflow-hidden group">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Transaction Date') }}</p>
            <h3 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ $item->created_at->format('Y-m-d') }}</h3>
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details Card -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-8 capitalize">{{ __('Detailed Information') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Customer') }}</p>
                            <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->user->name ?? '-' }}</p>
                            <span class="text-[10px] font-black text-slate-400 uppercase">{{ __($item->user->type ?? 'Individual') }}</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Order Reference') }}</p>
                            @if($item->order_id)
                                <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-md font-black text-primary hover:underline">#{{ $item->order_id }}</a>
                            @else
                                <p class="text-md font-bold text-slate-400">-</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Payment Method') }}</p>
                            <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ __($item->payment_method) }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Created At') }}</p>
                            <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->created_at->format('Y-m-d H:i A') }}</p>
                        </div>
                    </div>
                </div>

                @if($item->status == 'failed' && $item->remarks)
                <div class="mt-8 p-6 rounded-2xl bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-800">
                    <p class="text-xs font-black text-red-600 uppercase tracking-widest mb-2">{{ __('Rejection Reason') }}</p>
                    <p class="text-sm font-bold text-red-500">{{ $item->remarks }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar / Actions -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-8">
                <h3 class="text-md font-black text-[#1A1A31] dark:text-white mb-6">{{ __('Quick Actions') }}</h3>
                <div class="space-y-4">
                    @if($item->user_id)
                        @php
                            $customerRoute = in_array($item->user->type, ['individual', 'client']) 
                                ? 'admin.individual-customers.show' 
                                : 'admin.corporate-customers.show';
                        @endphp
                        <a href="{{ route($customerRoute, $item->user_id) }}" class="flex items-center justify-center gap-3 w-full py-4 rounded-2xl bg-slate-50 dark:bg-white/5 text-[#1A1A31] dark:text-white font-black text-xs hover:bg-slate-100 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ __('Customer Profile') }}
                        </a>
                    @endif

                    @if($item->order_id)
                        <a href="{{ route('admin.orders.show', $item->order_id) }}" class="flex items-center justify-center gap-3 w-full py-4 rounded-2xl bg-[#1A1A31] dark:bg-primary text-white font-black text-xs shadow-lg hover:scale-[1.02] transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            {{ __('View Linked Order') }}
                        </a>
                    @endif
                </div>
            </div>
            
            <!-- Audit Info -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xs font-black text-slate-400 tracking-widest uppercase">{{ __('System info') }}</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between text-[11px] font-bold">
                        <span class="text-slate-400 capitalize">{{ __('Last updated') }}:</span>
                        <span class="text-[#1A1A31] dark:text-white">{{ $item->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex justify-between text-[11px] font-bold">
                        <span class="text-slate-400 capitalize">{{ __('Transaction ID') }}:</span>
                        <span class="text-[#1A1A31] dark:text-white uppercase">TXN-{{ $item->id }}{{ $item->created_at->format('Ymd') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection