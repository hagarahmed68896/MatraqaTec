@extends('layouts.admin')

@section('title', __('Inquiries Management'))
@section('page_title', __('Inquiries Management'))

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <form action="{{ route('admin.inquiries.index') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by name, email, phone...') }}" class="flex-1 px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white">
            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                {{ __('Search') }}
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-white/5">
                        <th class="pb-4 px-6">{{ __('ID') }}</th>
                        <th class="pb-4 px-6">{{ __('Sender') }}</th>
                        <th class="pb-4 px-6">{{ __('Subject') }}</th>
                        <th class="pb-4 px-6">{{ __('Date') }}</th>
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-all">
                        <td class="py-4 px-6">#{{ $item->id }}</td>
                        <td class="py-4 px-6">
                            <span class="text-slate-900 dark:text-white block font-bold">{{ $item->name }}</span>
                            <span class="text-[10px] opacity-70">{{ $item->email }}</span>
                        </td>
                        <td class="py-4 px-6 opacity-80">{{ Str::limit($item->subject, 50) }}</td>
                        <td class="py-4 px-6 opacity-70">{{ $item->created_at->format('Y-m-d') }}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.inquiries.show', $item->id) }}" class="p-2 rounded-lg hover:bg-blue-50 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <form action="{{ route('admin.inquiries.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this inquiry?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">{{ __('No inquiries found') }}</td>
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