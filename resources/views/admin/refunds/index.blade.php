@extends('layouts.admin')

@section('title', __('Returns'))

@section('content')
<div class="space-y-8" x-data="returnManagement">

    <!-- HEADER SECTION -->
    <div class="flex items-end justify-between">
        <div class="space-y-2">
            <h1 class="text-[2.5rem] font-black text-[#1A1A31] dark:text-white leading-none">{{ __('Returns') }}</h1>
        </div>

        <div class="flex bg-white dark:bg-[#1A1A31] p-1.5 rounded-2xl shadow-sm border border-slate-50 dark:border-white/5">
            <a href="{{ route('admin.refunds.index', ['status' => 'pending']) }}" 
               class="px-8 py-3 rounded-xl text-xs font-black transition-all {{ request('status', 'pending') == 'pending' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-400 hover:text-slate-600 dark:hover:text-white' }}">
                {{ __('Return Requests') }}
            </a>
            <a href="{{ route('admin.refunds.index', ['status' => 'history']) }}" 
               class="px-8 py-3 rounded-xl text-xs font-black transition-all {{ request('status') == 'history' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-400 hover:text-slate-600 dark:hover:text-white' }}">
                {{ __('Return History') }}
            </a>
        </div>
    </div>

    <!-- CONTROLS CONTAINER -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-50 dark:border-white/5 shadow-sm p-4">
        <div class="flex items-center justify-between {{ app()->getLocale() == 'ar' ? 'flex-row-reverse' : 'flex-row' }}">
           
           <!-- Right Side: Filter+Search OR Selection Bar -->
            <div class="flex items-center gap-3 flex-1 max-w-2xl px-2">

                {{-- When NO rows selected: show filter + search --}}
                <template x-if="selectedRows.length === 0">
                    <div class="flex items-center gap-3 flex-1">
                        {{-- Filter Button --}}
                        <div class="relative">
                            <button @click="showFilters = !showFilters"
                                    class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 dark:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            </button>

                            <!-- Filter Dropdown Panel -->
                            <div x-show="showFilters" @click.away="showFilters = false" x-cloak
                                 class="absolute {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} top-full mt-2 w-64 bg-white dark:bg-[#1A1A31] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/10 z-[100] overflow-hidden"
                                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                                <form action="{{ route('admin.refunds.index') }}" method="GET" class="p-5 space-y-5">
                                    <input type="hidden" name="status" value="{{ request('status', 'pending') }}">
                                    <input type="hidden" name="search" value="{{ request('search') }}">

                                    {{-- Sort By --}}
                                    <div>
                                        <p class="text-[11px] font-black text-[#1A1A31] dark:text-white mb-4">{{ __('Sort By') }}:</p>
                                        <div class="space-y-3.5">
                                            @foreach(['' => __('All'), 'name' => __('Name'), 'newest' => __('Newest First'), 'oldest' => __('Oldest First')] as $val => $label)
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="radio" name="sort_by" value="{{ $val }}"
                                                       {{ request('sort_by', '') == $val ? 'checked' : '' }}
                                                       class="sr-only filter-radio">
                                                <span class="radio-circle"></span>
                                                <span class="text-xs font-bold text-slate-500 dark:text-slate-300 group-hover:text-[#1A1A31] dark:group-hover:text-white transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="h-px bg-slate-100 dark:bg-white/5"></div>

                                    {{-- Customer Type --}}
                                    <div>
                                        <p class="text-[11px] font-black text-[#1A1A31] dark:text-white mb-4">{{ __('Customer Type') }}:</p>
                                        <div class="space-y-3.5">
                                            @foreach(['' => __('All'), 'individual' => __('Individual'), 'company' => __('Company')] as $val => $label)
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="radio" name="client_type" value="{{ $val }}"
                                                       {{ request('client_type', '') == $val ? 'checked' : '' }}
                                                       class="sr-only filter-radio">
                                                <span class="radio-circle"></span>
                                                <span class="text-xs font-bold text-slate-500 dark:text-slate-300 group-hover:text-[#1A1A31] dark:group-hover:text-white transition-colors">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Buttons --}}
                                    <div class="flex items-center gap-2 pt-1">
                                        <button type="submit" class="flex-1 h-10 bg-[#1A1A31] dark:bg-white dark:text-[#1A1A31] text-white rounded-xl text-xs font-black hover:opacity-90 transition-all">{{ __('Apply') }}</button>
                                        <a href="{{ route('admin.refunds.index', ['status' => request('status', 'pending')]) }}"
                                           class="flex-1 h-10 flex items-center justify-center bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-300 rounded-xl text-xs font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all">{{ __('Reset') }}</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Search --}}
                        <div class="flex-1 relative group">
                            <form action="{{ route('admin.refunds.index') }}" method="GET" id="searchForm">
                                <input type="hidden" name="status" value="{{ request('status', 'pending') }}">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="w-full h-11 {{ app()->getLocale() == 'ar' ? 'pr-12 pl-6' : 'pl-12 pr-6' }} bg-slate-50 dark:bg-white/5 border border-transparent focus:border-slate-100 dark:focus:border-white/10 rounded-xl transition-all font-bold text-xs text-[#1A1A31] dark:text-white placeholder:text-slate-300"
                                       placeholder="{{ __('Search...') }}">
                                <div class="absolute {{ app()->getLocale() == 'ar' ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>

                {{-- When rows ARE selected: show count + return button --}}
                <template x-if="selectedRows.length > 0">
                    <div class="flex items-center gap-4 flex-1"
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                        {{-- Count Badge --}}
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] flex items-center justify-center text-xs font-black" x-text="selectedRows.length"></div>
                            <span class="text-xs font-black text-[#1A1A31] dark:text-white">{{ __('selected') }}</span>
                        </div>


                        @if(request('status', 'pending') == 'pending')
                        {{-- Bulk Return (Transferred) --}}
                        <form action="{{ route('admin.refunds.bulk-status') }}" method="POST" @submit.prevent="bulkAction($event, 'transferred')">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2 h-9 px-5 bg-emerald-500 hover:bg-emerald-600  rounded-xl text-xs font-black transition-all shadow-sm shadow-emerald-500/20">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M21.5459 16.2998C21.9323 16.1512 22.3658 16.3442 22.5146 16.7305C22.6667 17.1258 22.75 17.5543 22.75 18C22.75 19.9884 21.1133 21.5781 19.1221 21.5781H17.2871C17.4789 21.8597 17.4593 22.2463 17.2188 22.5078C16.9383 22.8125 16.4639 22.8321 16.1592 22.5518L15.3359 21.7939C15.3301 21.7885 15.3241 21.7829 15.3184 21.7773C15.3127 21.7717 15.306 21.7658 15.2998 21.7598C15.228 21.6894 15.1254 21.5877 15.0508 21.4902C14.9892 21.4098 14.728 21.0568 14.915 20.6123C15.0996 20.1743 15.5277 20.1084 15.626 20.0947C15.7471 20.0779 15.8915 20.078 15.9932 20.0781H19.1221C20.3095 20.0781 21.25 19.1356 21.25 18C21.25 17.8061 21.2231 17.6193 21.1729 17.4424L21.0908 17.1963C20.991 16.8296 21.1835 16.4392 21.5459 16.2998ZM17 1.25C18.1198 1.25 19.191 1.37292 20.168 1.59668C20.1873 1.6011 20.2065 1.60502 20.2256 1.60938C20.7724 1.73428 21.218 1.83657 21.7061 2.22266C21.9981 2.45374 22.3233 2.86101 22.4834 3.19727C22.7516 3.76074 22.7507 4.3186 22.75 5.00879V11.5C22.75 11.9142 22.4142 12.25 22 12.25C21.5858 12.2499 21.25 11.9142 21.25 11.5V5.11426C21.25 4.27178 21.2354 4.06557 21.1289 3.8418C21.0675 3.71282 20.8874 3.48801 20.7754 3.39941C20.5724 3.23882 20.4413 3.19793 19.833 3.05859C18.9706 2.86106 18.012 2.75 17 2.75C15.1744 2.75002 13.5352 3.1109 12.3174 3.67969C10.8702 4.35556 9.00842 4.75 7 4.75C5.8803 4.74999 4.80984 4.62704 3.83301 4.40332C3.23951 4.26738 2.75 4.71179 2.75 5.11426V15.8857C2.75 16.7282 2.76459 16.9344 2.87109 17.1582C2.93245 17.2871 3.11261 17.5119 3.22461 17.6006C3.42761 17.7612 3.55938 17.802 4.16797 17.9414C5.03028 18.1389 5.98818 18.25 7 18.25C8.39595 18.25 9.68693 18.0389 10.7646 17.6826C11.1579 17.5526 11.5818 17.766 11.7119 18.1592C11.8419 18.5525 11.6286 18.9764 11.2354 19.1064C9.99276 19.5173 8.54167 19.75 7 19.75C5.8803 19.75 4.80984 19.627 3.83301 19.4033C3.81347 19.3988 3.79369 19.395 3.77441 19.3906C3.22757 19.2657 2.78207 19.1635 2.29395 18.7773C2.0019 18.5463 1.67665 18.139 1.5166 17.8027C1.24846 17.2393 1.24933 16.6814 1.25 15.9912V5.11426C1.25 3.54707 2.84152 2.63759 4.16797 2.94141C5.03028 3.13889 5.98818 3.24999 7 3.25C8.82562 3.25 10.4648 2.8891 11.6826 2.32031C13.1298 1.64441 14.9916 1.25002 17 1.25ZM18.7812 13.4922C19.0618 13.1875 19.5371 13.1677 19.8418 13.4482L20.665 14.2061C20.671 14.2115 20.6769 14.217 20.6826 14.2227C20.6883 14.2282 20.694 14.2342 20.7002 14.2402C20.772 14.3106 20.8756 14.4123 20.9502 14.5098C21.0122 14.5908 21.2718 14.9437 21.085 15.3877C20.9006 15.825 20.4742 15.8915 20.375 15.9053C20.254 15.9221 20.1095 15.922 20.0078 15.9219H16.8779C15.6905 15.9219 14.75 16.8644 14.75 18C14.75 18.1939 14.7769 18.3807 14.8271 18.5576L14.9092 18.8037C15.0091 19.1705 14.8165 19.5608 14.4541 19.7002C14.0677 19.8486 13.6341 19.6558 13.4854 19.2695C13.3333 18.8742 13.25 18.4457 13.25 18C13.25 16.0116 14.8867 14.4219 16.8779 14.4219H18.7129C18.5211 14.1403 18.5408 13.7537 18.7812 13.4922ZM12 7.25C13.7949 7.25 15.25 8.70507 15.25 10.5C15.25 12.2949 13.7949 13.75 12 13.75C10.2051 13.7499 8.75 12.2949 8.75 10.5C8.75 8.70512 10.2051 7.25007 12 7.25ZM5.5 10.5C6.05228 10.5 6.5 10.9477 6.5 11.5V11.5088C6.5 12.0611 6.05228 12.5088 5.5 12.5088C4.94778 12.5087 4.5 12.061 4.5 11.5088V11.5C4.5 10.9478 4.94778 10.5001 5.5 10.5ZM12 8.75C11.0336 8.75007 10.25 9.53355 10.25 10.5C10.25 11.4665 11.0336 12.2499 12 12.25C12.9665 12.25 13.75 11.4665 13.75 10.5C13.75 9.5335 12.9665 8.75 12 8.75ZM18.5 8.49219C19.0523 8.49219 19.5 8.9399 19.5 9.49219V9.50098C19.5 10.0533 19.0523 10.501 18.5 10.501C17.9478 10.5009 17.5 10.0532 17.5 9.50098V9.49219C17.5 8.93995 17.9478 8.49226 18.5 8.49219Z"/></svg>
                            </button>
                        </form>
                        @endif

                    </div>
                </template>

            </div>

            <!-- Left Side: Smart Download -->
            <div class="flex items-center gap-3">
                <button @click="selectedRows.length > 0 ? downloadSelected() : window.location.href='{{ route('admin.refunds.download', request()->query()) }}'"
                        class="h-11 px-6 flex items-center gap-3 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 text-[#1A1A31] dark:text-white rounded-xl font-bold text-xs hover:bg-slate-50 dark:hover:bg-white/10 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span x-text="selectedRows.length > 0 ? '{{ __('Download Selected') }}' : '{{ __('Download') }}'"></span>
                </button>
            </div>

         
        </div>
    </div>



    <!-- DATA TABLE -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] border-b border-slate-50 dark:border-white/5">
                        <th class="py-8 px-6">
                            <input type="checkbox" 
                                   @change="toggleAll($event.target.checked)"
                                   :checked="selectedRows.length === {{ $items->count() }} && {{ $items->count() }} > 0"
                                   class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary bg-transparent cursor-pointer">
                        </th>
                        <th class="py-8 px-4">#</th>
                        <th class="py-8 px-4">{{ __('Transaction Number') }}</th>
                        <th class="py-8 px-4">{{ __('Customer Name') }}</th>
                        <th class="py-8 px-4">{{ __('Customer Type') }}</th>
                        <th class="py-8 px-4">{{ __('Order Number') }}</th>
                        <th class="py-8 px-4">{{ __('Amount') }}</th>
                        @if(request('status', 'pending') != 'pending')
                        <th class="py-8 px-4">{{ __('Status') }}</th>
                        @endif
                        <th class="py-8 px-4">{{ __('Date') }}</th>
                        @if(request('status', 'pending') == 'pending')
                        <th class="py-8 px-10 text-center">{{ __('Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                    @forelse($items as $index => $item)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all" :class="selectedRows.includes({{ $item->id }}) ? 'bg-primary/5 dark:bg-primary/10' : ''">
                        <td class="py-8 px-6">
                            <input type="checkbox" 
                                   value="{{ $item->id }}"
                                   @change="toggleRow({{ $item->id }})"
                                   :checked="selectedRows.includes({{ $item->id }})"
                                   class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary bg-transparent cursor-pointer">
                        </td>
                        <td class="py-8 px-4 text-xs font-black text-slate-400 group-hover:text-primary transition-colors">
                            {{ ($items->currentPage() - 1) * $items->perPage() + $index + 1 }}
                        </td>
                        <td class="py-8 px-4">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-black text-[#1A1A31] dark:text-white">{{ __('Operation - #') }}{{ $item->id }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $item->refund_number }}</span>
                            </div>
                        </td>
                        <td class="py-8 px-4">
                            <span class="text-xs font-black text-[#1A1A31] dark:text-white">
                                @if($item->order && $item->order->maintenanceCompany)
                                    {{ $item->order->maintenanceCompany->company_name_ar ?? $item->order->maintenanceCompany->company_name_en }}
                                @elseif($item->order && $item->order->user)
                                    {{ $item->order->user->name }}
                                @else
                                    <span class="text-slate-300 font-bold italic">{{ __('Unknown') }}</span>
                                @endif
                            </span>
                        </td>
                        <td class="py-8 px-4">
                            @php
                                $cType = 'individual';
                                if($item->order && $item->order->maintenanceCompany) $cType = 'company';
                                elseif($item->order && $item->order->user) $cType = $item->order->user->type;
                            @endphp
                            <span class="px-3 py-1.5 bg-slate-50 dark:bg-white/5 rounded-lg text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap">
                                {{ $cType == 'maintenance_company' || $cType == 'company' ? __('Company') : __('Individual') }}
                            </span>
                        </td>
                        <td class="py-8 px-4">
                            <div class="flex flex-col gap-0.5">
                                @if($item->order)
                                    <span class="text-xs font-black text-[#1A1A31] dark:text-white">{{ __('Order') }} #{{ $item->order->order_number }}</span>
                                @else
                                    <span class="text-slate-300 text-xs font-bold italic">{{ __('N/A') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-8 px-4">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-black text-[#1A1A31] dark:text-white">{{ number_format($item->amount, 2) }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">{!! __('SAR') !!}</span>
                            </div>
                        </td>
                        @if(request('status', 'pending') != 'pending')
                        <td class="py-8 px-4">
                            @php
                                $statusColors = [
                                    'pending'     => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
                                    'transferred' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
                                    'rejected'    => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
                                ];
                                $statusLabels = [
                                    'pending'     => __('Pending'),
                                    'transferred' => __('Transferred'),
                                    'rejected'    => __('Rejected'),
                                ];
                                $color = $statusColors[$item->status] ?? 'bg-slate-50 text-slate-400';
                                $label = $statusLabels[$item->status] ?? $item->status;
                            @endphp
                            <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest {{ $color }}">{{ $label }}</span>
                        </td>
                        @endif
                        <td class="py-8 px-4">
                            <span class="text-xs font-bold text-slate-400">{{ $item->created_at->format('Y/m/d') }}</span>
                        </td>
                        @if(request('status', 'pending') == 'pending')
                        <td class="py-8 px-6">
                            <div class="flex items-center justify-center">
                                @if($item->status == 'pending')
                                <form action="{{ route('admin.refunds.change-status', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="transferred">
                                    <button type="submit" title="{{ __('Mark Transferred') }}"
                                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 hover:shadow-sm transition-all group/btn">
                                        <svg class="w-5 h-5 transition-transform group-hover/btn:scale-110" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M21.5459 16.2998C21.9323 16.1512 22.3658 16.3442 22.5146 16.7305C22.6667 17.1258 22.75 17.5543 22.75 18C22.75 19.9884 21.1133 21.5781 19.1221 21.5781H17.2871C17.4789 21.8597 17.4593 22.2463 17.2188 22.5078C16.9383 22.8125 16.4639 22.8321 16.1592 22.5518L15.3359 21.7939C15.3301 21.7885 15.3241 21.7829 15.3184 21.7773C15.3127 21.7717 15.306 21.7658 15.2998 21.7598C15.228 21.6894 15.1254 21.5877 15.0508 21.4902C14.9892 21.4098 14.728 21.0568 14.915 20.6123C15.0996 20.1743 15.5277 20.1084 15.626 20.0947C15.7471 20.0779 15.8915 20.078 15.9932 20.0781H19.1221C20.3095 20.0781 21.25 19.1356 21.25 18C21.25 17.8061 21.2231 17.6193 21.1729 17.4424L21.0908 17.1963C20.991 16.8296 21.1835 16.4392 21.5459 16.2998ZM17 1.25C18.1198 1.25 19.191 1.37292 20.168 1.59668C20.1873 1.6011 20.2065 1.60502 20.2256 1.60938C20.7724 1.73428 21.218 1.83657 21.7061 2.22266C21.9981 2.45374 22.3233 2.86101 22.4834 3.19727C22.7516 3.76074 22.7507 4.3186 22.75 5.00879V11.5C22.75 11.9142 22.4142 12.25 22 12.25C21.5858 12.2499 21.25 11.9142 21.25 11.5V5.11426C21.25 4.27178 21.2354 4.06557 21.1289 3.8418C21.0675 3.71282 20.8874 3.48801 20.7754 3.39941C20.5724 3.23882 20.4413 3.19793 19.833 3.05859C18.9706 2.86106 18.012 2.75 17 2.75C15.1744 2.75002 13.5352 3.1109 12.3174 3.67969C10.8702 4.35556 9.00842 4.75 7 4.75C5.8803 4.74999 4.80984 4.62704 3.83301 4.40332C3.23951 4.26738 2.75 4.71179 2.75 5.11426V15.8857C2.75 16.7282 2.76459 16.9344 2.87109 17.1582C2.93245 17.2871 3.11261 17.5119 3.22461 17.6006C3.42761 17.7612 3.55938 17.802 4.16797 17.9414C5.03028 18.1389 5.98818 18.25 7 18.25C8.39595 18.25 9.68693 18.0389 10.7646 17.6826C11.1579 17.5526 11.5818 17.766 11.7119 18.1592C11.8419 18.5525 11.6286 18.9764 11.2354 19.1064C9.99276 19.5173 8.54167 19.75 7 19.75C5.8803 19.75 4.80984 19.627 3.83301 19.4033C3.81347 19.3988 3.79369 19.395 3.77441 19.3906C3.22757 19.2657 2.78207 19.1635 2.29395 18.7773C2.0019 18.5463 1.67665 18.139 1.5166 17.8027C1.24846 17.2393 1.24933 16.6814 1.25 15.9912V5.11426C1.25 3.54707 2.84152 2.63759 4.16797 2.94141C5.03028 3.13889 5.98818 3.24999 7 3.25C8.82562 3.25 10.4648 2.8891 11.6826 2.32031C13.1298 1.64441 14.9916 1.25002 17 1.25ZM18.7812 13.4922C19.0618 13.1875 19.5371 13.1677 19.8418 13.4482L20.665 14.2061C20.671 14.2115 20.6769 14.217 20.6826 14.2227C20.6883 14.2282 20.694 14.2342 20.7002 14.2402C20.772 14.3106 20.8756 14.4123 20.9502 14.5098C21.0122 14.5908 21.2718 14.9437 21.085 15.3877C20.9006 15.825 20.4742 15.8915 20.375 15.9053C20.254 15.9221 20.1095 15.922 20.0078 15.9219H16.8779C15.6905 15.9219 14.75 16.8644 14.75 18C14.75 18.1939 14.7769 18.3807 14.8271 18.5576L14.9092 18.8037C15.0091 19.1705 14.8165 19.5608 14.4541 19.7002C14.0677 19.8486 13.6341 19.6558 13.4854 19.2695C13.3333 18.8742 13.25 18.4457 13.25 18C13.25 16.0116 14.8867 14.4219 16.8779 14.4219H18.7129C18.5211 14.1403 18.5408 13.7537 18.7812 13.4922ZM12 7.25C13.7949 7.25 15.25 8.70507 15.25 10.5C15.25 12.2949 13.7949 13.75 12 13.75C10.2051 13.7499 8.75 12.2949 8.75 10.5C8.75 8.70512 10.2051 7.25007 12 7.25ZM5.5 10.5C6.05228 10.5 6.5 10.9477 6.5 11.5V11.5088C6.5 12.0611 6.05228 12.5088 5.5 12.5088C4.94778 12.5087 4.5 12.061 4.5 11.5088V11.5C4.5 10.9478 4.94778 10.5001 5.5 10.5ZM12 8.75C11.0336 8.75007 10.25 9.53355 10.25 10.5C10.25 11.4665 11.0336 12.2499 12 12.25C12.9665 12.25 13.75 11.4665 13.75 10.5C13.75 9.5335 12.9665 8.75 12 8.75ZM18.5 8.49219C19.0523 8.49219 19.5 8.9399 19.5 9.49219V9.50098C19.5 10.0533 19.0523 10.501 18.5 10.501C17.9478 10.5009 17.5 10.0532 17.5 9.50098V9.49219C17.5 8.93995 17.9478 8.49226 18.5 8.49219Z"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-32">
                            <div class="flex flex-col items-center justify-center space-y-4 opacity-20">
                                <div class="w-20 h-20 rounded-[2.5rem] bg-slate-100 dark:bg-white/5 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ __('No returns found') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FOOTER PAGINATION -->
        @if($items->hasPages() || $items->total() > 0)
        <div class="p-8 bg-slate-50/30 dark:bg-white/5 border-t border-slate-50 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400">{{ __('Rows per page:') }}</span>
                    <div class="relative" x-data="{ open: false, limit: {{ request('limit', 10) }} }">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-lg text-xs font-black text-[#1A1A31] dark:text-white shadow-sm">
                            <span x-text="limit"></span>
                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute bottom-full mb-2 left-0 w-20 bg-white dark:bg-[#1A1A31] rounded-xl shadow-xl border border-slate-100 dark:border-white/10 z-[120] py-2">
                            @foreach([10, 25, 50, 100] as $l)
                            <a href="{{ request()->fullUrlWithQuery(['limit' => $l]) }}" class="block px-4 py-2 text-xs font-black hover:bg-slate-50 dark:hover:bg-white/5 {{ request('limit', 10) == $l ? 'text-primary' : 'text-slate-400' }}">{{ $l }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">
                    {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} {{ __('of') }} {{ $items->total() }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($items->onFirstPage())
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-white/10 cursor-not-allowed" disabled>
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                @else
                    <a href="{{ $items->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 hover:text-primary hover:border-primary transition-all shadow-sm">
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @endif

                @foreach($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $page => $url)
                    @if($page == $items->currentPage())
                        <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1A1A31] text-white text-xs font-black shadow-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 hover:text-primary hover:border-primary transition-all shadow-sm text-xs font-black">{{ $page }}</a>
                    @endif
                @endforeach

                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-slate-400 hover:text-primary hover:border-primary transition-all shadow-sm">
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @else
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-300 dark:text-white/10 cursor-not-allowed" disabled>
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@section('styles')
<style>
    /* Custom radio button: show checkmark inside when checked */
    .radio-circle {
        position: relative;
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 9999px;
        border: 2px solid #e2e8f0;
        background: transparent;
        flex-shrink: 0;
        transition: border-color 0.15s, background-color 0.15s;
    }
    .radio-circle::after {
        content: '';
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -55%) rotate(45deg);
        width: 5px;
        height: 9px;
        border-right: 2.5px solid white;
        border-bottom: 2.5px solid white;
    }
    input[type="radio"].filter-radio:checked + .radio-circle {
        background-color: #10b981;
        border-color: #10b981;
    }
    input[type="radio"].filter-radio:checked + .radio-circle::after {
        display: block;
    }
    .dark input[type="radio"].filter-radio:not(:checked) + .radio-circle {
        border-color: rgba(255,255,255,0.2);
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('returnManagement', () => ({
            showFilters: false,
            sortBy: '{{ request('sort_by', '') }}',
            clientType: '{{ request('client_type', '') }}',
            selectedRows: [],

            toggleRow(id) {
                if (this.selectedRows.includes(id)) {
                    this.selectedRows = this.selectedRows.filter(r => r !== id);
                } else {
                    this.selectedRows.push(id);
                }
            },

            toggleAll(checked) {
                if (checked) {
                    this.selectedRows = [{{ $items->pluck('id')->join(', ') }}];
                } else {
                    this.selectedRows = [];
                }
            },

            downloadSelected() {
                const ids = this.selectedRows.join(',');
                const url = '{{ route('admin.refunds.download') }}?ids=' + ids;
                window.location.href = url;
            },

            bulkAction(event, status) {
                const form = event.target;
                const idsInput = document.createElement('input');
                idsInput.type = 'hidden';
                idsInput.name = 'status';
                idsInput.value = status;

                const idsArray = this.selectedRows.map(id => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'ids[]';
                    inp.value = id;
                    return inp;
                });

                idsArray.forEach(inp => form.appendChild(inp));
                form.submit();
            },

            resetFilters() {
                this.sortBy = '';
                this.clientType = '';
                window.location.href = "{{ route('admin.refunds.index', ['status' => request('status', 'pending')]) }}";
            }
        }));
    });
</script>
@endsection
@endsection