@extends('layouts.admin')

@section('title', __('Corporate Customers'))
@section('page_title', __('Corporate Customers'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('Corporate Customers') }}</h2>
            <p class="text-sm text-slate-500 dark:text-white/50">{{ __('Manage company accounts and contracts') }}</p>
        </div>
        <a href="{{ route('admin.corporate-customers.create') }}" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ __('Add Company') }}
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6">
        <form action="{{ route('admin.corporate-customers.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search company name, CR...') }}" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            
            <select name="status" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
                <option value="">{{ __('All Status') }}</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>{{ __('Blocked') }}</option>
            </select>

            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                {{ __('Filter') }}
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="pb-4 px-6">{{ __('ID') }}</th>
                        <th class="pb-4 px-6">{{ __('Company') }}</th>
                        <th class="pb-4 px-6">{{ __('CR Number') }}</th>
                        <th class="pb-4 px-6">{{ __('Contact') }}</th>
                        <th class="pb-4 px-6">{{ __('Status') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr onclick="window.location='{{ route('admin.corporate-customers.show', $item->id) }}'" class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all cursor-pointer">
                        <td class="py-4 px-6">#{{ $item->id }}</td>
                        <td class="py-4 px-6">
                            <span class="text-slate-900 dark:text-white font-black">{{ $item->company_name_ar }}</span>
                        </td>
                        <td class="py-4 px-6 font-mono">{{ $item->commercial_record_number ?? '-' }}</td>
                        <td class="py-4 px-6">
                            <div class="flex flex-col text-[10px]">
                                <span>{{ $item->user->email ?? '-' }}</span>
                                <span>{{ $item->user->phone ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ ($item->user->status ?? '') == 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ __($item->user->status ?? 'active') }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.corporate-customers.show', $item->id) }}" class="p-2 rounded-lg hover:bg-blue-50 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('admin.corporate-customers.edit', $item->id) }}" class="p-2 rounded-lg hover:bg-yellow-50 text-yellow-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">{{ __('No companies found') }}</td>
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