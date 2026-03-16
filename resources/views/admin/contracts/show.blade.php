@extends('layouts.admin')

@section('title', __('Contract Details'))
@section('page_title', __('Contract Details'))

@section('content')
<div class="max-w-4xl mx-auto pb-20" dir="rtl">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.contracts.index') }}"
               class="flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-[#1A1A31] dark:hover:text-white transition-colors font-bold group">
                <div class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center border border-slate-100 dark:border-white/10 group-hover:bg-slate-50 dark:hover:bg-white/10 shadow-sm transition-all">
                    <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </div>
            </a>
            <div>
                <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Contract Details') }} <span class="text-slate-300 dark:text-slate-600">←</span></h1>
            </div>
        </div>
        <div class="flex items-center gap-3">
             <a href="{{ route('admin.contracts.edit', $item->id) }}"
               class="px-6 py-3 bg-white dark:bg-white/5 text-[#1A1A31] dark:text-white rounded-xl font-black text-xs border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/10 transition-all uppercase tracking-widest flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                {{ __('Edit') }}
            </a>
            @if($item->contract_file)
            <a href="{{ asset('storage/' . $item->contract_file) }}" target="_blank"
               class="px-6 py-3 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-xl font-black text-xs shadow-lg shadow-[#1A1A31]/20 dark:shadow-white/5 hover:scale-[1.02] transition-all uppercase tracking-widest flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('Download Contract') }}
            </a>
            @endif
        </div>
    </div>

    {{-- Info Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Primary Details --}}
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5">
                <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-50 dark:border-white/5">
                    <div class="w-12 h-12 rounded-2xl bg-[#1A1A31]/5 dark:bg-white/5 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#1A1A31] dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Contract Summary') }}</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-bold mt-0.5">{{ __('Key information and status') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Contract Number') }}</label>
                        <p class="text-lg font-black text-[#1A1A31] dark:text-white">{{ $item->contract_number }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Status') }}</label>
                        <div class="pt-1">
                            <span class="px-3 py-1 rounded-lg text-xs font-black uppercase
                                {{ $item->status == 'active' ? 'bg-green-50 text-green-600 dark:bg-green-500/10' : ($item->status == 'expired' ? 'bg-red-50 text-red-600 dark:bg-red-500/10' : 'bg-blue-50 text-blue-600 dark:bg-blue-500/10') }}">
                                {{ __($item->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Maintenance Company') }}</label>
                        <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->maintenanceCompany->company_name_ar ?? $item->maintenanceCompany->name ?? '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Created At') }}</label>
                        <p class="text-md font-bold text-slate-600 dark:text-slate-300">{{ $item->created_at->format('Y/m/d H:i') }}</p>
                    </div>
                </div>
            </div>

            {{-- Financial Overview --}}
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 overflow-hidden relative">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-12 h-12 rounded-2xl bg-[#1A1A31]/5 dark:bg-white/5 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#1A1A31] dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Financial Details') }}</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-bold mt-0.5">{{ __('Project value and payments') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                    <div class="p-6 bg-slate-50 dark:bg-white/5 rounded-3xl border border-slate-100 dark:border-white/5">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest block mb-2">{{ __('Total Value') }}</label>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-6 h-6">
                            <span class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ number_format($item->project_value) }}</span>
                        </div>
                    </div>
                    <div class="p-6 bg-green-50/50 dark:bg-green-500/5 rounded-3xl border border-green-100/50 dark:border-green-500/10">
                        <label class="text-[10px] font-black text-green-600/60 dark:text-green-400/60 uppercase tracking-widest block mb-2">{{ __('Paid') }}</label>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-6 h-6">
                            <span class="text-2xl font-black text-green-600 dark:text-green-400">{{ number_format($item->paid_amount) }}</span>
                        </div>
                    </div>
                    <div class="p-6 bg-red-50/50 dark:bg-red-500/5 rounded-3xl border border-red-100/50 dark:border-red-500/10">
                        <label class="text-[10px] font-black text-red-600/60 dark:text-red-400/60 uppercase tracking-widest block mb-2">{{ __('Remaining') }}</label>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-6 h-6">
                            <span class="text-2xl font-black text-red-600 dark:text-red-400">{{ number_format($item->remaining_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Details --}}
        <div class="space-y-8">
            {{-- Contact Box --}}
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5">
                <h4 class="text-sm font-black text-[#1A1A31] dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    {{ __('Contact Info') }}
                </h4>
                @if($item->contact_numbers)
                    @foreach(explode(',', $item->contact_numbers) as $phone)
                    <div class="flex items-center justify-between py-3 border-b border-slate-50 dark:border-white/5 last:border-0">
                        <span class="text-sm font-bold text-slate-400">{{ __('Phone') }}</span>
                        <span class="text-sm font-black text-[#1A1A31] dark:text-white" dir="ltr">{{ $phone }}</span>
                    </div>
                    @endforeach
                @else
                    <p class="text-sm text-slate-400 font-bold italic">{{ __('No contact numbers provided') }}</p>
                @endif
            </div>

            {{-- Dates Box --}}
            <div class="bg-[#1A1A31] dark:bg-white/5 rounded-[2rem] p-8 shadow-lg shadow-[#1A1A31]/20 dark:shadow-none text-white">
                <h4 class="text-sm font-black mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"/>
                    </svg>
                    {{ __('Contract Term') }}
                </h4>
                <div class="space-y-6">
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ __('Start Date') }}</span>
                        <p class="text-lg font-black">{{ $item->start_date }}</p>
                    </div>
                    <div class="pt-4 border-t border-white/10">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ __('End Date') }}</span>
                        <p class="text-lg font-black">{{ $item->end_date }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection