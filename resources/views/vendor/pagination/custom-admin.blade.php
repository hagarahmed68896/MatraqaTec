@if ($paginator->hasPages())
    <div class="flex items-center justify-between px-10 text-slate-400 text-[11px] font-bold w-full">
        <div class="flex items-center gap-4">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-50 flex items-center justify-center text-slate-300 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="w-10 h-10 rounded-xl bg-white border border-slate-50 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                </a>
            @endif

            {{-- Page Counter --}}
            <div class="px-6 py-2 bg-[#1A1A31] text-white rounded-xl shadow-lg">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="w-10 h-10 rounded-xl bg-white border border-slate-50 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </a>
            @else
                <span class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-50 flex items-center justify-center text-slate-300 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </span>
            @endif
        </div>
        
        <div class="flex items-center gap-2">
            <span>{{ __('الصفحة رقم:') }}</span>
            <div class="flex items-center gap-1 text-[#1A1A31] font-black underline decoration-primary decoration-2 underline-offset-4 cursor-default">
                <span>{{ $paginator->currentPage() }}</span>
            </div>
        </div>

        <div class="opacity-60">
            {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} {{ __('من') }} {{ $paginator->total() }}
        </div>
    </div>
@endif
