@extends('layouts.admin')
@section('title', __('Services Report'))
@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Services Report') }}</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-white/5">
            <h3 class="font-bold mb-4">{{ __('Top Services') }}</h3>
            @foreach($top_services as $s)
            <div class="flex items-center justify-between py-2 border-b border-slate-50">
                <span class="text-sm font-bold">{{ $s->service->name_ar ?? 'Service' }}</span>
                <span class="text-sm font-black">{{ $s->count }} {{ __('Orders') }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
