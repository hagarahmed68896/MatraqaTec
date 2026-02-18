@extends('layouts.admin')
@section('title', __('Financial Report'))
@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Financial Report') }}</h2>
    <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-white/5">
        <h3 class="font-bold mb-4">{{ __('Settlements Summary') }}</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-slate-400 border-b border-slate-50">
                    <th class="py-3 text-right">{{ __('Status') }}</th>
                    <th class="py-3 text-right">{{ __('Count') }}</th>
                    <th class="py-3 text-right">{{ __('Total Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($settlements as $s)
                <tr class="border-b border-slate-50">
                    <td class="py-3">{{ $s->status }}</td>
                    <td class="py-3">{{ $s->count }}</td>
                    <td class="py-3">{{ $s->total_amount }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
