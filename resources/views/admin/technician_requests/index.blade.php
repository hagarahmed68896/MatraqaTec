@extends('layouts.admin')

@section('title', __('Technician Requests'))
@section('page_title', __('Technician Requests'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('Technician Onboarding') }}</h2>
            <p class="text-sm text-slate-500 dark:text-white/50">{{ __('Review and approve new technician registration requests') }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.technician-requests.index') }}" method="GET" class="flex gap-4">
            <select name="status" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
                <option value="">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>{{ __('Accepted') }}</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
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
                        <th class="pb-4 px-6">{{ __('Name') }}</th>
                        <th class="pb-4 px-6">{{ __('Company') }}</th>
                        <th class="pb-4 px-6">{{ __('Service') }}</th>
                        <th class="pb-4 px-6">{{ __('Experience') }}</th>
                        <th class="pb-4 px-6">{{ __('Status') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6">#{{ $item->id }}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                @if($item->photo)
                                <img src="{{ $item->photo }}" class="w-8 h-8 rounded-lg object-cover">
                                @else
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                @endif
                                <div>
                                    <span class="text-slate-900 dark:text-white block">{{ $item->name_ar }}</span>
                                    <span class="text-[10px] opacity-70">{{ $item->phone }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-slate-900 dark:text-white">{{ $item->maintenanceCompany->user->name ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-6 text-[10px]">{{ $item->service->name_ar ?? '-' }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-lg bg-blue-50 text-blue-600">{{ $item->years_experience }} {{ __('Years') }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ $item->status == 'accepted' ? 'bg-green-100 text-green-600' : ($item->status == 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                                {{ __($item->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.technician-requests.show', $item->id) }}" class="p-2 rounded-lg hover:bg-blue-50 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">{{ __('No technician requests found') }}</td>
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