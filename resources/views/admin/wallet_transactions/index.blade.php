@extends('layouts.admin')

@section('title', __('Wallet Transactions'))
@section('page_title', __('Wallet Transactions'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('Financial Wallets') }}</h2>
            <p class="text-sm text-slate-500 dark:text-white/50">{{ __('Monitor and manage digital wallet balances for all users') }}</p>
        </div>
        <a href="{{ route('admin.wallet-transactions.create') }}" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            {{ __('Adjust Balance') }}
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.wallet-transactions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by user or note...') }}" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            
            <select name="type" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
                <option value="">{{ __('All Types') }}</option>
                <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>{{ __('Deposit') }}</option>
                <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>{{ __('Debit') }}</option>
                <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>{{ __('Refund') }}</option>
                <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>{{ __('Payment') }}</option>
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
                        <th class="pb-4 px-6">{{ __('Date') }}</th>
                        <th class="pb-4 px-6">{{ __('User') }}</th>
                        <th class="pb-4 px-6">{{ __('Type') }}</th>
                        <th class="pb-4 px-6">{{ __('Amount') }}</th>
                        <th class="pb-4 px-6">{{ __('Note') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6 opacity-70">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                        <td class="py-4 px-6 text-slate-900 dark:text-white">
                            @if($item->user)
                            <span class="block">{{ $item->user->name }}</span>
                            <span class="text-[9px] opacity-70">{{ __($item->user->type) }}</span>
                            @else
                            <span class="text-slate-400 font-normal italic">{{ __('Deleted User') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-lg text-[10px] uppercase font-black
                                {{ in_array($item->type, ['deposit', 'refund']) ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ __($item->type) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-black {{ in_array($item->type, ['deposit', 'refund']) ? 'text-green-600' : 'text-red-600' }}">
                            {{ in_array($item->type, ['deposit', 'refund']) ? '+' : '-' }}{{ number_format($item->amount, 2) }} {{ __('SAR') }}
                        </td>
                        <td class="py-4 px-6 opacity-70">{{ $item->note ?? '---' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">{{ __('No transactions found') }}</td>
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
