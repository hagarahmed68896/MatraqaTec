@extends('layouts.admin')

@section('title', __('Permissions List'))
@section('page_title', __('Permissions List'))

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-8 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($grouped as $module => $data)
            <div class="space-y-4 p-6 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5">
                <h3 class="text-lg font-black text-slate-900 dark:text-white border-b border-primary/20 pb-2 uppercase tracking-tight">{{ $module }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($data['actions'] as $action)
                    <div class="group relative">
                        <span class="px-3 py-1 bg-white dark:bg-white/10 dark:text-white rounded-lg text-[10px] font-black border border-slate-200 dark:border-white/10 shadow-sm cursor-default">
                            {{ $action['label'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection