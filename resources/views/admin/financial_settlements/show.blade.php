@extends('layouts.admin')

@section('title', __('Financial Settlement Details') . ' #' . $item->id)

@section('content')
<div class="space-y-8 pb-20" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.financial-settlements.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-100 dark:border-white/10 text-[#1A1A31] dark:text-white hover:bg-slate-50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app()->getLocale() == 'ar' ? 'M14 5l7 7m0 0l-7 7m7-7H3' : 'M10 19l-7-7m0 0l7-7m-7 7h18' }}"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Settlement Details') }} - #{{ $item->id }}</h1>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-6 py-2 rounded-xl text-xs font-black uppercase
                {{ $item->status == 'transferred' ? 'bg-green-100 text-green-600' : ($item->status == 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                {{ __($item->status) }}
            </span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 flex flex-col justify-center h-48 relative overflow-hidden group">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Settlement Amount') }}</p>
            <div class="flex items-center gap-2">
                <h3 class="text-4xl font-black text-[#1A1A31] dark:text-white">{{ number_format($item->amount, 2) }}</h3>
                <span class="text-sm text-slate-400 font-bold"><img src="/assets/images/Vector (1).svg" alt="SAR" class="inline-block w-4 h-4 align-middle"></span>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                <span class="text-[10px] font-black text-slate-400 uppercase">{{ __('Net to Provider') }}</span>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform text-[#1A1A31] dark:text-white">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 flex flex-col justify-center h-48 relative overflow-hidden group">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Commission') }}</p>
            <h3 class="text-3xl font-black text-[#1A1A31] dark:text-white">0.00</h3>
            <span class="text-xs text-slate-400 font-bold"><img src="/assets/images/Vector (1).svg" alt="SAR" class="inline-block w-4 h-4 align-middle"></span>
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform text-[#1A1A31] dark:text-white">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 flex flex-col justify-center h-48 relative overflow-hidden group">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Settlement Status') }}</p>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center 
                    {{ $item->status == 'transferred' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"></path></svg>
                </div>
                <h3 class="text-xl font-black text-[#1A1A31] dark:text-white uppercase">{{ __($item->status) }}</h3>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform text-[#1A1A31] dark:text-white">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details Section -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-10">{{ __('Settlement Information') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-12">
                    <!-- Provider Info -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Service Provider') }}</p>
                            @if($item->maintenanceCompany)
                                <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->maintenanceCompany->company_name_ar ?? $item->maintenanceCompany->company_name_en }}</p>
                                <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 text-[10px] font-black uppercase">{{ __('Company') }}</span>
                            @else
                                <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->user->name ?? '-' }}</p>
                                <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-500/10 text-purple-600 text-[10px] font-black uppercase">{{ __('Technician') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Linked Order -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Referenced Order') }}</p>
                            @if($item->order)
                                <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-md font-black text-primary hover:underline">#{{ $item->order->order_number ?? $item->order_id }}</a>
                            @else
                                <p class="text-md font-bold text-slate-400">-</p>
                            @endif
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Creation Date') }}</p>
                            <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->created_at->format('Y-m-d H:i A') }}</p>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Payment Method') }}</p>
                            <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ __($item->payment_method ?? 'N/A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bank Details Sim -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10">
                <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-6 uppercase tracking-wider">{{ __('Bank Details') }}</h3>
                <div class="p-8 rounded-3xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase mb-2">{{ __('Beneficiary Name') }}</p>
                        <p class="text-lg font-black text-[#1A1A31] dark:text-white">{{ $item->user->name ?? $item->maintenanceCompany->user->name ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-black text-slate-400 uppercase mb-2">{{ __('IBAN') }}</p>
                        <p class="text-lg font-mono font-black text-[#1A1A31] dark:text-white tracking-widest">SA** **** **** **** **** ****</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-8">
                <h3 class="text-md font-black text-[#1A1A31] dark:text-white mb-8">{{ __('Update Status') }}</h3>
                
                <form action="{{ route('admin.financial-settlements.change-status', $item->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        @foreach(['pending', 'transferred', 'suspended'] as $status)
                        <label class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-transparent cursor-pointer hover:border-primary/30 transition-all group">
                            <div class="flex items-center gap-3">
                                <span class="capitalize text-xs font-black text-slate-500 dark:text-white group-hover:text-[#1A1A31] dark:group-hover:text-white">{{ __($status) }}</span>
                            </div>
                            <input type="radio" name="status" value="{{ $status }}" {{ $item->status == $status ? 'checked' : '' }} class="w-4 h-4 text-primary border-slate-300 focus:ring-primary">
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="w-full py-4 mt-4 bg-primary text-white rounded-[1.5rem] font-black text-xs shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all">
                        {{ __('Save Changes') }}
                    </button>
                </form>
            </div>

            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/10 text-blue-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xs font-black text-slate-400 tracking-widest uppercase">{{ __('Audit Log') }}</h3>
                </div>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-[11px] font-black text-[#1A1A31] dark:text-white">{{ __('Settlement Created') }}</p>
                            <p class="text-[9px] font-bold text-slate-400">{{ $item->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @if($item->status == 'transferred')
                    <div class="flex gap-4">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-[11px] font-black text-[#1A1A31] dark:text-white">{{ __('Funds Transferred') }}</p>
                            <p class="text-[9px] font-bold text-slate-400">{{ $item->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection