@extends('layouts.admin')

@section('title', __('Notification Management'))

@section('content')
<div x-data="notificationManagement()" class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700 pb-20" dir="rtl">
    
    <!-- Page Heading -->
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Notification Management') }}</h1>
    </div>

    @php
        $cardStats = [
            ['label' => __('Total Notifications'), 'value' => $statistics['total']['count'], 'percentage' => $statistics['total']['percentage'], 'color' => '#6366f1', 'icon' => 'total'],
            ['label' => __('Sent Notifications'), 'value' => $statistics['sent']['count'], 'percentage' => $statistics['sent']['percentage'], 'color' => '#10B981', 'icon' => 'sent'],
            ['label' => __('Scheduled Notifications'), 'value' => $statistics['scheduled']['count'], 'percentage' => $statistics['scheduled']['percentage'], 'color' => '#3b82f6', 'icon' => 'scheduled'],
            ['label' => __('Unsent Notifications'), 'value' => $statistics['unsent']['count'], 'percentage' => $statistics['unsent']['percentage'], 'color' => '#ef4444', 'icon' => 'unsent'],
        ];
    @endphp

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        @foreach($cardStats as $index => $stat)
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 flex flex-col justify-between h-48 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex items-center justify-between relative z-10">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-[#1A1A31] dark:text-slate-400 opacity-60">{{ $stat['label'] }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ $stat['value'] }}</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold border" 
                              style="background-color: {{ $stat['color'] }}10; color: {{ $stat['color'] }}; border-color: {{ $stat['color'] }}20;">
                            {{ $stat['percentage'] }}%+
                        </span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:scale-110 transition-transform">
                    @if($stat['icon'] == 'total')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    @elseif($stat['icon'] == 'sent')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @elseif($stat['icon'] == 'scheduled')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    @endif
                </div>
            </div>
            
            <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
                <canvas id="sparkline-{{ $index }}" class="w-full h-full"></canvas>
            </div>
        </div>
        @endforeach
    </div>

    <!-- SEARCH & FILTERS AND BULK ACTIONS CONTAINER -->
    <div class="relative">
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-50 dark:border-white/5 shadow-sm p-4 h-20 flex items-center">
            
            <!-- Default Actions (Search, Filter, Add) -->
            <div x-show="selectedRows.length === 0" x-transition.opacity class="flex items-center justify-between w-full h-full">
             

                <!-- Right Side: Search and Filter -->
                <div class="flex items-center gap-3 flex-1 max-w-2xl px-2">
                
                    <div class="relative">
                        <button @click="showFilters = !showFilters" 
                                class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 dark:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        </button>
                        
                        <!-- Filter Dropdown Panel (Unchanged) -->
                        <div x-show="showFilters" @click.away="showFilters = false" x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute top-full right-0 mt-2 w-80 bg-white dark:bg-[#1A1A31] rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-white/10 z-[100] p-8 space-y-6 text-right">
                            <!-- Filter content content ... -->
                            <form action="{{ route('admin.broadcast-notifications.index') }}" method="GET" class="space-y-6">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                
                                <!-- Sort By -->
                                <div class="space-y-4">
                                    <h4 class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Sort by:') }}</h4>
                                    <div class="space-y-3">
                                        @foreach(['newest' => __('All'), 'name' => __('Name'), 'newest_date' => __('Newest'), 'oldest' => __('Oldest')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-sm font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                             <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                                  :class="sortBy == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                                 <input type="radio" name="sort_by" value="{{ $val }}" x-model="sortBy" class="hidden">
                                                 <template x-if="sortBy == '{{ $val }}'">
                                                     <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                 </template>
                                             </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                                <!-- Notification Type -->
                                <div class="space-y-4">
                                    <h4 class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Notification Type:') }}</h4>
                                    <div class="space-y-3">
                                        @foreach(['' => __('All'), 'alert' => __('Alert'), 'reminder' => __('Reminder'), 'notification' => __('Notification')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-sm font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                             <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                                  :class="type == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                                 <input type="radio" name="type" value="{{ $val }}" x-model="type" class="hidden">
                                                 <template x-if="type == '{{ $val }}'">
                                                     <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                 </template>
                                             </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                                <!-- Target Audience -->
                                <div class="space-y-4">
                                    <h4 class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Target Audience:') }}</h4>
                                    <div class="space-y-3">
                                        @foreach(['' => __('All'), 'client' => __('Clients'), 'company' => __('Companies'), 'technician' => __('Technicians')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-sm font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                             <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                                  :class="targetAudience == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                                 <input type="radio" name="target_audience" value="{{ $val }}" x-model="targetAudience" class="hidden">
                                                 <template x-if="targetAudience == '{{ $val }}'">
                                                     <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                 </template>
                                             </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 dark:bg-white/5"></div>

                                <!-- Status -->
                                <div class="space-y-4">
                                    <h4 class="text-sm font-black text-[#1A1A31] dark:text-white">{{ __('Status:') }}</h4>
                                    <div class="space-y-3">
                                        @foreach(['' => __('All'), 'sent' => __('Sent'), 'scheduled' => __('Scheduled'), 'unsent' => __('Unsent')] as $val => $label)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                             <span class="text-sm font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $label }}</span>
                                             <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                                  :class="status == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-200 dark:border-white/10'">
                                                 <input type="radio" name="status" value="{{ $val }}" x-model="status" class="hidden">
                                                 <template x-if="status == '{{ $val }}'">
                                                     <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                 </template>
                                             </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <button type="submit" class="flex-1 py-4 bg-[#1A1A31] text-white rounded-2xl font-black text-sm shadow-xl hover:scale-[1.02] transition-all">
                                        {{ __('Apply') }}
                                    </button>
                                    <button type="button" @click="resetFilters()" class="flex-1 py-4 bg-slate-100 text-[#1A1A31] rounded-2xl font-black text-sm hover:bg-slate-200 transition-all">
                                        {{ __('Reset') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>  
                <div class="flex-1 relative group max-w-md">
                        <form action="{{ route('admin.broadcast-notifications.index') }}" method="GET" id="searchForm">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="w-full h-11 border border-slate-200 dark:border-white/10 rounded-xl px-6 pr-10 bg-white dark:bg-white/5 font-bold text-sm text-[#1A1A31] dark:text-white focus:outline-none focus:ring-1 focus:ring-primary/20 transition-all shadow-sm"
                                   placeholder="{{ __('Search...') }}">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </form>
                    </div>

                </div>

                   <!-- Left Side: Add -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.broadcast-notifications.create') }}" 
                       class="h-11 px-6 flex items-center gap-2 bg-[#1A1A31] text-white rounded-xl font-bold text-sm shadow-lg hover:scale-[1.02] transition-all whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span>{{ __('Add Notification') }}</span>
                    </a>
                </div>
            </div>

            <!-- Bulk Action Bar -->
            <div x-show="selectedRows.length > 0" x-transition.opacity class="flex items-center justify-between w-full h-full bg-slate-50 dark:bg-white/5 rounded-2xl px-6 border-2 border-primary/20">
                <div class="flex items-center gap-4">
                    <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black" x-text="selectedRows.length"></span>
                    <span class="text-sm font-black text-slate-700 dark:text-white">{{ __('Selected') }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <button @click="bulkDelete()" class="h-11 px-6 flex items-center gap-2 bg-red-500 text-white rounded-xl font-bold text-sm shadow-lg shadow-red-500/20 hover:bg-red-600 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span>{{ __('Delete Selected') }}</span>
                    </button>
                    <button @click="selectedRows = []; selectAll = false" class="text-xs font-bold text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section with White Background -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] shadow-sm border border-slate-50 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="py-6 px-8 text-center w-16">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()"
                                   class="w-5 h-5 rounded-lg border-slate-200 text-primary focus:ring-primary/20 transition-all">
                        </th>
                        <th class="py-6 px-4">#</th>
                        <th class="py-6 px-4">{{ __('Notification Type') }}</th>
                        <th class="py-6 px-4">{{ __('Notification Title') }}</th>
                        <th class="py-6 px-4">{{ __('Notification Body') }}</th>
                        <th class="py-6 px-4">{{ __('Target Audience') }}</th>
                        <th class="py-6 px-4">{{ __('Status') }}</th>
                        <th class="py-6 px-4">{{ __('Date') }}</th>
                        <th class="py-6 px-8 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                    @forelse($items as $item)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all" :class="selectedRows.includes('{{ $item->id }}') ? 'bg-primary/5' : ''">
                        <td class="py-5 px-8 text-center">
                            <input type="checkbox" value="{{ $item->id }}" x-model="selectedRows"
                                   class="row-checkbox w-5 h-5 rounded-lg border-slate-200 text-primary focus:ring-primary/20 transition-all">
                        </td>
                        <td class="py-5 px-4 text-xs font-bold text-slate-400">{{ $loop->iteration }}</td>
                        <td class="py-5 px-4 text-xs font-black text-slate-700 dark:text-white uppercase">
                            @if($item->type == 'alert') {{ __('Alert') }} @elseif($item->type == 'reminder') {{ __('Reminder') }} @else {{ __('Notification') }} @endif
                        </td>
                        <td class="py-5 px-4">
                            <h4 class="text-sm font-black text-slate-800 dark:text-white mb-0.5">{{ $item->title_ar }}</h4>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $item->title_en }}</span>
                        </td>
                        <td class="py-5 px-4">
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 line-clamp-1 max-w-[200px]">{{ $item->body_ar }}</p>
                        </td>
                        <td class="py-5 px-4">
                            <span class="px-3 py-1 rounded-full bg-slate-50 dark:bg-white/5 text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider">
                                {{ __($item->target_audience) }}
                            </span>
                        </td>
                        <td class="py-5 px-4">
                            @php
                                $statusClasses = [
                                    'sent' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                    'scheduled' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                    'unsent' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                                ];
                                $statusLabels = [
                                    'sent' => 'مرسل',
                                    'scheduled' => 'مجدول',
                                    'unsent' => 'غير مرسل',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full border {{ $statusClasses[$item->status] ?? 'bg-slate-100 text-slate-500' }} text-[10px] font-black">
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                        </td>
                        <td class="py-5 px-4 text-right">
                            <div class="text-xs font-black text-slate-700 dark:text-white">{{ $item->created_at->format('j/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $item->created_at->format('H:i A') }}</div>
                        </td>
                        <td class="py-5 px-8">
                            <div class="flex items-center justify-center gap-1.5">
                                <button class="w-8 h-8 text-orange-500 flex items-center justify-center   transition-all " title="{{ __('Resend') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                </button>
                                <a href="{{ route('admin.broadcast-notifications.edit', $item->id) }}" class="w-8 h-8  flex items-center justify-center transition-all" title="{{ __('Edit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('admin.broadcast-notifications.destroy', $item->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center transition-all" title="{{ __('Delete') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-20 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="w-20 h-20 rounded-full bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-300 dark:text-slate-600">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-slate-800 dark:text-white">{{ __('No notifications found') }}</h3>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ __('Try adjusting your search or filters.') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-8 border-t border-slate-50 dark:border-white/5 bg-slate-50/30 dark:bg-white/5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400">
                    {{ __('Showing') }} <span class="text-slate-800 dark:text-white">{{ $items->firstItem() ?? 0 }}</span> {{ __('to') }} <span class="text-slate-800 dark:text-white">{{ $items->lastItem() ?? 0 }}</span> {{ __('of') }} <span class="text-slate-800 dark:text-white">{{ $items->total() }}</span> {{ __('results') }}
                </div>
                {{ $items->onEachSide(1)->links('vendor.pagination.custom-admin') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationManagement', () => ({
            showFilters: false,
            sortBy: '{{ request('sort_by', 'newest') }}',
            type: '{{ request('type', '') }}',
            targetAudience: '{{ request('target_audience', '') }}',
            status: '{{ request('status', '') }}',
            selectedRows: [],
            selectAll: false,

            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedRows = Array.from(document.querySelectorAll('.row-checkbox')).map(el => el.value);
                } else {
                    this.selectedRows = [];
                }
            },

            resetFilters() {
                this.sortBy = 'newest';
                this.type = '';
                this.targetAudience = '';
                this.status = '';
                window.location.href = "{{ route('admin.broadcast-notifications.index') }}";
            },

            async bulkDelete() {
                if (!confirm('{{ __("Are you sure you want to delete the selected items?") }}')) return;

                try {
                    const response = await fetch("{{ route('admin.broadcast-notifications.bulk-destroy') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: this.selectedRows })
                    });

                    const result = await response.json();
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.message || 'Error occurred');
                    }
                } catch (error) {
                    console.error('Bulk delete error:', error);
                    alert('An error occurred during bulk deletion');
                }
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
            elements: { point: { radius: 0 } }
        };

        @foreach($cardStats as $index => $stat)
        new Chart(document.getElementById('sparkline-{{ $index }}').getContext('2d'), {
            type: 'line',
            data: {
                labels: Array(10).fill(''),
                datasets: [{
                    data: [{{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}, {{ rand(10, 50) }}],
                    borderColor: '{{ $stat["color"] }}',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, 'white');
                        gradient.addColorStop(1, '{{ $stat["color"] }}20');
                        return gradient;
                    },
                    tension: 0.4
                }]
            },
            options: commonOptions
        });
        @endforeach
    });
</script>
@endsection
