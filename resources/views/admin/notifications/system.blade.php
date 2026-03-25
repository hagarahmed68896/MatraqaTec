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
        <button id="markAllReadBtn" class="flex items-center gap-2 text-sm font-bold text-primary hover:text-primary dark:hover:text-white-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ __('Mark all as read') }}
        </button>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="divide-y divide-slate-50 dark:divide-white/5" id="notificationsList">
            @forelse($notifications as $notification)
            <div class="p-6 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-all group relative {{ !$notification->is_read ? 'bg-primary/5 dark:bg-white/5' : '' }}">
                <div class="flex items-start gap-4">
                    @php
                        $locale = app()->getLocale();
                        $notifTitle = $locale === 'ar' ? ($notification->title_ar ?? $notification->title_en ?? ($notification->data['title'] ?? __('Notification'))) : ($notification->title_en ?? $notification->title_ar ?? ($notification->data['title'] ?? __('Notification')));
                        $notifBody  = $locale === 'ar' ? ($notification->body_ar ?? $notification->body_en ?? ($notification->data['body'] ?? $notification->data['message'] ?? '')) : ($notification->body_en ?? $notification->body_ar ?? ($notification->data['body'] ?? $notification->data['message'] ?? ''));
                    @endphp
                    <!-- Icon -->
                    <div class="w-14 h-14 rounded-2xl bg-[#1A1A31] dark:bg-white flex items-center justify-center flex-shrink-0 text-white dark:text-slate-900 shadow-lg shadow-indigo-500/10">
                        <span class="text-xl font-black">{{ mb_strtoupper(mb_substr($notifTitle, 0, 1)) }}</span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-black text-slate-800 dark:text-white text-lg leading-tight">
                                {{ $notifTitle }}
                                @if(!$notification->is_read)
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-primary border-2 border-white dark:border-[#1A1A31] ml-2 animate-pulse"></span>
                                @endif
                            </h3>
                            
                            <!-- Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-slate-400 hover:text-slate-600 dark:text-white dark:hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white dark:bg-[#1A1A31] rounded-xl shadow-xl border border-slate-100 dark:border-white/10 z-10 overflow-hidden" style="display: none;">
                                    @if(!$notification->is_read)
                                    <button onclick="markRead('{{ $notification->id }}')" class="block w-full text-start px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-colors">{{ __('Mark as read') }}</button>
                                    @endif
                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="block w-full text-start px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-white/5 transition-colors">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <p class="text-slate-500 dark:text-slate-300 mb-4 leading-relaxed text-sm font-medium">{{ $notifBody }}</p>

                        <!-- Expanded Metadata -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            @if(isset($notification->data['customer_name']))
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Customer') }}</span>
                                    <span class="text-[11px] font-black text-slate-700 dark:text-slate-200">{{ $notification->data['customer_name'] }}</span>
                                </div>
                            @endif
                            @if(isset($notification->data['order_number']))
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Order #') }}</span>
                                    <span class="text-[11px] font-black text-slate-700 dark:text-slate-200">#{{ $notification->data['order_number'] }}</span>
                                </div>
                            @endif
                            @if(isset($notification->data['service_name']))
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20">
                                    <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">{{ __('Service') }}</span>
                                    <span class="text-[11px] font-black text-indigo-600 dark:text-indigo-300">{{ $notification->data['service_name'] }}</span>
                                </div>
                            @endif
                            @if($notification->type)
                                <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest border border-slate-200/50 dark:border-white/5">
                                    {{ __($notification->type) }}
                                </span>
                            @endif
                        </div>

                        <!-- Actions for Technician Requests -->
                        @if(($notification->data['type'] ?? $notification->type) == 'technician_request' && isset($notification->data['request_id']))
                            @php
                                $techRequest = \App\Models\TechnicianRequest::find($notification->data['request_id']);
                            @endphp
                            @if($techRequest && $techRequest->status === 'pending')
                            <div class="flex items-center gap-3 mb-4">
                                <form action="{{ route('admin.technician-requests.accept', $notification->data['request_id']) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                    <input type="hidden" name="password" value="password123">
                                    <button type="submit" class="h-10 px-6 rounded-xl bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] font-black text-xs flex items-center gap-2 hover:scale-[1.02] active:scale-95 transition-all shadow-lg hover:shadow-indigo-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        {{ __('Accept Request') }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.technician-requests.refuse', $notification->data['request_id']) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                    <input type="hidden" name="rejection_reason" value="Rejected via notifications list">
                                    <button type="submit" class="h-10 px-6 rounded-xl bg-rose-500/10 text-rose-500 font-black text-xs flex items-center gap-2 hover:bg-rose-500 hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        {{ __('Refuse') }}
                                    </button>
                                </form>
                            </div>
                            @endif
                        @endif

                        <!-- Actions for New Orders -->
                        @if(($notification->data['type'] ?? $notification->type) == 'new_order' && isset($notification->data['order_id']))
                            @php
                                $order = \App\Models\Order::find($notification->data['order_id']);
                            @endphp
                            @if($order && $order->status === 'new')
                            <div class="flex items-center gap-3 mb-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}?notification_id={{ $notification->id }}" class="h-10 px-6 rounded-xl bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] font-black text-xs flex items-center gap-2 hover:scale-[1.02] active:scale-95 transition-all shadow-lg hover:shadow-indigo-500/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ __('View Order Details') }}
                                </a>
                                <form action="{{ route('admin.orders.refuse', $order->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                    <input type="hidden" name="rejection_reason" value="Rejected via notifications list">
                                    <button type="submit" class="h-10 px-6 rounded-xl bg-rose-500/10 text-rose-500 font-black text-xs flex items-center gap-2 hover:bg-rose-500 hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        {{ __('Refuse Order') }}
                                    </button>
                                </form>
                            </div>
                            @endif
                        @endif

                        <div class="flex items-center justify-between mt-auto pt-2 border-t border-slate-50 dark:border-white/5">
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                            @if($notification->is_read)
                                <span class="text-[10px] font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">{{ __('Read') }}</span>
                            @endif
                        </div>
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

@section('scripts')
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
@endsection
@endsection
