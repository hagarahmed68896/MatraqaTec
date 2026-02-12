@extends('layouts.admin')

@section('title', __('Top Technicians'))
@section('page_title', __('Top Technicians'))

@section('content')
<div x-data="{ viewMode: 'grid' }" class="space-y-6 pb-20">
    <!-- Header with Search and View Toggle -->
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white whitespace-nowrap">{{ __('Top Technicians') }}</h2>
        
        <!-- Search Bar -->
        <form action="{{ route('admin.technicians.top') }}" method="GET" class="flex gap-3">
            <div class="relative w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by name, phone, email...') }}" class="w-full pr-10 pl-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1A1A31] focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary dark:text-white font-bold transition-all text-sm shadow-sm px-2">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                </div>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-900 dark:bg-primary text-gray rounded-xl font-bold hover:opacity-90 transition-all shadow-sm text-sm whitespace-nowrap">
                {{ __('Search') }}
            </button>
        </form>

        <!-- View Mode Toggle -->
        <div class="flex items-center p-1 bg-white dark:bg-white/5 rounded-xl border border-slate-100 dark:border-white/10 shadow-sm">
            <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600'" class="p-2.5 rounded-lg transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </button>
            <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600'" class="p-2.5 rounded-lg transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <!-- Grid View -->
   <div x-show="viewMode === 'grid'" 
     x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0 translate-y-4" 
     x-transition:enter-end="opacity-100 translate-y-0" 
     class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" 
     dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    
    @forelse($items as $item)
    <div class="bg-white dark:bg-[#1A1A31] p-5 rounded-2xl border border-slate-100 dark:border-white/5 shadow-sm hover:shadow-xl transition-all duration-300 group">
        
        <div class="flex items-start justify-between mb-4">
             <div class="w-16 h-16 rounded-2xl overflow-hidden ring-4 ring-slate-50 dark:ring-white/5 shadow-lg transform group-hover:scale-105 transition-transform duration-300">
                @if($item->image)
                    <img src="{{ asset('storage/'.$item->image) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-xl">
                        {{ mb_substr(app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en, 0, 1) }}
                    </div>
                @endif
            </div>
            <span class="px-3 py-1.5 rounded-full {{ $item->availability_status === 'available' ? 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' }} text-[11px] font-bold shadow-sm">
                {{ $item->availability_status === 'available' ? __('Available') : __('Unavailable') }}
            </span>
            
           
        </div>

        <div class="mb-5">
            <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">{{ app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                {{ __('Specialist in') }} {{ app()->getLocale() == 'ar' ? ($item->category->name_ar ?? __('Technical Services')) : ($item->category->name_en ?? __('Technical Services')) }}
            </p>
        </div>

        <div class="space-y-4 mb-6 border-t border-slate-50 dark:border-white/5 pt-4">
            
            <div class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                <div class="p-1.5 bg-slate-50 dark:bg-white/5 rounded-lg">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <span class="text-xs font-bold min-w-[80px]">{{ __('Type') }}:</span>
                <span class="text-xs text-slate-900 dark:text-white font-black">{{ $item->maintenanceCompany ? (app()->getLocale() == 'ar' ? ($item->maintenanceCompany->name_ar ?? __('Company')) : ($item->maintenanceCompany->name_en ?? __('Company'))) : __('Platform Name') }}</span>
            </div>

            <div class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                <div class="p-1.5 bg-slate-50 dark:bg-white/5 rounded-lg">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                </div>
                <span class="text-xs font-bold min-w-[80px]">{{ __('Service Type') }}:</span>
                <span class="text-xs text-slate-900 dark:text-white font-black">{{ app()->getLocale() == 'ar' ? ($item->service->name_ar ?? '-') : ($item->service->name_en ?? '-') }}</span>
            </div>

            <div class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                <div class="p-1.5 bg-slate-50 dark:bg-white/5 rounded-lg">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                </div>
                <span class="text-xs font-bold min-w-[80px]">{{ __('Regions') }}:</span>
                <span class="text-xs text-slate-900 dark:text-white font-black">{{ __('All Regions') }}</span>
            </div>

            <div class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                <div class="p-1.5 bg-slate-50 dark:bg-white/5 rounded-lg">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-xs font-bold min-w-[80px]">{{ __('Orders Count') }}:</span>
                <span class="text-xs text-slate-900 dark:text-white font-black">{{ $item->orders_count ?? 0 }} {{ __('Orders') }}</span>
            </div>

            <div class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                <div class="p-1.5 bg-yellow-50 dark:bg-yellow-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <span class="text-xs font-bold min-w-[80px]">{{ __('Average Rating') }}:</span>
                <span class="text-xs text-slate-900 dark:text-white font-black">{{ number_format($item->reviews_avg_rating ?? 0, 1) }}</span>
            </div>
        </div>

    <a href="{{ route('admin.technicians.show', $item->id) }}" 
   class="w-full flex items-center justify-center py-3 bg-[#1A1A31] dark:bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-slate-800 dark:hover:bg-indigo-700 shadow-sm hover:shadow-lg transition-all duration-200">
    {{ __('View Details') }}
