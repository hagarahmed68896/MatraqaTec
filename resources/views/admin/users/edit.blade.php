@extends('layouts.admin')

@section('title', __('Edit User') . ' - ' . __('MatraqaTec'))
@section('page_title', __('Edit User'))

@section('content')
<div class="max-w-4xl">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-white/70 hover:text-primary transition-all">
            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ __('Back to Users') }}
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm p-8">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="space-y-2">
                <label for="name" class="block text-xs font-black text-slate-600 dark:text-white/70 uppercase tracking-wider">
                    {{ __('Name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                @error('name')
                <p class="text-xs text-red-500 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <label for="email" class="block text-xs font-black text-slate-600 dark:text-white/70 uppercase tracking-wider">
                    {{ __('Email') }}
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                @error('email')
                <p class="text-xs text-red-500 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div class="space-y-2">
                <label for="phone" class="block text-xs font-black text-slate-600 dark:text-white/70 uppercase tracking-wider">
                    {{ __('Phone Number') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required maxlength="9" inputmode="numeric"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-mono">
                @error('phone')
                <p class="text-xs text-red-500 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <label for="password" class="block text-xs font-black text-slate-600 dark:text-white/70 uppercase tracking-wider">
                    {{ __('Password') }}
                </label>
                <input type="password" id="password" name="password"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                @error('password')
                <p class="text-xs text-red-500 font-bold">{{ $message }}</p>
                @enderror
                <p class="text-xs text-slate-500 dark:text-white/50">{{ __('Leave blank to keep current password') }}</p>
            </div>

            <!-- Type -->
            <div class="space-y-2">
                <label for="type" class="block text-xs font-black text-slate-600 dark:text-white/70 uppercase tracking-wider">
                    {{ __('User Type') }} <span class="text-red-500">*</span>
                </label>
                <select id="type" name="type" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    <option value="client" {{ old('type', $user->type) == 'client' ? 'selected' : '' }}>{{ __('Client') }}</option>
                    <option value="technician" {{ old('type', $user->type) == 'technician' ? 'selected' : '' }}>{{ __('Technician') }}</option>
                    <option value="supervisor" {{ old('type', $user->type) == 'supervisor' ? 'selected' : '' }}>{{ __('Supervisor') }}</option>
                    <option value="admin" {{ old('type', $user->type) == 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                </select>
                @error('type')
                <p class="text-xs text-red-500 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="space-y-2">
                <label for="status" class="block text-xs font-black text-slate-600 dark:text-white/70 uppercase tracking-wider">
                    {{ __('Status') }} <span class="text-red-500">*</span>
                </label>
                <select id="status" name="status" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="blocked" {{ old('status', $user->status) == 'blocked' ? 'selected' : '' }}>{{ __('Blocked') }}</option>
                </select>
                @error('status')
                <p class="text-xs text-red-500 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-4 pt-6 border-t border-slate-100 dark:border-white/5">
                <button type="submit" class="px-8 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                    {{ __('Update User') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-8 py-3 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-white/70 rounded-xl font-bold hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
