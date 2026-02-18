@extends('layouts.admin')

@section('title', __('Financial Settlements'))
@section('page_title', __('Financial Settlements'))

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-primary/10 rounded-2xl text-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Total Settlements') }}</h3>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($stats['total']['value'], 2) }} <span class="text-xs"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span></p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-500/10 rounded-2xl text-yellow-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Pending') }}</h3>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($stats['pending']['value'], 2) }} <span class="text-xs"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span></p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/10 rounded-2xl text-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Transferred') }}</h3>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($stats['transferred']['value'], 2) }} <span class="text-xs"><img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.financial-settlements.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search order #, account...') }}" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            
            <select name="status" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
                <option value="">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>{{ __('Transferred') }}</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
            </select>

            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                {{ __('Filter') }}
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="pb-4 px-6">{{ __('ID') }}</th>
                        <th class="pb-4 px-6">{{ __('Account') }}</th>
                        <th class="pb-4 px-6">{{ __('Order') }}</th>
                        <th class="pb-4 px-6">{{ __('Amount') }}</th>
                        <th class="pb-4 px-6">{{ __('Date') }}</th>
                        <th class="pb-4 px-6">{{ __('Status') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6">#{{ $item->id }}</td>
                        <td class="py-4 px-6">
                            @if($item->maintenanceCompany)
                            <span class="px-2 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px]">{{ __('Company') }}</span>
                            <span class="block mt-1">{{ $item->maintenanceCompany->name_ar ?? '-' }}</span>
                            @else
                            <span class="px-2 py-1 rounded-lg bg-purple-50 text-purple-600 text-[10px]">{{ __('Technician') }}</span>
                            <span class="block mt-1">{{ $item->user->name ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-primary">#{{ $item->order->order_number ?? $item->order_id }}</td>
                        <td class="py-4 px-6 text-green-600 font-black">{{ number_format($item->amount, 2) }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></td>
                        <td class="py-4 px-6 opacity-70">{{ $item->created_at->format('Y-m-d') }}</td>
                        <td class="py-4 px-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ $item->status == 'transferred' ? 'bg-green-100 text-green-600' : ($item->status == 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                                {{ __($item->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.financial-settlements.show', $item->id) }}" class="p-2 rounded-lg hover:bg-blue-50 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">{{ __('No settlements found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="p-6 border-t border-slate-100 dark:border-white/5">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>
@endsection