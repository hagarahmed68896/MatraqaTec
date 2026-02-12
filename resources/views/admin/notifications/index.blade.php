@extends('layouts.admin')

@section('title', __('Notifications'))
@section('page_title', __('Notifications'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
            {{ __('Notifications') }}
            <span class="text-sm font-bold text-slate-400 dark:text-slate-500">({{ $notifications->total() }})</span>
        </h2>
        <button id="markAllReadBtn" class="flex items-center gap-2 text-sm font-bold text-primary hover:text-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ __('Mark all as read') }}
        </button>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="divide-y divide-slate-50 dark:divide-white/5" id="notificationsList">
            @forelse($notifications as $notification)
            <div class="p-6 hover:bg-slate-50 dark:hover:bg-white/5 transition-all group relative {{ !$notification->read_at ? 'bg-primary/5 dark:bg-white/5' : '' }}">
                <div class="flex items-start gap-4">
                    <!-- Icon -->
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/10 flex items-center justify-center flex-shrink-0 text-slate-500 dark:text-white">
                        <span class="text-lg font-black">{{ strtoupper(substr($notification->data['title'] ?? 'N', 0, 1)) }}</span>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-1">
                            <h3 class="font-bold text-slate-800 dark:text-white text-lg">
                                {{ $notification->data['title'] ?? __('Notification') }}
                                @if(!$notification->read_at)
                                <span class="inline-block w-2 h-2 rounded-full bg-red-500 ml-2"></span>
                                @endif
                            </h3>
                            
                            <!-- Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white dark:bg-[#1A1A31] rounded-xl shadow-xl border border-slate-100 dark:border-white/10 z-10 overflow-hidden" style="display: none;">
                                    @if(!$notification->read_at)
                                    <button onclick="markRead('{{ $notification->id }}')" class="block w-full text-start px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">{{ __('Mark as read') }}</button>
                                    @endif
                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="block w-full text-start px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-white/5 transition-colors">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <p class="text-slate-500 dark:text-slate-300 mb-4 leading-relaxed">{{ $notification->data['body'] ?? $notification->data['message'] ?? '' }}</p>

                        <!-- Actions for Technician Requests -->
                        @if(($notification->data['type'] ?? '') == 'technician_request' && isset($notification->data['request_id']))
                        <div class="flex items-center gap-3 mb-3">
                            <a href="{{ route('admin.technician-requests.show', $notification->data['request_id']) }}" class="flex items-center gap-2 px-6 py-2 rounded-xl bg-slate-800 dark:bg-white text-white dark:text-slate-800 font-bold hover:opacity-90 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ __('Accept') }}
                            </a>
                            <form action="#" method="POST"> <!-- Placeholder reject action -->
                                <button type="button" class="flex items-center gap-2 px-6 py-2 rounded-xl bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white font-bold hover:bg-slate-200 dark:hover:bg-white/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    {{ __('Refuse') }}
                                </button>
                            </form>
                        </div>
                        @endif

                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto bg-slate-50 dark:bg-white/5 rounded-full flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-1">{{ __('No notifications') }}</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('You are all caught up!') }}</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('markAllReadBtn').addEventListener('click', async () => {
        try {
            const response = await fetch("{{ route('admin.notifications.markAllRead') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            if (response.ok) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    });

    async function markRead(id) {
        try {
            const response = await fetch(`{{ route('admin.notifications.markRead', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            if (response.ok) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }
</script>
@endpush
@endsection
