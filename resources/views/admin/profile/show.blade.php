@extends('layouts.admin')

@section('title', __('Personal Account') . ' - ' . __('MatraqaTec'))

@section('content')
<div x-data="{ tab: 'details', deleteModal: false }" class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">
    
    <!-- Custom Delete Modal -->
    <div x-show="deleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-cloak>
        <div @click.away="deleteModal = false" 
             x-show="deleteModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-[#1A1A31] w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl border border-slate-100 dark:border-white/5 relative overflow-hidden">
            
            
            <div class="relative flex flex-col items-center text-center space-y-6">
                <div class="w-20 h-20 bg-red-50 dark:bg-red-500/10 rounded-3xl flex items-center justify-center text-red-500 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Confirm') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-bold px-4 leading-relaxed">
                        {{ __('Are you sure you want to delete your photo?') }}
                    </p>
                </div>

                <div class="flex items-center gap-4 w-full pt-4">
                    <form action="{{ route('admin.profile.delete-avatar') }}" method="POST" class="w-1/2">
                        @csrf
                        <button type="submit" class="w-full px-8 py-4 bg-red-500 text-white rounded-2xl text-sm font-black hover:bg-red-600 transition-all shadow-lg shadow-red-500/20">
                            {{ __('Confirm') }}
                        </button>
                    </form>
                    <button @click="deleteModal = false" class="w-1/2 px-8 py-4 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl text-sm font-black hover:bg-slate-200 transition-all">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

 

    <!-- Page Title -->
    <div class="flex items-center justify-center py-4">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Personal Account') }}</h1>
    </div>

    <!-- Profile Info Card -->
    <div class="bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
            <!-- Left Side: Buttons -->
            <div class="flex items-center gap-4 order-2 md:order-1">
                <button @click="deleteModal = true" type="button" class="px-6 py-3 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-xl text-xs font-black flex items-center gap-2 hover:bg-red-50 hover:text-red-500 transition-all">
                    {{ __('Delete Photo') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
                
                <button @click="$refs.avatarInput.click()" class="px-8 py-3 bg-[#1A1A31] dark:bg-primary text-white rounded-xl text-xs font-black flex items-center gap-2 hover:opacity-90 transition-all shadow-lg shadow-indigo-500/10">
                    {{ __('Edit Photo') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </button>
                <form id="avatarForm" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" x-ref="avatarInput" name="avatar" @change="$refs.avatarFormSubmit.click()">
                    <button type="submit" x-ref="avatarFormSubmit"></button>
                </form>
            </div>

            <!-- Right Side: User Info -->
            <div class="flex items-center gap-6 order-1 md:order-2">
                <div class="text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ $user->name }}</h3>
                    <p class="text-slate-400 dark:text-slate-400 text-sm font-bold mt-1">{{ __('Admin') }}</p>
                </div>
                <div class="w-20 h-20 rounded-2xl bg-[#1A1A31] dark:bg-primary text-white flex items-center justify-center text-3xl font-black shadow-xl shadow-indigo-500/20 overflow-hidden">
                    @if($user->avatar)
                        <img src="{{ asset($user->avatar) }}" class="w-full h-full object-cover">
                    @else
                        {{ mb_substr($user->name ?? 'A', 0, 1) }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Container -->
    <div class="flex flex-col gap-6">
        <!-- Tab Switcher -->
        <div class="flex items-center {{ app()->getLocale() == 'ar' ? 'justify-end' : 'justify-start' }} gap-4">
            <button @click="tab = 'password'" :class="tab == 'password' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-lg' : 'bg-white dark:bg-white/5 text-slate-400 font-bold border border-slate-100 dark:border-white/5'" class="px-8 py-3 rounded-xl text-sm font-black transition-all">
                {{ __('Password') }}
            </button>
            <button @click="tab = 'details'" :class="tab == 'details' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-lg' : 'bg-white dark:bg-white/5 text-slate-400 font-bold border border-slate-100 dark:border-white/5'" class="px-8 py-3 rounded-xl text-sm font-black transition-all">
                {{ __('Account Details') }}
            </button>
        </div>

        <!-- Account Details Tab -->
        <div x-show="tab == 'details'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- First Name -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('First Name') }}
                        </label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->adminProfile->first_name ?? '') }}" placeholder="{{ __('Enter First Name') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    </div>
                    <!-- Last Name -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Last Name') }}
                        </label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->adminProfile->last_name ?? '') }}" placeholder="{{ __('Enter Last Name') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    </div>
                </div>

                <!-- Mobile Number -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Mobile Number') }}
                    </label>
                    <div class="relative">
                        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="5xxxx-xxxx" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <span class="absolute {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm" dir="ltr">+966</span>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-start gap-4 pt-4">
                    <button type="submit" class="px-8 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-xl text-sm font-black hover:opacity-90 transition-all shadow-lg shadow-indigo-500/10 min-w-[120px]">
                        {{ __('Save') }}
                    </button>
                    <button type="reset" class="px-8 py-4 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-xl text-sm font-black hover:bg-slate-200 transition-all min-w-[120px]">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Tab -->
        <div x-show="tab == 'password'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm">
            <form action="{{ route('admin.profile.update-password') }}" method="POST" class="space-y-8">
                @csrf
                <div class="space-y-6 max-w-2xl">
                    <!-- Current Password -->
                    <div class="space-y-3" x-data="{ show: false }">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Current Password') }}
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="current_password" placeholder="••••••••" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <button type="button" @click="show = !show" class="absolute {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary dark:hover:text-white transition-all">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                            </button>
                        </div>
                    </div>
                    <!-- New Password -->
                    <div class="space-y-3" x-data="{ show: false }">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('New Password') }}
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" placeholder="••••••••" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <button type="button" @click="show = !show" class="absolute {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary dark:hover:text-white transition-all">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                            </button>
                        </div>
                    </div>
                    <!-- Confirm Password -->
                    <div class="space-y-3" x-data="{ show: false }">
                        <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            {{ __('Confirm Password') }}
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" placeholder="••••••••" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <button type="button" @click="show = !show" class="absolute {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary dark:hover:text-white transition-all">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-start gap-4 pt-4">
                    <button type="submit" class="px-8 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-xl text-sm font-black hover:opacity-90 transition-all shadow-lg shadow-indigo-500/10 min-w-[120px]">
                        {{ __('Update Password') }}
                    </button>
                    <button type="reset" class="px-8 py-4 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-xl text-sm font-black hover:bg-slate-200 transition-all min-w-[120px]">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
