@extends('layouts.admin')

@section('title', __('Platform Profits'))
@section('page_title', __('Platform Profits'))

@section('content')
<div class="space-y-6">
    <!-- Total Profit Card -->
    <div class="bg-primary rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl shadow-primary/20">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h3 class="text-white/70 text-sm font-black uppercase tracking-widest mb-2">{{ __('Total Platform Profits') }}</h3>
                <p class="text-4xl md:text-6xl font-black">{{ number_format($total_profit, 2) }} <span class="text-2xl">{{ __('SAR') }}</span></p>
            </div>
            <div class="bg-white/10 p-4 rounded-3xl backdrop-blur-md border border-white/10">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <!-- Decorative SVG -->
        <svg class="absolute right-0 top-0 w-64 h-64 text-white/5 -mr-20 -mt-20" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM5.884 6.884a1 1 0 10-1.414-1.414l-.707.707a1 1 0 101.414 1.414l.707-.707zm11.314-1.414a1 1 0 10-1.414 1.414l.707.707a1 1 0 101.414-1.414l-.707-.707zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 10-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM14.828 14.828l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414z"></path></svg>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.platform-profits.index') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by order number...') }}" class="flex-1 px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                {{ __('Find') }}
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="pb-4 px-6">{{ __('ID') }}</th>
                        <th class="pb-4 px-6">{{ __('Order') }}</th>
                        <th class="pb-4 px-6">{{ __('Profit Amount') }}</th>
                        <th class="pb-4 px-6">{{ __('Percentage') }}</th>
                        <th class="pb-4 px-6">{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6">#{{ $item->id }}</td>
                        <td class="py-4 px-6">
                            @if($item->order)
                            <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-primary hover:underline">#{{ $item->order->order_number ?? $item->order_id }}</a>
                            @else
                            -
                            @endif
                        </td>
                        <td class="py-4 px-6 text-green-600 font-black">{{ number_format($item->profit_amount, 2) }} {{ __('SAR') }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-lg bg-primary/5 text-primary border border-primary/10">
                                {{ $item->percentage ?? 0 }}%
                            </span>
                        </td>
                        <td class="py-4 px-6 opacity-70">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">{{ __('No profit records found') }}</td>
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