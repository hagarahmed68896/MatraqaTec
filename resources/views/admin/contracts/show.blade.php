@extends('layouts.admin')

@section('title', __('Contract Details'))
@section('page_title', __('Contract Details'))

@section('content')
<div class="max-w-4xl mx-auto pb-20" dir="rtl" x-data="{ openOfferModal: false, openRejectModal: false }">

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
                <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Contract Details') }} </h1>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(in_array($item->status, ['pending', 'under_review']))
            <button @click="openOfferModal = true"
                    class="px-6 py-3 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-xl font-black text-xs shadow-lg shadow-[#1A1A31]/20 dark:shadow-white/5 hover:scale-[1.02] transition-all flex items-center gap-2 uppercase tracking-widest">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('Send Price Offer') }}
            </button>
            <button @click="openRejectModal = true"
                    class="px-6 py-3 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-500/20 rounded-xl font-black text-xs hover:bg-red-100 dark:hover:bg-red-500/20 transition-all flex items-center gap-2 uppercase tracking-widest">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('Reject Request') }}
            </button>
            @endif

            @if(!in_array($item->status, ['pending', 'under_review']))
             <a href="{{ route('admin.contracts.edit', $item->id) }}"
               class="px-6 py-3 bg-white dark:bg-white/5 text-[#1A1A31] dark:text-white rounded-xl font-black text-xs border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/10 transition-all uppercase tracking-widest flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                {{ __('Edit') }}
            </a>
            @endif
            @if($item->contract_file)
            <a href="{{ $item->contract_file_url }}" target="_blank"
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
                            @php
                                $statusColors = [
                                    'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10',
                                    'under_review' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10',
                                    'offer_sent' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10',
                                    'offer_accepted' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
                                    'offer_rejected' => 'bg-red-50 text-red-600 dark:bg-red-500/10',
                                    'awaiting_signed_contract' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10',
                                    'awaiting_payment_proof' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/10',
                                    'active' => 'bg-green-50 text-green-600 dark:bg-green-500/10',
                                    'completed' => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10',
                                    'expired' => 'bg-red-50 text-red-600 dark:bg-red-500/10',
                                ];
                                $colorClass = $statusColors[$item->status] ?? 'bg-slate-50 text-slate-600 dark:bg-white/5';
                            @endphp
                            <span class="px-3 py-1 rounded-lg text-xs font-black uppercase {{ $colorClass }}">
                                {{ __($item->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Corporate Customer') }}</label>
                        <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->corporateCustomer->company_name_ar ?? $item->corporateCustomer->company_name_en ?? '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Created At') }}</label>
                        <p class="text-md font-bold text-slate-600 dark:text-slate-300">{{ $item->created_at->format('Y/m/d H:i') }}</p>
                    </div>
                </div>
            </div>

            @if($item->company_name)
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5">
                <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-50 dark:border-white/5">
                    <div class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Corporate Tendering Request Details') }}</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-bold mt-0.5">{{ __('Company information and request details') }}</p>
                    </div>
                </div>

                @if($item->status === 'offer_rejected')
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border-r-4 border-red-500 rounded-xl text-red-700 dark:text-red-400">
                    <p class="text-sm font-black">{{ __('Rejection Reason') }}:</p>
                    <p class="text-xs mt-1 font-bold">{{ $item->rejection_reason ?? __('No reason provided') }}</p>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-8 mb-8">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Company Name') }}</label>
                        <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->company_name }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Commercial Record Number') }}</label>
                        <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->commercial_record_number }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Head Office') }}</label>
                        <p class="text-md font-bold text-[#1A1A31] dark:text-white">{{ $item->head_office }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Contact Number') }}</label>
                        <p class="text-md font-bold text-[#1A1A31] dark:text-white" dir="ltr">{{ $item->contact_number }}</p>
                    </div>
                </div>

                <div class="space-y-2 mb-8">
                    <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Service Description') }}</label>
                    <p class="text-sm text-slate-700 dark:text-slate-300 font-bold bg-slate-50 dark:bg-white/5 p-4 rounded-xl whitespace-pre-wrap leading-relaxed">{{ $item->service_description }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($item->boq_file)
                    <a href="{{ $item->boq_file_url }}" target="_blank" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all font-bold text-xs">
                        <span class="text-[#1A1A31] dark:text-white">{{ __('BOQ File') }}</span>
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                    @endif

                    @if($item->drawings_file)
                    <a href="{{ $item->drawings_file_url }}" target="_blank" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all font-bold text-xs">
                        <span class="text-[#1A1A31] dark:text-white">{{ __('Drawings File') }}</span>
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                    @endif

                    @if($item->other_docs_file)
                    <a href="{{ $item->other_docs_file_url }}" target="_blank" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all font-bold text-xs">
                        <span class="text-[#1A1A31] dark:text-white">{{ __('Other Documents File') }}</span>
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-50 dark:border-white/5">
                    @if($item->price_offer_file)
                    <a href="{{ $item->price_offer_file_url }}" target="_blank" class="flex items-center justify-between p-4 bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100/50 dark:border-indigo-500/10 rounded-2xl hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all font-bold text-xs">
                        <span class="text-[#1A1A31] dark:text-white">{{ __('Price Offer File') }}</span>
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                    @endif

                    @if($item->signed_contract_file)
                    <a href="{{ $item->signed_contract_file_url }}" target="_blank" class="flex items-center justify-between p-4 bg-teal-50/50 dark:bg-teal-500/5 border border-teal-100/50 dark:border-teal-500/10 rounded-2xl hover:bg-teal-50 dark:hover:bg-teal-500/10 transition-all font-bold text-xs">
                        <span class="text-[#1A1A31] dark:text-white">{{ __('Signed Contract File') }}</span>
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Financial Overview --}}
            @if(!in_array($item->status, ['pending', 'under_review']))
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
            @endif
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
                @php
                    $phones = [];
                    if ($item->contact_numbers) {
                        $phones = array_filter(explode(',', $item->contact_numbers));
                    } elseif ($item->contact_number) {
                        $phones = [$item->contact_number];
                    }
                @endphp
                @if(count($phones) > 0)
                    @foreach($phones as $phone)
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
            @if($item->start_date || $item->end_date)
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
            @endif
        </div>

        @if(!in_array($item->status, ['pending', 'under_review', 'offer_sent', 'offer_rejected']))
        {{-- Payment Receipts --}}
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 mt-8">
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-50 dark:border-white/5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-green-500/5 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Payment Receipts') }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 font-bold mt-0.5">{{ __('List of uploaded payment confirmations') }}</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto text-right">
                    <table class="w-full">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 dark:border-white/5">
                                <th class="py-4 px-4">{{ __('Date') }}</th>
                                <th class="py-4 px-4">{{ __('Amount') }}</th>
                                <th class="py-4 px-4">{{ __('Method') }}</th>
                                <th class="py-4 px-4">{{ __('File') }}</th>
                                <th class="py-4 px-4">{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-white/5">
                            @forelse($item->paymentReceipts as $receipt)
                            <tr class="group">
                                <td class="py-4 px-4">
                                     <span class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ $receipt->payment_date->format('Y/m/d') }}</span>
                                </td>
                                <td class="py-4 px-4">
                                     <span class="text-sm font-black text-green-600 dark:text-green-400 font-mono">{{ number_format($receipt->amount) }} SAR</span>
                                </td>
                                <td class="py-4 px-4">
                                     <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __($receipt->payment_method) }}</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                     <a href="{{ $receipt->receipt_url }}" target="_blank" class="text-primary dark:text-white font-black text-xs hover:underline uppercase tracking-widest">
                                         {{ __('View Receipt') }}
                                     </a>
                                </td>
                                <td class="py-4 px-4">
                                     <p class="text-xs text-slate-400 font-bold max-w-xs truncate">{{ $receipt->notes ?? '-' }}</p>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-300 font-bold uppercase tracking-widest">{{ __('No receipts found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Send Price Offer Modal --}}
    <div x-show="openOfferModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] max-w-lg w-full p-8 border border-slate-100 dark:border-white/10 shadow-2xl relative" @click.away="openOfferModal = false">
            <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-6 text-right">{{ __('Send Price Offer') }}</h3>
            
            <form action="{{ route('admin.contracts.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="offer_sent">

                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">{{ __('Project Value') }} (SAR)</label>
                    <input type="number" name="project_value" required min="0" placeholder="0"
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none text-right dark:text-white">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">{{ __('Price Offer File') }} (PDF)</label>
                    <input type="file" name="price_offer_file" required accept=".pdf"
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none dark:text-white">
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="flex-1 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-xs shadow-lg hover:scale-[1.01] hover:brightness-110 transition-all">
                        {{ __('Send') }}
                    </button>
                    <button type="button" @click="openOfferModal = false" class="px-6 py-4 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black text-xs border border-slate-200 dark:border-white/10 hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Request Modal --}}
    <div x-show="openRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] max-w-lg w-full p-8 border border-slate-100 dark:border-white/10 shadow-2xl relative" @click.away="openRejectModal = false">
            <h3 class="text-lg font-black text-[#1A1A31] dark:text-white mb-6 text-right">{{ __('Reject Request') }}</h3>
            
            <form action="{{ route('admin.contracts.update', $item->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="offer_rejected">

                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">{{ __('Rejection Reason') }}</label>
                    <textarea name="rejection_reason" required rows="4" placeholder="{{ __('Enter rejection reason') }}"
                              class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none text-right dark:text-white"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="flex-1 py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black text-xs shadow-lg hover:scale-[1.01] transition-all">
                        {{ __('Reject') }}
                    </button>
                    <button type="button" @click="openRejectModal = false" class="px-6 py-4 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black text-xs border border-slate-200 dark:border-white/10 hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection