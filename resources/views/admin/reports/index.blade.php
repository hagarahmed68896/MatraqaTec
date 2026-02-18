@extends('layouts.admin')

@section('title', __('Reports Summary'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Reports Summary') }}</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- New Orders Card -->
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-white/5 group hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <span class="text-xs font-bold {{ $data['header_stats']['new_orders']['change'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $data['header_stats']['new_orders']['change'] }}%
                </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-bold">{{ __('New Orders') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $data['header_stats']['new_orders']['count'] }}</h3>
        </div>

        <!-- Revenue Card -->
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-white/5 group hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center text-orange-600 dark:text-orange-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-xs font-bold {{ $data['header_stats']['total_revenue']['change'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $data['header_stats']['total_revenue']['change'] }}%
                </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-bold">{{ __('Total Revenue') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $data['header_stats']['total_revenue']['amount'] }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></h3>
        </div>

        <!-- Technicians Card -->
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-white/5 group hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="text-xs font-bold text-slate-500">
                    {{ $data['header_stats']['available_technicians']['percentage'] }}% {{ __('Available') }}
                </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-bold">{{ __('Technicians') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $data['header_stats']['available_technicians']['count'] }} / {{ $data['header_stats']['available_technicians']['total'] }}</h3>
        </div>

        <!-- Rating Card -->
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-white/5 group hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-yellow-50 dark:bg-yellow-500/10 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </div>
                <span class="text-xs font-bold {{ $data['header_stats']['average_rating']['change'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $data['header_stats']['average_rating']['change'] }}%
                </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-bold">{{ __('Average Rating') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $data['header_stats']['average_rating']['rating'] }} / 5</h3>
        </div>
    </div>

    <!-- Links Group -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.reports.users') }}" class="p-4 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-100 dark:border-white/5 hover:border-primary transition-all text-center">
            <span class="font-bold text-sm">{{ __('Users Report') }}</span>
        </a>
        <a href="{{ route('admin.reports.financials') }}" class="p-4 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-100 dark:border-white/5 hover:border-primary transition-all text-center">
            <span class="font-bold text-sm">{{ __('Financial Report') }}</span>
        </a>
        <a href="{{ route('admin.reports.services') }}" class="p-4 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-100 dark:border-white/5 hover:border-primary transition-all text-center">
            <span class="font-bold text-sm">{{ __('Services Report') }}</span>
        </a>
        <a href="{{ route('admin.reports.technicians') }}" class="p-4 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-100 dark:border-white/5 hover:border-primary transition-all text-center">
            <span class="font-bold text-sm">{{ __('Technicians Report') }}</span>
        </a>
    </div>
</div>
@endsection
