@extends('layouts.admin')

@section('title', __('Refund Requests'))
@section('page_title', __('Refund Requests'))

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <span class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Pending Refunds') }}</span>
            <span class="text-3xl font-black text-amber-500">{{ \App\Models\Refund::where('status', 'pending')->count() }}</span>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <span class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Total Refunded') }}</span>
            <span class="text-3xl font-black text-green-500">{{ number_format(\App\Models\Refund::where('status', 'transferred')->sum('amount'), 2) }}</span>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <span class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Total Requests') }}</span>
            <span class="text-3xl font-black text-slate-900 dark:text-white">{{ \App\Models\Refund::count() }}</span>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <span class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Rejected') }}</span>
            <span class="text-3xl font-black text-red-500">{{ \App\Models\Refund::where('status', 'rejected')->count() }}</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.refunds.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by refund #, order #...') }}" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            
            <select name="status" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
                <option value="">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>{{ __('Transferred') }}</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
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
                        <th class="pb-4 px-6">{{ __('Refund #') }}</th>
                        <th class="pb-4 px-6">{{ __('Client') }}</th>
                        <th class="pb-4 px-6">{{ __('Order') }}</th>
                        <th class="pb-4 px-6">{{ __('Amount') }}</th>
                        <th class="pb-4 px-6">{{ __('Status') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6">
                            <span class="text-slate-900 dark:text-white">{{ $item->refund_number }}</span>
                        </td>
                        <td class="py-4 px-6">
                            @if($item->order && $item->order->user)
                                <span class="block">{{ $item->order->user->name }}</span>
                                <span class="text-[9px] opacity-70 uppercase tracking-tighter">{{ __($item->order->user->type) }}</span>
                            @else
                                <span class="text-slate-400">{{ __('Deleted User') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            @if($item->order)
                                <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-primary hover:underline">#{{ $item->order->order_number }}</a>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-red-500">{{ number_format($item->amount, 2) }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></td>
                        <td class="py-4 px-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ $item->status == 'transferred' ? 'bg-green-100 text-green-600' : ($item->status == 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600') }}">
                                {{ __($item->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.refunds.show', $item->id) }}" class="p-2 rounded-lg hover:bg-slate-50 text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">{{ __('No refund requests found') }}</td>
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