@extends('layouts.admin')

@section('title', __('Technicians Management'))
@section('page_title', __('Technicians Management'))

@section('content')
<div x-data="{ viewMode: 'grid' }" class="space-y-6 pb-20">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('All Technicians') }}</h2>
            <p class="text-sm text-slate-500 dark:text-white/50">{{ __('View and manage all technicians in the system') }}</p>
        </div>
        <div class="flex items-center gap-4">
            <!-- View Mode Toggle -->
            <div class="flex items-center p-1 bg-white dark:bg-white/5 rounded-xl border border-slate-100 dark:border-white/10 shadow-sm">
                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600'" class="p-2.5 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                </button>
                <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600'" class="p-2.5 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
            <a href="{{ route('admin.technicians.create') }}" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all flex items-center gap-2 shadow-lg shadow-primary/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('Add Technician') }}
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.technicians.index') }}" method="GET" class="flex gap-4">
            <div class="flex-1 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by name, phone, email...') }}" class="w-full pr-12 pl-5 py-3.5 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary dark:text-white font-bold transition-all text-sm">
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            <button type="submit" class="px-8 py-3.5 bg-slate-900 dark:bg-primary text-white rounded-xl font-bold hover:opacity-90 transition-all shadow-sm text-sm">
                {{ __('Search') }}
            </button>
        </form>
    </div>

    <!-- Grid View -->
    <div x-show="viewMode === 'grid'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($items as $item)
        <div onclick="window.location='{{ route('admin.technicians.show', $item->id) }}'" class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group cursor-pointer">
            <div class="flex items-start justify-between mb-6">
                <div class="relative">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-white font-black overflow-hidden ring-4 ring-slate-50 dark:ring-white/5 group-hover:scale-105 transition-transform shadow-lg shadow-primary/20">
                        @if($item->image)
                            <img src="{{ asset('storage/'.$item->image) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl">{{ mb_substr($item->name_ar, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-[#1A1A31] {{ $item->availability_status === 'available' ? 'bg-green-500' : 'bg-red-500' }} animate-pulse"></div>
                </div>
                <span class="px-3 py-1 rounded-full {{ $item->availability_status === 'available' ? 'bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400' }} text-[10px] font-black uppercase tracking-wide">
                    {{ $item->availability_status === 'available' ? __('Available') : __('Unavailable') }}
                </span>
            </div>

            <div class="space-y-4 mb-6">
                <div>
                    <h3 class="text-base font-black text-slate-800 dark:text-white group-hover:text-primary transition-colors text-right mb-1">{{ $item->name_ar }}</h3>
                    <p class="text-[10px] text-slate-400 font-bold text-right">{{ $item->category->name_ar ?? __('Specialized Tech') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 dark:border-white/5">
                    <div class="text-right">
                        <p class="text-[9px] text-slate-400 font-bold uppercase mb-1">{{ __('Experience') }}</p>
                        <p class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $item->years_experience }} {{ __('Years') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] text-slate-400 font-bold uppercase mb-1">{{ __('Orders') }}</p>
                        <p class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $item->orders_count ?? 0 }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3">
                    <div class="flex items-center gap-1.5">
                        <div class="flex text-yellow-400">
                            @php $rating = round($item->reviews()->avg('rating') ?? 0); @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= $rating ? 'fill-current' : 'text-slate-200 dark:text-white/10 fill-none' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endfor
                        </div>
                        <span class="text-[10px] font-black text-slate-800 dark:text-white">{{ number_format($item->reviews()->avg('rating') ?? 0, 1) }}</span>
                    </div>
                    <span class="text-[10px] font-black {{ $item->maintenanceCompany ? 'text-primary' : 'text-slate-400' }}">{{ $item->maintenanceCompany ? __('Partner') : __('Independent') }}</span>
                </div>
            </div>

            <a href="{{ route('admin.technicians.show', $item->id) }}" class="w-full flex items-center justify-center py-3 bg-slate-900 dark:bg-white/5 text-white rounded-xl font-black text-xs hover:bg-primary hover:shadow-lg hover:shadow-primary/30 transition-all">
                {{ __('Show Details') }}
            </a>
        </div>
        @empty
        <div class="col-span-full py-20 text-center bg-white dark:bg-[#1A1A31] rounded-[3rem] border border-dashed border-slate-200 dark:border-white/10">
            <div class="w-20 h-20 bg-slate-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <p class="text-slate-400 font-bold">{{ __('No technicians found') }}</p>
        </div>
        @endforelse
    </div>

    <!-- List View -->
    <div x-show="viewMode === 'list'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="pb-6 pt-8 px-6">#</th>
                        <th class="pb-6 pt-8 px-6">{{ __('Technician') }}</th>
                        <th class="pb-6 pt-8 px-6 text-center">{{ __('Tech Type') }}</th>
                        <th class="pb-6 pt-8 px-6">{{ __('Service Name') }}</th>
                        <th class="pb-6 pt-8 px-6">{{ __('Service Type') }}</th>
                        <th class="pb-6 pt-8 px-6 text-center">{{ __('Orders Count') }}</th>
                        <th class="pb-6 pt-8 px-6">{{ __('Average Rating') }}</th>
                        <th class="pb-6 pt-8 px-6">{{ __('Status') }}</th>
                        <th class="pb-6 pt-8 px-6">{{ __('Date') }}</th>
                        <th class="pb-6 pt-8 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr onclick="window.location='{{ route('admin.technicians.show', $item->id) }}'" class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all group cursor-pointer">
                        <td class="py-5 px-6 opacity-50">#{{ $item->id }}</td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-white font-black overflow-hidden group-hover:ring-4 ring-primary/20 transition-all shadow-md shadow-primary/20">
                                    @if($item->image)
                                        <img src="{{ asset('storage/'.$item->image) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ mb_substr($item->name_ar, 0, 1) }}
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-slate-900 dark:text-white font-black group-hover:text-primary transition-colors">{{ $item->name_ar }}</span>
                                    <span class="text-[9px] opacity-70 font-mono">{{ $item->user->phone ?? '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-5 px-6 text-center text-[10px] uppercase">
                            {{ $item->maintenanceCompany ? __('Corporate') : __('Independent') }}
                        </td>
                        <td class="py-5 px-6 opacity-80">{{ $item->category->name_ar ?? '-' }}</td>
                        <td class="py-5 px-6">
                            <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 text-[9px] font-black">
                                {{ $item->service->name_ar ?? '-' }}
                            </span>
                        </td>
                        <td class="py-5 px-6 text-center opacity-80">{{ $item->orders_count ?? 0 }}</td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-1.5">
                                <span class="text-yellow-400 flex">
                                    @php $rating = round($item->reviews()->avg('rating') ?? 0); @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3.5 h-3.5 {{ $i <= $rating ? 'fill-current' : 'text-slate-100 dark:text-white/10 fill-none' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endfor
                                </span>
                                <span class="text-[10px] opacity-70">{{ number_format($item->reviews()->avg('rating') ?? 0, 1) }}</span>
                            </div>
                        </td>
                        <td class="py-5 px-6">
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider
                                {{ ($item->user->status ?? '') == 'active' ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                {{ ($item->user->status ?? 'active') === 'active' ? __('Active') : __('Blocked') }}
                            </span>
                        </td>
                        <td class="py-5 px-6 opacity-50 text-[10px]">{{ $item->created_at->format('Y/m/d') }}</td>
                        <td class="py-5 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.technicians.show', $item->id) }}" class="p-2 rounded-lg bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('admin.technicians.edit', $item->id) }}" class="p-2 rounded-lg bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-amber-500 hover:bg-amber-500/10 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('admin.technicians.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-red-500 hover:bg-red-500/10 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400 opacity-50">{{ __('No technicians found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($items->hasPages())
    <div class="mt-8">
        {{ $items->links() }}
    </div>
    @endif
</div>
@endsection