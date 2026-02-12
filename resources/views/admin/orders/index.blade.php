@extends('layouts.admin')

@section('title', __('Orders Management'))
@section('page_title', __('Orders Management'))

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Total Orders') }}</p>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</h3>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Scheduled') }}</p>
            <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $stats['scheduled'] }}</h3>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('In Progress') }}</p>
            <h3 class="text-2xl font-black text-orange-600 dark:text-orange-400">{{ $stats['in_progress'] }}</h3>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Completed') }}</p>
            <h3 class="text-2xl font-black text-green-600 dark:text-green-400">{{ $stats['completed'] }}</h3>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-2 p-1 bg-slate-100 dark:bg-white/5 rounded-2xl w-fit">
        @foreach(['all' => 'All', 'new' => 'New', 'scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Rejected'] as $tab => $label)
        <a href="{{ route('admin.orders.index', ['tab' => $tab == 'all' ? null : $tab]) }}" 
           class="px-6 py-2 rounded-xl text-xs font-bold transition-all {{ (request('tab') == $tab || (request('tab') == null && $tab == 'all')) ? 'bg-white dark:bg-white/10 text-primary dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-white/50' }}">
            {{ __($label) }}
        </a>
        @endforeach
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="tab" value="{{ request('tab') }}">
            
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search ID, Name, Phone...') }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            </div>

            <select name="customer_type" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
                <option value="">{{ __('All Customer Types') }}</option>
                <option value="client" {{ request('customer_type') == 'client' ? 'selected' : '' }}>{{ __('Individual') }}</option>
                <option value="corporate" {{ request('customer_type') == 'corporate' ? 'selected' : '' }}>{{ __('Corporate') }}</option>
            </select>

            <select name="sort_by" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
                <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>{{ __('Newest First') }}</option>
                <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>{{ __('Oldest First') }}</option>
                <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>{{ __('Customer Name') }}</option>
            </select>

            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                {{ __('Filter Results') }}
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <th class="pb-4 px-6">{{ __('ID') }}</th>
                        <th class="pb-4 px-6">{{ __('Customer') }}</th>
                        <th class="pb-4 px-6">{{ __('Service') }}</th>
                        <th class="pb-4 px-6">{{ __('Technician') }}</th>
                        <th class="pb-4 px-6">{{ __('Price') }}</th>
                        <th class="pb-4 px-6">{{ __('Status') }}</th>
                        <th class="pb-4 px-6">{{ __('Date') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <td class="py-4 px-6">#{{ $item->id }}</td>
                        <td class="py-4 px-6">
                            <div class="flex flex-col">
                                <span class="text-slate-900 dark:text-white font-black">{{ $item->user->name }}</span>
                                <span class="text-[10px] opacity-70 font-mono">{{ $item->user->phone }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex flex-col">
                                <span>{{ $item->service->name_ar }}</span>
                                <span class="text-[10px] opacity-70 italic">{{ $item->service->parent->name_ar ?? '' }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            @if($item->technician)
                                <span class="text-blue-600 dark:text-blue-400">{{ $item->technician->user->name }}</span>
                            @elseif($item->maintenanceCompany)
                                <span class="text-purple-600 dark:text-purple-400">{{ $item->maintenanceCompany->user->name }}</span>
                            @else
                                <span class="text-slate-400 italic text-[10px]">{{ __('Not Assigned') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-mono text-green-600 dark:text-green-400">{{ number_format($item->total_price, 2) }} {{ __('SAR') }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                @if($item->status == 'new') bg-blue-100 text-blue-600
                                @elseif($item->status == 'scheduled') bg-purple-100 text-purple-600
                                @elseif($item->status == 'in_progress') bg-orange-100 text-orange-600
                                @elseif($item->status == 'completed') bg-green-100 text-green-600
                                @else bg-red-100 text-red-600 @endif">
                                {{ __($item->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6">{{ $item->created_at->format('Y-m-d') }}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.orders.show', $item->id) }}" class="p-2 rounded-lg hover:bg-blue-50 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('admin.orders.edit', $item->id) }}" class="p-2 rounded-lg hover:bg-yellow-50 text-yellow-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">{{ __('No orders found') }}</td>
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