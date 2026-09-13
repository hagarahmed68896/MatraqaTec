@forelse(($adminNotifications ?? [])->take(10) as $notification)
@php
    $locale = app()->getLocale();
    $notifTitle = $locale === 'ar' ? ($notification->title_ar ?? $notification->title_en ?? __('Notification')) : ($notification->title_en ?? $notification->title_ar ?? __('Notification'));
    $notifBody  = $locale === 'ar' ? ($notification->body_ar ?? $notification->body_en ?? '') : ($notification->body_en ?? $notification->body_ar ?? '');
@endphp
<div x-data="{ expanded: false }" 
     @click="
        if ($event.target.closest('button') || $event.target.closest('a') || $event.target.closest('form')) return;
        @if($notification->url)
            @if(!$notification->is_read)
                fetch('{{ route('admin.notifications.markRead', $notification->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(() => {
                    window.location.href = '{{ $notification->url }}';
                }).catch(() => {
                    window.location.href = '{{ $notification->url }}';
                });
            @else
                window.location.href = '{{ $notification->url }}';
            @endif
        @else
            expanded = !expanded;
        @endif
     "
     class="notification-item px-5 py-4 border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-all relative cursor-pointer {{ $notification->is_read ? 'opacity-60' : '' }}">
    <!-- Unread dot -->
    @if(!$notification->is_read)
    <span class="absolute top-4 {{ app()->getLocale() == 'ar' ? 'left-5' : 'right-5' }} w-2 h-2 rounded-full bg-primary"></span>
    @endif
    <div class="flex gap-3 {{ app()->getLocale() == 'ar' ? 'pl-4' : 'pr-4' }}">
        <div class="w-10 h-10 rounded-full bg-[#1A1A31] dark:bg-white flex items-center justify-center text-white dark:text-slate-900 font-black flex-shrink-0 text-sm">
            {{ mb_strtoupper(mb_substr($notifTitle, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
                <p class="text-xs font-bold text-slate-800 dark:text-white leading-snug">{{ $notifTitle }}</p>
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
            
            <div class="text-[11px] text-slate-500 dark:text-slate-300 leading-relaxed break-words">
                <div x-show="expanded" x-collapse @click.stop>
                    <p class="mb-3">{{ $notifBody }}</p>
                    <div class="pt-3 space-y-3">
                        <!-- Detailed Fields from data -->
                        @if(isset($notification->data['customer_name']))
                            <div class="flex items-center gap-2 text-[10px] bg-slate-50 dark:bg-white/5 p-2 rounded-lg">
                                <span class="text-slate-400 font-bold uppercase tracking-wider">{{ __('Customer') }}:</span> 
                                <span class="text-slate-700 dark:text-white font-black">{{ $notification->data['customer_name'] }}</span>
                            </div>
                        @endif

                        <!-- Actions for Technician Requests -->
                        @if(($notification->data['type'] ?? $notification->type) == 'technician_request' && isset($notification->data['request_id']))
                            @php
                                $techRequest = \App\Models\TechnicianRequest::find($notification->data['request_id']);
                            @endphp
                            @if($techRequest && $techRequest->status === 'pending')
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.technician-requests.accept', $notification->data['request_id']) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                    <input type="hidden" name="password" value="password123">
                                    <button type="submit" class="w-full py-2 bg-primary text-white rounded-xl text-[10px] font-black flex items-center justify-center gap-1 hover:brightness-110 transition-all shadow-lg shadow-primary/20">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('Accept') }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.technician-requests.refuse', $notification->data['request_id']) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                    <input type="hidden" name="rejection_reason" value="Rejected via notification">
                                    <button type="submit" class="w-full py-2 bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 hover:bg-slate-200 dark:hover:bg-white/20 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.orders.show', $order->id) }}?notification_id={{ $notification->id }}" class="flex-1 py-1.5 bg-primary text-white rounded-xl text-[10px] font-black flex items-center justify-center gap-1 hover:brightness-110 transition-all shadow-lg shadow-primary/20">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ __('Details') }}
                                </a>
                                <form action="{{ route('admin.orders.refuse', $order->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                    <input type="hidden" name="rejection_reason" value="Rejected via notification">
                                    <button type="submit" class="w-full py-1.5 bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 hover:bg-slate-200 dark:hover:bg-white/20 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        {{ __('Refuse') }}
                                    </button>
                                </form>
                            </div>
                            @endif
                        @endif
                        
                        <!-- General Actions for notifications with a target URL -->
                        @php
                            $isCustomAction = false;
                            if (($notification->data['type'] ?? $notification->type) == 'technician_request' && isset($notification->data['request_id'])) {
                                $techRequest = \App\Models\TechnicianRequest::find($notification->data['request_id']);
                                if ($techRequest && $techRequest->status === 'pending') {
                                    $isCustomAction = true;
                                }
                            }
                            if (($notification->data['type'] ?? $notification->type) == 'new_order' && isset($notification->data['order_id'])) {
                                $order = \App\Models\Order::find($notification->data['order_id']);
                                if ($order && $order->status === 'new') {
                                    $isCustomAction = true;
                                }
                            }
                        @endphp

                        @if(!$isCustomAction && $notification->url)
                            <div class="flex items-center gap-2">
                                <a href="{{ $notification->url }}" onclick="
                                    event.stopPropagation();
                                    @if(!$notification->is_read)
                                        fetch('{{ route('admin.notifications.markRead', $notification->id) }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json'
                                            }
                                        });
                                    @endif
                                " class="flex-1 py-1.5 bg-primary text-white rounded-xl text-[10px] font-black flex items-center justify-center gap-1 hover:brightness-110 transition-all shadow-lg shadow-primary/20">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ __('Details') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Type badge + time -->
            <div class="flex items-center gap-2 flex-wrap mt-2">
                @if($notification->type)
                <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300 uppercase tracking-wide">{{ __($notification->type) }}</span>
                @endif
                <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
            </div>
        </div>

        <!-- ... menu -->
        <div x-data="{ openMenu: false }" class="absolute top-3 {{ app()->getLocale() == 'ar' ? 'left-3' : 'right-3' }}" @click.stop>
            <button @click="openMenu = !openMenu" class="text-slate-300 hover:text-slate-600 dark:text-white dark:hover:text-white transition-colors p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
            </button>
            <div x-show="openMenu" @click.away="openMenu = false" class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-1 w-40 bg-white dark:bg-[#1A1A31] rounded-xl shadow-xl border border-slate-100 dark:border-white/10 z-30 overflow-hidden" style="display:none;">
                @if(!$notification->is_read)
                <button @click="markRead('{{ $notification->id }}', $event)" class="w-full text-start px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-colors flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Mark as read') }}
                </button>
                @endif
                <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-start px-4 py-2.5 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-white/5 transition-colors flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@empty
<div class="p-10 text-center text-slate-400 dark:text-slate-500 flex flex-col items-center gap-3">
    <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
    <p class="text-xs font-bold">{{ __('No new notifications') }}</p>
</div>
@endforelse
