@extends('layouts.admin')
@section('title', __('Users Report'))
@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Users Report') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($distribution as $item)
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-white/5">
            <p class="text-sm text-slate-500 font-bold uppercase">{{ $item->type }}</p>
            <h3 class="text-2xl font-black mt-1">{{ $item->count }}</h3>
        </div>
        @endforeach
    </div>
    <!-- Add charts or tables here as needed -->
</div>
@endsection
