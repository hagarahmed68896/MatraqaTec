@extends('layouts.admin')

@section('title', __('Service Management'))
@section('page_title', __('Service Management'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('All Services') }}</h2>
        <a href="{{ route('admin.services.create') }}" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
            {{ __('Add New') }}
        </a>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-400 text-xs font-black uppercase border-b border-slate-100 dark:border-white/5">
                        <th class="pb-4 px-6">{{ __('ID') }}</th>
                        <th class="pb-4 px-6">{{ __('Icon') }}</th>
                        <th class="pb-4 px-6">{{ __('Name') }}</th>
                        <th class="pb-4 px-6">{{ __('Price') }}</th>
                        <th class="pb-4 px-6">{{ __('Sub-services') }}</th>
                        <th class="pb-4 px-6">{{ __('Technicians') }}</th>
                        <th class="pb-4 px-6">{{ __('Companies') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6">#{{ $item->id }}</td>
                        
                        <td class="py-4 px-6">
                            @if($item->icon)
                                <img src="{{ asset($item->icon) }}" alt="Icon" class="w-8 h-8 rounded-lg object-contain bg-slate-100 dark:bg-white/5 p-1">
                            @else
                                <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-300">-</span>
                            @endif
                        </td>
                        
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                @if($item->image)
                                    <img src="{{ asset($item->image) }}" alt="Image" class="w-10 h-10 rounded-lg object-cover">
                                @endif
                                <div>
                                    <p class="text-slate-800 dark:text-white">{{ $item->name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $item->name_en }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <td class="py-4 px-6 text-green-600 dark:text-green-400">
                            {{ $item->price ? number_format($item->price, 2) . ' ' . __('SAR') : '-' }}
                        </td>
                        
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-md bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                {{ $item->children_count }}
                            </span>
                        </td>
                        
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                                {{ $item->technicians_count }}
                            </span>
                        </td>
                        
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-md bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400">
                                {{ $item->companies_count ?? 0 }}
                            </span>
                        </td>

                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.services.show', $item->id) }}" class="p-2 rounded-lg hover:bg-blue-50 text-blue-600 dark:text-blue-400 dark:hover:bg-blue-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </a>
                                <a href="{{ route('admin.services.edit', $item->id) }}" class="p-2 rounded-lg hover:bg-yellow-50 text-yellow-600 dark:text-yellow-400 dark:hover:bg-yellow-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('admin.services.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-600 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors" onclick="return confirm('{{ __('Are you sure?') }}')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400 flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="font-bold">{{ __('No services found') }}</p>
                        </td>
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