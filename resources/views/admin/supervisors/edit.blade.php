@extends('layouts.admin')

@section('page_title', __('Edit Supervisor'))

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ 
    openStatus: false,
    status: '{{ old('status', $item->status) }}',
    statusLabel: '{{ old('status', $item->status) == 'active' ? __('Active') : __('Blocked') }}'
}">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.supervisors.index') }}" 
               class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/5 text-slate-400 hover:text-primary transition-all shadow-sm">
                <svg class="w-6 h-6 {{ app()->getLocale() == 'ar' ? 'rotate-0' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Edit Supervisor') }}</h1>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-white/5 overflow-hidden">
        <form action="{{ route('admin.supervisors.update', $item->id) }}" method="POST" class="p-8 md:p-12">
            @csrf
            @method('PUT')

            <div class="mb-10 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">{{ __('Supervisor Data') }}</h3>
                <div class="h-1.5 w-12 bg-primary rounded-full"></div>
            </div>

            <div class="space-y-8">
                @php
                    $names = explode(' ', $item->name, 2);
                    $first_name = $names[0] ?? '';
                    $last_name = $names[1] ?? '';
                @endphp

                <!-- Names Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- First Name -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('First Name') }}</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $first_name) }}" required placeholder="{{ __('Enter first name') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('first_name') border-red-500 @enderror">
                        @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Last Name -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Last Name') }}</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $last_name) }}" required placeholder="{{ __('Enter last name') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('last_name') border-red-500 @enderror">
                        @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Contact Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Phone -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Phone Number') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $item->phone) }}" required placeholder="5XXXXXXXX"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('phone') border-red-500 @enderror">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Email Address') }}</label>
                        <input type="email" name="email" value="{{ old('email', $item->email) }}" required placeholder="{{ __('Enter email address') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('email') border-red-500 @enderror">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Permissions (Roles) -->
                <div class="space-y-4">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Permissions & Roles') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($roles as $role)
                        <label class="relative flex items-center gap-3 p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border-2 border-transparent hover:border-primary/30 transition-all cursor-pointer group">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                   {{ is_array(old('roles', $item->roles->pluck('id')->toArray())) && in_array($role->id, old('roles', $item->roles->pluck('id')->toArray())) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20 bg-white dark:bg-white/5">
                            <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-primary transition-colors">{{ $role->name_ar ?? $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Account Status -->
                <div class="space-y-4 pt-4">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Account Status') }}</label>
                    <div class="relative">
                        <!-- Dropdown Button -->
                        <button type="button" 
                                @click="openStatus = !openStatus"
                                class="w-full flex items-center justify-between px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent hover:border-primary/30 rounded-2xl transition-all outline-none">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full shadow-sm shadow-current" :class="status === 'active' ? 'bg-green-500 text-green-500/40' : 'bg-red-500 text-red-500/40'"></div>
                                <span class="text-sm font-bold" :class="status === 'active' ? 'text-slate-800 dark:text-white' : 'text-slate-500 dark:text-slate-400'" x-text="statusLabel"></span>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 transition-transform duration-300" :class="openStatus ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Hidden Input -->
                        <input type="hidden" name="status" x-model="status">

                        <!-- Dropdown Menu -->
                        <div x-show="openStatus" 
                             @click.away="openStatus = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute z-50 w-full mt-2 p-2 bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-2xl shadow-xl">
                            
                            <button type="button" 
                                    @click="status = 'active'; statusLabel = '{{ __('Active') }}'; openStatus = false"
                                    class="w-full flex items-center gap-4 p-4 rounded-xl transition-all"
                                    :class="status === 'active' ? 'bg-green-50 dark:bg-green-500/5' : 'hover:bg-slate-50 dark:hover:bg-white/5'">
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-green-500/10 text-green-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('Active') }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Active Account') }}</span>
                                </div>
                            </button>

                            <button type="button" 
                                    @click="status = 'blocked'; statusLabel = '{{ __('Blocked') }}'; openStatus = false"
                                    class="w-full flex items-center gap-4 p-4 rounded-xl transition-all mt-1"
                                    :class="status === 'blocked' ? 'bg-red-50 dark:bg-red-500/5' : 'hover:bg-slate-50 dark:hover:bg-white/5'">
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-500/10 text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('Blocked') }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Suspended Account') }}</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="grid grid-cols-2 gap-4 mt-12 pt-8 border-t border-slate-100 dark:border-white/5">
                <button type="submit" 
                        class="py-4 bg-[#1A1A31] text-white rounded-2xl font-black shadow-xl shadow-primary/20 hover:bg-black transition-all uppercase tracking-widest text-sm">
                    {{ __('Update Supervisor') }}
                </button>
                <a href="{{ route('admin.supervisors.index') }}" 
                   class="py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black hover:bg-slate-200 dark:hover:bg-white/10 transition-all text-center uppercase tracking-widest text-sm">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
