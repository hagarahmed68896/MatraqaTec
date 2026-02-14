@extends('layouts.admin')

@section('title', __('Technician Request Details'))
@section('page_title', __('Technician Request Details'))

@section('content')
<div class="space-y-6 pb-20">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4 mb-2">
        <a href="{{ route('admin.technician-requests.index') }}" 
           class="w-10 h-10 rounded-xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/5 flex items-center justify-center text-slate-400 hover:text-primary transition-all shadow-sm">
            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ $item->name_ar ?? $item->name }}</h2>
    </div>

    <!-- Main Profile Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden relative">
        <!-- Banner/Cover -->
        <div class="h-48 bg-[#1A1A31] dark:bg-black/40 relative">
            <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\"20\" height=\"20\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"%239C92AC\" fill-opacity=\"0.4\" fill-rule=\"evenodd\"%3E%3Ccircle cx=\"3\" cy=\"3\" r=\"3\"/%3E%3Ccircle cx=\"13\" cy=\"13\" r=\"3\"/%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="px-10 pb-10">
            <!-- Profile Info Header -->
            <div class="relative flex flex-col md:flex-row items-end gap-6 -mt-16 mb-10">
                <div class="w-32 h-32 rounded-[2rem] border-4 border-white dark:border-[#1A1A31] bg-slate-100 dark:bg-white/5 shadow-xl overflow-hidden relative z-10">
                    @if($item->photo)
                        <img src="{{ asset($item->photo) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-3xl font-black text-slate-300">
                            {{ mb_substr($item->name_ar ?? $item->name, 0, 1) }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-8 gap-x-12">
                <!-- Name -->
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Full Name') }}</p>
                        <p class="text-md font-bold text-slate-900 dark:text-white">{{ $item->name_ar ?? $item->name }}</p>
                    </div>
                </div>

                <!-- Account Type -->
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Account Type') }}</p>
                        <p class="text-md font-bold text-slate-900 dark:text-white">
                            @if($item->maintenance_company_id)
                                {{ __('Company Technician') }} ({{ $item->maintenanceCompany->user->name ?? $item->company_name }})
                            @else
                                {{ __('Platform Technician') }}
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Phone -->
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Phone Number') }}</p>
                        <p class="text-md font-bold text-slate-900 dark:text-white font-mono">+966 {{ $item->phone }}</p>
                    </div>
                </div>

                <!-- Email -->
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Email Address') }}</p>
                        <p class="text-md font-bold text-slate-900 dark:text-white">{{ $item->email }}</p>
                    </div>
                </div>

                <!-- Districts -->
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Working Districts') }}</p>
                        <p class="text-md font-bold text-slate-900 dark:text-white">{{ is_array($item->districts) ? implode(', ', $item->districts) : ($item->districts ?? __('All Areas')) }}</p>
                    </div>
                </div>

                <!-- Service -->
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Main Service') }}</p>
                        <p class="text-md font-bold text-slate-900 dark:text-white">{{ $item->service->name_ar ?? '-' }}</p>
                    </div>
                </div>

                <!-- Experience -->
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Years of Experience') }}</p>
                        <p class="text-md font-bold text-slate-900 dark:text-white">{{ $item->years_experience }} {{ __('Years') }}</p>
                    </div>
                </div>

                <!-- Date -->
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Request Date') }}</p>
                        <p class="text-md font-bold text-slate-900 dark:text-white">{{ $item->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm p-10">
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-8">{{ __('Documents') }}</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="p-6 rounded-3xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                <p class="text-sm font-black text-slate-900 dark:text-white mb-4">{{ __('Iqama / National ID') }}</p>
                <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-400">Scan_File.pdf</span>
                    </div>
                    <button type="button" class="px-4 py-2 bg-[#1A1A31] text-white text-[10px] font-black rounded-lg hover:bg-black transition-all">
                        {{ __('View') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bio Section -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm p-10">
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-6 uppercase tracking-widest text-[14px]">{{ __('About Technician') }}</h3>
        <div class="p-8 rounded-3xl bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 text-sm font-bold leading-relaxed line-clamp-3">
            {{ $item->bio_ar ?? $item->bio_en ?? __('No bio provided') }}
        </div>
    </div>

    <!-- Rejection Reason Section (Conditional) -->
    @if($item->status === 'rejected' && $item->rejection_reason)
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm p-10">
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-6 uppercase tracking-widest text-[14px]">{{ __('Rejection Reason') }}</h3>
        <div class="p-8 rounded-3xl bg-red-50 dark:bg-red-500/5 text-red-600 dark:text-red-400 text-sm font-black leading-relaxed">
            {{ $item->rejection_reason }}
        </div>
    </div>
    @endif
</div>
@endsection