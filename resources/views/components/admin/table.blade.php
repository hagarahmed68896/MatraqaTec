@props([
    'columns' => [],
    'items' => null,
    'sortBy' => 'created_at',
    'sortOrder' => 'desc',
    'actions' => true,
    'bulkActions' => false,
    'searchRoute' => null,
])

<div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
    <!-- Table Header with Search -->
    @if($searchRoute)
    <div class="p-6 border-b border-slate-100 dark:border-white/5">
        <form action="{{ $searchRoute }}" method="GET" class="flex items-center gap-4">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 absolute {{ app()->getLocale() == 'ar' ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}" class="w-full {{ app()->getLocale() == 'ar' ? 'pr-12 pl-4' : 'pl-12 pr-4' }} py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            </div>
            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                {{ __('Search') }}
            </button>
        </form>
    </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
            <thead>
                <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                    @if($bulkActions)
                    <th class="pb-4 px-4">
                        <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300">
                    </th>
                    @endif
                    
                    @foreach($columns as $key => $label)
                    <th class="pb-4 px-4">
                        <a href="?sort_by={{ $key }}&sort_order={{ $sortBy === $key && $sortOrder === 'asc' ? 'desc' : 'asc' }}" class="flex items-center gap-2 hover:text-primary transition-colors">
                            {{ $label }}
                            @if($sortBy === $key)
                                <svg class="w-3 h-3 {{ $sortOrder === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"></path></svg>
                            @endif
                        </a>
                    </th>
                    @endforeach

                    @if($actions)
                    <th class="pb-4 px-4 text-center">{{ __('Actions') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($items && $items->hasPages())
    <div class="p-6 border-t border-slate-100 dark:border-white/5">
        {{ $items->links() }}
    </div>
    @endif
</div>

@if($bulkActions)
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
@endif
