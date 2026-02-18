@extends('layouts.admin')

@section('title', __('Contract Receipts'))
@section('page_title', __('Contract Receipts'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('Payment Documentation') }}</h2>
            <p class="text-sm text-slate-500 dark:text-white/50">{{ __('Proof of payments for active corporate contracts') }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.contract-receipts.index') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by contract or reference #...') }}" class="flex-1 px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            <button type="submit" class="px-8 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                {{ __('Find') }}
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="pb-4 px-6">{{ __('Date') }}</th>
                        <th class="pb-4 px-6">{{ __('Contract') }}</th>
                        <th class="pb-4 px-6">{{ __('Method') }}</th>
                        <th class="pb-4 px-6">{{ __('Reference') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Receipt') }}</th>
                        <th class="pb-4 px-6">{{ __('Amount') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6">{{ $item->payment_date }}</td>
                        <td class="py-4 px-6 text-primary">
                            @if($item->contract)
                            <a href="{{ route('admin.contracts.show', $item->contract_id) }}" class="hover:underline">#{{ $item->contract->contract_number }}</a>
                            @endif
                        </td>
                        <td class="py-4 px-6 uppercase text-[10px]">{{ __($item->payment_method) }}</td>
                        <td class="py-4 px-6 opacity-70">{{ $item->reference_number ?? '---' }}</td>
                        <td class="py-4 px-6 text-center">
                            <a href="{{ asset('storage/' . $item->receipt_file) }}" target="_blank" class="inline-flex items-center gap-1 text-primary hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                {{ __('View') }}
                            </a>
                        </td>
                        <td class="py-4 px-6 text-green-500 font-black">+{{ number_format($item->amount, 2) }} <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="inline-block w-4 h-4 align-middle"></td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('admin.contract-receipts.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Delete receipt?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">{{ __('No receipts found') }}</td>
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
