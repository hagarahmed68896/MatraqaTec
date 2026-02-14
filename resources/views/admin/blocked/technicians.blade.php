@extends('layouts.admin')

@section('title', __('Blocked Technicians'))

@section('content')
<div class="space-y-6 pb-20" x-data="{ 
    selectedIds: [], 
    
    toggleAll(e) {
        if (e.target.checked) {
            this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(el => el.value);
        } else {
            this.selectedIds = [];
        }
    },

    async unblockSelected() {
        if (!confirm('{{ __('Are you sure you want to unblock the selected technicians?') }}')) return;

        try {
            const response = await fetch('{{ route('admin.blocked.bulk-unblock') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    ids: this.selectedIds,
                    target_type: 'technician'
                })
            });

            const result = await response.json();
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.message || '{{ __('Error occurred') }}');
            }
        } catch (e) {
            console.error(e);
            alert('{{ __('An error occurred while unblocking.') }}');
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-visible">
        <div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">{{ __('Blocked Technicians') }}</h1>
            <p class="text-slate-500 dark:text-slate-400 font-bold text-sm">{{ __('Manage blocked technicians easily.') }}</p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden transition-all duration-500" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <div class="p-8 flex flex-col md:flex-row items-center justify-between gap-6 bg-slate-50/50 dark:bg-white/[0.02] border-b border-slate-100 dark:border-white/5">
            <!-- Search -->
             <div class="flex items-center gap-3 w-full md:w-auto" x-show="selectedIds.length === 0">
                <form action="{{ url()->current() }}" method="GET" class="relative group" x-data="{ search: '{{ request('search') }}' }">
                    <input type="text"
                        name="search"
                        x-model="search"
                        placeholder="{{ __('Search technician by name or phone...') }}"
                        class="w-80 pr-12 pl-4 py-3.5 bg-white dark:bg-[#0F0F1E] border border-slate-200 dark:border-white/10 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder-slate-400 outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all duration-300">

                    <button type="submit"
                        class="absolute {{ app()->getLocale() == 'ar' ? 'right-4' : 'left-auto right-4' }} top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-primary transition-colors duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Bulk Actions Bar -->
            <div x-show="selectedIds.length > 0" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex items-center gap-6 bg-primary/10 px-8 py-3 rounded-2xl border border-primary/20 backdrop-blur-md">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-ping"></span>
                        <span class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center text-sm font-black shadow-lg shadow-primary/30" x-text="selectedIds.length"></span>
                    </div>
                    <span class="text-sm font-black text-primary uppercase tracking-wider">{{ __('Items Selected') }}</span>
                </div>
                <div class="h-10 w-px bg-primary/20"></div>
                <button @click="unblockSelected()" 
                        class="flex items-center gap-3 px-6 py-3 bg-green-500 text-white text-sm font-black rounded-xl hover:bg-green-600 hover:scale-105 active:scale-95 transition-all duration-300 shadow-lg shadow-green-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ __('Unblock Selected') }}
                </button>
            </div>

            <!-- Actions (Download) -->
            <div class="flex items-center gap-3 shrink-0" x-show="selectedIds.length === 0">
                <a href="{{ route('admin.blocked.download', ['target' => 'technicians']) }}" 
                   class="flex items-center gap-3 px-6 py-3.5 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 text-sm font-black rounded-2xl hover:bg-slate-50 dark:hover:bg-white/10 hover:border-primary/30 transition-all duration-300 group">
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V3"/>
                    </svg>
                    {{ __('Export to CSV') }}
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto overflow-y-visible">
             <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                <tr class="text-slate-400 text-[11px] font-black uppercase tracking-[0.2em] border-b border-slate-100 dark:border-white/5 bg-slate-50/30 dark:bg-white/[0.01]">
                    <th class="py-5 px-8 text-center w-16">
                        <input type="checkbox" @change="toggleAll($event)" class="w-5 h-5 border-2 border-slate-200 dark:border-white/10 rounded-lg text-primary focus:ring-primary focus:ring-offset-0 transition-all cursor-pointer">
                    </th>
                    <th class="py-5 px-6">{{ __('Photo') }}</th>
                    <th class="py-5 px-6">{{ __('Technician Name') }}</th>
                    <th class="py-5 px-6">{{ __('Mobile Number') }}</th>
                    <th class="py-5 px-6">{{ __('Email') }}</th>
                    <th class="py-5 px-6">{{ __('Company Name') }}</th>
                    <th class="py-5 px-6">{{ __('Service Name') }}</th>
                    <th class="py-5 px-6">{{ __('Service Type') }}</th>
                    <th class="py-5 px-6 text-center">{{ __('Orders Count') }}</th>
                    <th class="py-5 px-6">{{ __('Date') }}</th>
                    <th class="py-5 px-8 text-center">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody class="text-sm font-bold text-slate-600 dark:text-slate-300">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/[0.02] hover:bg-slate-50/80 dark:hover:bg-white/[0.03] transition-all duration-300 group">
                        <td class="py-6 px-8 text-center">
                            <input type="checkbox" x-model="selectedIds" value="{{ $item->user_id }}" class="row-checkbox w-5 h-5 border-2 border-slate-200 dark:border-white/10 rounded-lg text-primary focus:ring-primary focus:ring-offset-0 transition-all cursor-pointer">
                        </td>
                        <td class="py-6 px-6">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" class="w-10 h-10 rounded-xl object-cover shadow-sm group-hover:scale-110 transition-transform duration-300" alt="{{ $item->name }}">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-black text-md shadow-sm group-hover:scale-110 transition-transform duration-300">
                                    {{ mb_substr($item->name ?? $item->name_ar, 0, 1) }}
                                </div>
                            @endif
                        </td>
                        <td class="py-6 px-6 whitespace-nowrap">
                            <span class="text-slate-900 dark:text-white font-black text-sm">{{ $item->name ?? $item->name_ar }}</span>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">ID: #{{ $item->id }}</span>
                        </td>
                        <td class="py-6 px-6 whitespace-nowrap">
                            <span class="font-mono opacity-80 text-xs" dir="ltr">{{ $item->user->phone ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-6">
                            <span class="opacity-80 text-xs truncate max-w-[120px] inline-block">{{ $item->user->email ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-6 whitespace-nowrap">
                            <span class="text-slate-700 dark:text-slate-300 font-bold text-xs">{{ $item->maintenanceCompany->name ?? __('Independent') }}</span>
                        </td>
                        <td class="py-6 px-6 whitespace-nowrap">
                            <span class="text-xs font-bold text-slate-500">{{ $item->service->name_ar ?? $item->service->name ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-6 whitespace-nowrap">
                            <span class="text-[10px] font-black uppercase bg-primary/5 text-primary px-2 py-0.5 rounded-lg">{{ $item->category->name_ar ?? $item->category->name ?? '-' }}</span>
                        </td>
                        <td class="py-6 px-6 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-slate-100 dark:bg-white/5 text-slate-700 dark:text-slate-400 text-xs font-black">
                                {{ $item->order_count ?? 0 }}
                            </span>
                        </td>
                        <td class="py-6 px-6 whitespace-nowrap">
                            <span class="text-xs text-slate-400 font-bold">{{ $item->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="py-6 px-8 text-center">
                            <form action="{{ route('admin.blocked.bulk-unblock') }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to unblock this technician?') }}')">
                                @csrf
                                <input type="hidden" name="ids[]" value="{{ $item->user_id }}">
                                <input type="hidden" name="target_type" value="technician">
                                <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center text-green-500 hover:bg-green-500 hover:text-white dark:hover:bg-green-500/20 dark:hover:text-green-400 transition-all duration-300 shadow-sm" title="{{ __('Unblock') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-24 text-center">
                             <div class="flex flex-col items-center max-w-sm mx-auto">
                                <div class="w-24 h-24 bg-slate-50 dark:bg-white/5 rounded-[2.5rem] flex items-center justify-center mb-6 text-slate-200 dark:text-slate-700 transform rotate-[15deg]">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <h3 class="font-black text-xl text-slate-800 dark:text-white mb-2">{{ __('No blocked technicians found') }}</h3>
                                <p class="text-slate-400 font-bold text-sm">{{ __('When a technician is blocked, they will appear in this list.') }}</p>
                             </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-8 border-t border-slate-100 dark:border-white/5 bg-slate-50/30 dark:bg-white/[0.01]">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