</a>
    </div>
    @empty
    <div class="col-span-full py-20 text-center bg-white dark:bg-[#1A1A31] rounded-3xl border border-dashed border-slate-200 dark:border-white/10">
        <p class="text-slate-400 font-bold">{{ __('No top technicians found') }}</p>
    </div>
    @endforelse
</div>

    <!-- List View -->
    <div x-show="viewMode === 'list'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead class="bg-slate-50 dark:bg-white/5">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-wider border-b-2 border-slate-200 dark:border-white/10">
                        <th class="pb-4 pt-6 px-6">#</th>
                        <th class="pb-4 pt-6 px-6">{{ __('Technician') }}</th>
                        <th class="pb-4 pt-6 px-6">{{ __('Service Name') }}</th>
                        <th class="pb-4 pt-6 px-6 text-center">{{ __('Orders Count') }}</th>
                        <th class="pb-4 pt-6 px-6">{{ __('Average Rating') }}</th>
                        <th class="pb-4 pt-6 px-6">{{ __('Status') }}</th>
                        <th class="pb-4 pt-6 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm font-bold text-slate-700 dark:text-white/80">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-100 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all group">
                        <td class="py-5 px-6 text-slate-500 dark:text-slate-400">#{{ $item->id }}</td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-white font-black overflow-hidden group-hover:ring-4 ring-primary/20 transition-all shadow-md shadow-primary/20">
                                    @if($item->image)
                                        <img src="{{ asset('storage/'.$item->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-lg">{{ mb_substr(app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-slate-900 dark:text-white font-black group-hover:text-primary transition-colors text-base">{{ app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $item->user->phone ?? '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-5 px-6">
                            <span class="px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xs font-black">
                                {{ app()->getLocale() == 'ar' ? ($item->service->name_ar ?? '-') : ($item->service->name_en ?? '-') }}
                            </span>
                        </td>
                        <td class="py-5 px-6 text-center text-base">{{ $item->orders_count ?? 0 }}</td>
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-2">
                                <span class="text-yellow-400 flex gap-0.5">
                                    @php $rating = round($item->reviews()->avg('rating') ?? 0); @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $rating ? 'fill-current' : 'text-slate-200 dark:text-white/10 fill-none' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endfor
                                </span>
                                <span class="text-sm font-black">{{ number_format($item->reviews()->avg('rating') ?? 0, 1) }}</span>
                            </div>
                        </td>
                        <td class="py-5 px-6">
                            <span class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-wider
                                {{ ($item->user->status ?? '') == 'active' ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                {{ ($item->user->status ?? 'active') === 'active' ? __('Active') : __('Blocked') }}
                            </span>
                        </td>
                        <td class="py-5 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.technicians.show', $item->id) }}" class="p-2.5 rounded-lg bg-slate-50 dark:bg-white/5 text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 text-base">{{ __('No top technicians found') }}</td>
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
