@forelse($recent_orders as $order)
<tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all text-xs font-bold text-slate-600 dark:text-white/70">
    <td class="py-5 px-2 opacity-50">{{ $loop->iteration }}</td>
    <td class="py-5 px-2 text-slate-400">{{ $order->order_number }}</td>
    <td class="py-5 px-2 text-slate-800 dark:text-white">{{ $order->user->name ?? '-' }}</td>
    <td class="py-5 px-2">
        <span class="opacity-70">
            {{ __($order->user->type ?? 'individual') }}
        </span>
    </td>
    <td class="py-5 px-2 font-black text-slate-800 dark:text-white">
        {{ $order->service->parent->{'name_'.app()->getLocale()} ?? $order->service->{'name_'.app()->getLocale()} }}
    </td>
    <td class="py-5 px-2 opacity-70">
        @if($order->service->parent_id)
            {{ $order->service->{'name_'.app()->getLocale()} }}
        @else
            {{ __('General Service') }}
        @endif
    </td>
    <td class="py-5 px-2 font-black text-slate-800 dark:text-white">{{ $order->address ?? __('Address') }}</td>
    <td class="py-5 px-2 opacity-70 tracking-tighter">{{ $order->created_at->format('d/m/Y - H:i') }}</td>
    <td class="py-5 px-2 font-black text-slate-800 dark:text-white flex items-center gap-1">
        {{ number_format($order->total_price) }}
        <img src="{{ asset('assets/images/Vector (1).svg') }}" class="w-4 h-4 opacity-70 filter dark:invert" alt="SAR">
    </td>
    <td class="py-5 px-2">
        <div class="flex items-center justify-center gap-2">
            <!-- Accept/Details Button -->
            <a href="{{ route('admin.orders.show', $order->id) }}" class="w-8 h-8 rounded-full bg-[#1e1b4b] dark:bg-emerald-500 text-white flex items-center justify-center transition-all shadow-lg shadow-indigo-500/20 group/btn border border-white/10" title="{{ __('Accept/Details') }}">
                <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </a>
            <!-- Cancel/Refuse Button -->
            <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="w-8 h-8 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center transition-all shadow-lg shadow-rose-500/30 group/btn border border-rose-400/20" title="{{ __('Refuse/Cancel') }}">
                    <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="py-12 text-center text-slate-400">{{ __('No orders found') }}</td>
</tr>
@endforelse
