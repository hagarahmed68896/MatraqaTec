@extends('layouts.admin')
@section('title', __('Technicians Report'))
@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Technicians Report') }}</h2>
    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-white/5">
        <h3 class="font-bold mb-4">{{ __('Performance Trends') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-xl">
                <p class="text-xs text-slate-400 font-bold mb-1 uppercase">{{ __('Completed Orders') }}</p>
                <p class="text-xl font-black">{{ $trends['completed_orders'] }}</p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-xl">
                <p class="text-xs text-slate-400 font-bold mb-1 uppercase">{{ __('Cancelled Orders') }}</p>
                <p class="text-xl font-black text-red-500">{{ $trends['cancelled_orders'] }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
