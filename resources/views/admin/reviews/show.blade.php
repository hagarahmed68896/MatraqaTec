@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8" dir="rtl">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.reviews.index') }}" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm border border-slate-100 text-slate-400 hover:text-primary transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 12H5m7 7l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-[#1A1A31]">{{ __('Evaluation Data') }}</h1>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Main Data Card -->
        <div class="bg-white rounded-[2.5rem] p-10 border border-slate-50 shadow-sm space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                <!-- Customer info -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400">{{ __('Customer Name:') }}</p>
                        <p class="text-sm font-black text-[#1A1A31]">{{ $item->user->name ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400">{{ __('Customer Account Type:') }}</p>
                        <p class="text-sm font-black text-[#1A1A31]">
                            {{ $item->user && $item->user->type == 'individual' ? __('Individual Client') : __('Corporate Customer') }}
                        </p>
                    </div>
                </div>

                <!-- Service info -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400">{{ __('Service:') }}</p>
                        <p class="text-sm font-black text-[#1A1A31]">
                            {{ app()->getLocale() == 'ar' ? $item->service->parent->name_ar ?? '' : $item->service->parent->name_en ?? '' }} 
                            ({{ app()->getLocale() == 'ar' ? $item->service->name_ar : $item->service->name_en }})
                        </p>
                    </div>
                </div>

                <!-- Technician info -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4m-2 4h.01M7 21h10"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400">{{ __('Technician Name:') }}</p>
                        <p class="text-sm font-black text-[#1A1A31]">{{ $item->technician->user->name ?? ($item->technician->name ?? '-') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-4 0h4"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400">{{ __('Technician Account Type:') }}</p>
                        <p class="text-sm font-black text-[#1A1A31]">
                            {{ $item->technician && $item->technician->maintenance_company_id ? __('Maintenance Company') : __('Platform Technician') }}
                        </p>
                    </div>
                </div>

                <!-- Rating status -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400">{{ __('Status:') }}</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-lg {{ $item->rating > 3 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} text-[11px] font-bold">
                            {{ $item->rating > 3 ? __('Positive') : __('Negative') }}
                        </span>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400">{{ __('Date:') }}</p>
                        <p class="text-sm font-black text-[#1A1A31]">{{ $item->created_at->format('j/n/Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400">{{ __('Time:') }}</p>
                        <p class="text-sm font-black text-[#1A1A31]">{{ $item->created_at->translatedFormat('h:i a') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluation Comment Section -->
        <div class="space-y-4">
            <h3 class="text-lg font-black text-[#1A1A31] tracking-tight">{{ __('Evaluation') }}</h3>
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-sm">
                <div class="bg-slate-50/50 rounded-3xl p-8 relative min-h-[140px]">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <!-- Avatar -->
                            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-white border border-slate-100 shadow-sm flex items-center justify-center shrink-0">
                                @if($item->user && $item->user->avatar)
                                    <img src="{{ Storage::url($item->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <div class="text-primary font-black text-xl">{{ mb_substr($item->user->name ?? '?', 0, 1) }}</div>
                                @endif
                            </div>
                            <!-- Name & Stars -->
                            <div class="space-y-1">
                                <p class="text-sm font-black text-[#1A1A31]">{{ $item->user->name ?? '-' }}</p>
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= (int)$item->rating; $i++)
                                        <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <!-- Time ago -->
                        <span class="text-[11px] font-bold text-slate-400">{{ $item->created_at->diffForHumans() }}</span>
                    </div>
                    <!-- Comment content -->
                    <p class="text-xs font-bold text-slate-500 leading-relaxed pr-18">
                        {{ $item->comment ?: __('No remarks left by customer.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection