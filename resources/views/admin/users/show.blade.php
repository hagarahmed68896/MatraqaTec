@extends('layouts.admin')

@section('title', __('User Details') . ' - ' . __('MatraqaTec'))
@section('page_title', __('User Details'))

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-white/70 hover:text-primary transition-all">
            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ __('Back to Users') }}
        </a>
    </div>

    <!-- User Info Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm p-8">
        <div class="flex items-start justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center text-primary text-3xl font-black">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500 dark:text-white/50">{{ __('ID') }}: #{{ $user->id }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="px-6 py-3 bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded-xl font-bold hover:bg-yellow-100 dark:hover:bg-yellow-500/20 transition-all">
                    {{ __('Edit') }}
                </a>
                <form action="{{ route('admin.users.toggle-block', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-6 py-3 {{ $user->status == 'active' ? 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' : 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' }} rounded-xl font-bold hover:opacity-80 transition-all">
                        {{ $user->status == 'active' ? __('Block User') : __('Unblock User') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Email -->
            <div class="space-y-2">
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Email') }}</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->email ?? '-' }}</p>
            </div>

            <!-- Phone -->
            <div class="space-y-2">
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Phone') }}</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $user->phone }}</p>
            </div>

            <!-- Type -->
            <div class="space-y-2">
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Type') }}</p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase
                    @if($user->type == 'admin') bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400
                    @elseif($user->type == 'supervisor') bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400
                    @elseif($user->type == 'technician') bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400
                    @else bg-slate-100 dark:bg-slate-500/20 text-slate-600 dark:text-slate-400
                    @endif">
                    {{ __($user->type) }}
                </span>
            </div>

            <!-- Status -->
            <div class="space-y-2">
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Status') }}</p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase
                    {{ $user->status == 'active' ? 'bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400' }}">
                    {{ __($user->status) }}
                </span>
            </div>

            <!-- Created At -->
            <div class="space-y-2">
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Created At') }}</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->created_at->format('Y-m-d H:i') }}</p>
            </div>

            <!-- Updated At -->
            <div class="space-y-2">
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Updated At') }}</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->updated_at->format('Y-m-d H:i') }}</p>
            </div>

            @if($user->blocked_at)
            <!-- Blocked At -->
            <div class="space-y-2">
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider">{{ __('Blocked At') }}</p>
                <p class="text-sm font-bold text-red-600 dark:text-red-400">{{ $user->blocked_at->format('Y-m-d H:i') }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Related Data -->
    @if($user->orders && $user->orders->count() > 0)
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm p-8">
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-6">{{ __('Recent Orders') }}</h3>
        <div class="space-y-4">
            @foreach($user->orders->take(5) as $order)
            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-white/5">
                <div>
                    <p class="font-black text-slate-900 dark:text-white">{{ __('Order') }} #{{ $order->id }}</p>
                    <p class="text-xs text-slate-500 dark:text-white/50">{{ $order->created_at->format('Y-m-d') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase bg-primary/10 text-primary">
                    {{ __($order->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
