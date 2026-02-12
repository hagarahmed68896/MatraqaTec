@extends('layouts.admin')

@section('title', __('Service Appointments'))
@section('page_title', __('Service Appointments'))

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <span class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Scheduled') }}</span>
            <span class="text-3xl font-black text-blue-500">{{ \App\Models\Appointment::where('status', 'scheduled')->count() }}</span>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <span class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('In Progress') }}</span>
            <span class="text-3xl font-black text-amber-500">{{ \App\Models\Appointment::where('status', 'in_progress')->count() }}</span>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <span class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Completed') }}</span>
            <span class="text-3xl font-black text-green-500">{{ \App\Models\Appointment::where('status', 'completed')->count() }}</span>
        </div>
        <div class="bg-white dark:bg-[#1A1A31] p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <span class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Today') }}</span>
            <span class="text-3xl font-black text-slate-900 dark:text-white">{{ \App\Models\Appointment::whereDate('appointment_date', today())->count() }}</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.appointments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by technician name...') }}" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            
            <select name="status" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
                <option value="">{{ __('All Status') }}</option>
                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>{{ __('Canceled') }}</option>
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
                        <th class="pb-4 px-6">{{ __('Appointment Date') }}</th>
                        <th class="pb-4 px-6">{{ __('Technician') }}</th>
                        <th class="pb-4 px-6">{{ __('Order') }}</th>
                        <th class="pb-4 px-6">{{ __('Status') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6">
                            <span class="text-slate-900 dark:text-white block font-bold">{{ $item->appointment_date }}</span>
                            <span class="text-[9px] opacity-70">{{ $item->from_time }} - {{ $item->to_time }}</span>
                        </td>
                        <td class="py-4 px-6">
                            @if($item->technician)
                                <span class="block">{{ $item->technician->name_ar ?? $item->technician->name_en }}</span>
                            @else
                                <span class="text-slate-400 italic font-normal">{{ __('No Technician Assigned') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            @if($item->order)
                                <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-primary hover:underline font-black">#{{ $item->order->order_number }}</a>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ $item->status == 'completed' ? 'bg-green-100 text-green-600' : ($item->status == 'scheduled' ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600') }}">
                                {{ __($item->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.appointments.show', $item->id) }}" class="p-2 rounded-lg hover:bg-slate-50 text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">{{ __('No appointments found') }}</td>
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