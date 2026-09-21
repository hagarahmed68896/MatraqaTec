@extends('layouts.admin')

@section('title', __('Platform Settings') . ' - ' . __('MatraqaTec'))

@section('content')
<div x-data="{ 
    tab: '{{ request('tab', 'platform') }}',
    app_tab: 'client',
    lang: '{{ old('default_language', $items['default_language'] ?? 'ar') }}',
    mode: '{{ old('system_mode', $items['system_mode'] ?? 'light') }}',
    reminder: '{{ old('reminder_type', $items['reminder_type'] ?? 'day') }}'
}" class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">

    <!-- Page Title & Navigation Tabs -->
    <div class="flex flex-col items-center justify-center gap-4 py-2">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Platform Settings') }}</h1>
        
        <!-- Main Tabs Header -->
        <div class="flex items-center bg-white dark:bg-[#1A1A31] p-1.5 rounded-2xl border border-slate-100 dark:border-white/5 shadow-sm">
            <button type="button" @click="tab = 'platform'" :class="tab == 'platform' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-md' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'" class="flex items-center gap-2 py-2.5 px-6 rounded-xl text-sm font-black transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>{{ __('General Settings') }}</span>
            </button>
            <button type="button" @click="tab = 'app_versions'" :class="tab == 'app_versions' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-md' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'" class="flex items-center gap-2 py-2.5 px-6 rounded-xl text-sm font-black transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>{{ __('App Versions & Updates') }}</span>
            </button>
        </div>
    </div>

    <!-- Settings Container -->
    <div class="bg-white dark:bg-[#1A1A31] p-8 md:p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm max-w-4xl mx-auto">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
            @csrf
            <input type="hidden" name="tab" :value="tab">

            <!-- ================= TAB 1: General Platform Settings ================= -->
            <div x-show="tab === 'platform'" class="space-y-8 animate-in fade-in duration-300">
                <!-- Default Language -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Default Language for Platform') }}
                    </label>
                    <div class="flex items-center bg-slate-50 dark:bg-white/5 p-1.5 rounded-2xl gap-2">
                        <input type="hidden" name="default_language" :value="lang">
                        <button type="button" @click="lang = 'en'" :class="lang == 'en' ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-400'" class="flex-1 py-3 px-6 rounded-xl text-sm font-black transition-all">
                            {{ __('English Language') }}
                        </button>
                        <button type="button" @click="lang = 'ar'" :class="lang == 'ar' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-lg' : 'text-slate-400'" class="flex-1 py-3 px-6 rounded-xl text-sm font-black transition-all">
                            {{ __('Arabic Language') }}
                        </button>
                    </div>
                </div>

                <!-- System Mode -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('System Mode') }}
                    </label>
                    <div class="flex items-center bg-slate-50 dark:bg-white/5 p-1.5 rounded-2xl gap-2">
                        <input type="hidden" name="system_mode" :value="mode">
                        <button type="button" @click="mode = 'auto'" :class="mode == 'auto' ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-400'" class="flex-1 py-3 px-4 rounded-xl text-sm font-black transition-all whitespace-nowrap">
                            {{ __('Automatic (Based on Device)') }}
                        </button>
                        <button type="button" @click="mode = 'dark'" :class="mode == 'dark' ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-400'" class="flex-1 py-3 px-4 rounded-xl text-sm font-black transition-all">
                            {{ __('Night Mode') }}
                        </button>
                        <button type="button" @click="mode = 'light'" :class="mode == 'light' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-lg' : 'text-slate-400'" class="flex-1 py-3 px-4 rounded-xl text-sm font-black transition-all">
                            {{ __('Day Mode') }}
                        </button>
                    </div>
                </div>

                <!-- Order Acceptance Duration -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Order Acceptance Duration') }}
                    </label>
                    <div class="relative group">
                        <input type="number" name="order_acceptance_duration" value="{{ old('order_acceptance_duration', $items['order_acceptance_duration'] ?? '') }}" placeholder="{{ __('Order Acceptance Duration') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <div class="absolute {{ app()->getLocale() == 'ar' ? 'left-6 border-r pr-6' : 'right-6 border-l pl-6' }} top-1/2 -translate-y-1/2 border-slate-200 dark:border-white/10 text-slate-400 font-bold text-sm">
                            {{ __('Minute') }}
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-bold text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Specify the time allowed for the technician to accept the order (in minutes)') }}
                    </p>
                </div>

                <!-- Required Photos Before -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Required Photos Before Work') }}
                    </label>
                    <input type="number" name="required_photos_before_count" value="{{ old('required_photos_before_count', $items['required_photos_before_count'] ?? '') }}" placeholder="{{ __('Enter number of photos required before work') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                </div>

                <!-- Required Photos After -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Required Photos After Work') }}
                    </label>
                    <input type="number" name="required_photos_after_count" value="{{ old('required_photos_after_count', $items['required_photos_after_count'] ?? '') }}" placeholder="{{ __('Enter number of photos required after finishing work') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                </div>

                <!-- Appointment Reminder -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Appointment Reminder') }}
                    </label>
                    <div class="space-y-3 bg-slate-50 dark:bg-white/5 p-6 rounded-[2rem] border border-slate-100 dark:border-white/5">
                        <div class="flex items-center justify-between p-4 bg-white dark:bg-[#1A1A31] rounded-2xl border border-slate-100 dark:border-white/10 shadow-sm cursor-pointer" @click="reminder = 'day'">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all" :class="reminder == 'day' ? 'border-primary' : 'border-slate-200 dark:border-white/10'">
                                    <div x-show="reminder == 'day'" class="w-3 h-3 rounded-full bg-primary"></div>
                                </div>
                                <span class="text-sm font-black text-slate-700 dark:text-white">{{ __('Before Day') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-white dark:bg-[#1A1A31] rounded-2xl border border-slate-100 dark:border-white/10 shadow-sm cursor-pointer" @click="reminder = 'hour'">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all" :class="reminder == 'hour' ? 'border-primary' : 'border-slate-200 dark:border-white/10'">
                                    <div x-show="reminder == 'hour'" class="w-3 h-3 rounded-full bg-primary"></div>
                                </div>
                                <span class="text-sm font-black text-slate-700 dark:text-white">{{ __('Before Hour') }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-[#1A1A31] rounded-2xl border border-slate-100 dark:border-white/10 shadow-sm cursor-pointer" @click="reminder = 'custom'">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all" :class="reminder == 'custom' ? 'border-primary' : 'border-slate-200 dark:border-white/10'">
                                        <div x-show="reminder == 'custom'" class="w-3 h-3 rounded-full bg-primary"></div>
                                    </div>
                                    <span class="text-sm font-black text-slate-700 dark:text-white">{{ __('Other') }}</span>
                                </div>
                            </div>
                            <div x-show="reminder == 'custom'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="px-4">
                                <div class="relative group">
                                    <input type="number" name="reminder_custom_value" value="{{ old('reminder_custom_value', $items['reminder_custom_value'] ?? '') }}" placeholder="{{ __('Select reminder time before appointment') }}" class="w-full bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                                    <div class="absolute {{ app()->getLocale() == 'ar' ? 'left-6 border-r pr-6' : 'right-6 border-l pl-6' }} top-1/2 -translate-y-1/2 border-slate-200 dark:border-white/10 text-slate-400 font-bold text-sm">
                                        {{ __('Minute') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="reminder_type" :value="reminder">
                    </div>
                </div>

                <!-- Copyright Text (Arabic) -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Copyright Text (Arabic)') }}
                    </label>
                    <input type="text" name="copyright_text_ar" value="{{ old('copyright_text_ar', $items['copyright_text_ar'] ?? '') }}" placeholder="{{ __('Enter copyright text for Arabic version') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-right" dir="rtl">
                </div>

                <!-- Copyright Text (English) -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        {{ __('Copyright Text (English)') }}
                    </label>
                    <input type="text" name="copyright_text_en" value="{{ old('copyright_text_en', $items['copyright_text_en'] ?? '') }}" placeholder="{{ __('Enter copyright text for English version') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-left" dir="ltr">
                </div>
            </div>

            <!-- ================= TAB 2: App Versions & Updates ================= -->
            <div x-show="tab === 'app_versions'" class="space-y-8 animate-in fade-in duration-300">
                
                <!-- Info Notice Box -->
                <div class="p-5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-800/50 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-black text-slate-800 dark:text-white">{{ __('App Version & Force Update System') }}</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ __('When a user opens the app, if their version is strictly lower than the "Minimum Supported Version", they will be blocked and prompted to update immediately. To enforce update on all users, set "Minimum Supported Version" equal to "Latest Version".') }}
                        </p>
                    </div>
                </div>

                <!-- 3 Apps Sub-Tabs Header -->
                <div class="flex items-center bg-slate-100 dark:bg-white/5 p-1.5 rounded-2xl gap-2">
                    <button type="button" @click="app_tab = 'client'" :class="app_tab == 'client' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-md' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'" class="flex-1 py-3 px-4 rounded-xl text-xs md:text-sm font-black transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>{{ __('Customer App') }} (Matraqa Tech)</span>
                    </button>
                    <button type="button" @click="app_tab = 'company'" :class="app_tab == 'company' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-md' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'" class="flex-1 py-3 px-4 rounded-xl text-xs md:text-sm font-black transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span>{{ __('Company App') }} (Matraqa Company)</span>
                    </button>
                    <button type="button" @click="app_tab = 'technician'" :class="app_tab == 'technician' ? 'bg-[#1A1A31] dark:bg-primary text-white shadow-md' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'" class="flex-1 py-3 px-4 rounded-xl text-xs md:text-sm font-black transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        <span>{{ __('Technician App') }} (Matraqa Technician)</span>
                    </button>
                </div>

                <!-- ================= 1. CLIENT APP SECTION ================= -->
                <div x-show="app_tab === 'client'" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-primary"></span>
                            <span>{{ __('Customer App') }} (Matraqa Tech - Client)</span>
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">app=client</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Android Card (Client) -->
                        <div class="p-6 rounded-[2rem] bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/30 space-y-5">
                            <div class="flex items-center justify-between border-b border-emerald-100 dark:border-emerald-900/30 pb-3">
                                <span class="font-black text-slate-800 dark:text-white text-sm">Android (Google Play)</span>
                                <span class="px-3 py-0.5 text-[11px] font-black rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Google Play</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Latest Version') }} (Android)</label>
                                <input type="text" name="client_android_latest_version" value="{{ old('client_android_latest_version', $items['client_android_latest_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Minimum Supported Version') }} (Force Update)</label>
                                <input type="text" name="client_android_minimum_version" value="{{ old('client_android_minimum_version', $items['client_android_minimum_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Google Play Store URL') }}</label>
                                <input type="url" name="client_android_store_url" value="{{ old('client_android_store_url', $items['client_android_store_url'] ?? '') }}" placeholder="https://play.google.com/store/apps/details?id=com.isky.matraqa&pcampaignid=web_share" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                        </div>

                        <!-- iOS Card (Client) -->
                        <div class="p-6 rounded-[2rem] bg-sky-50/40 dark:bg-sky-950/10 border border-sky-100 dark:border-sky-900/30 space-y-5">
                            <div class="flex items-center justify-between border-b border-sky-100 dark:border-sky-900/30 pb-3">
                                <span class="font-black text-slate-800 dark:text-white text-sm">iOS (App Store)</span>
                                <span class="px-3 py-0.5 text-[11px] font-black rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">App Store</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Latest Version') }} (iOS)</label>
                                <input type="text" name="client_ios_latest_version" value="{{ old('client_ios_latest_version', $items['client_ios_latest_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Minimum Supported Version') }} (Force Update)</label>
                                <input type="text" name="client_ios_minimum_version" value="{{ old('client_ios_minimum_version', $items['client_ios_minimum_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Apple App Store URL') }}</label>
                                <input type="url" name="client_ios_store_url" value="{{ old('client_ios_store_url', $items['client_ios_store_url'] ?? '') }}" placeholder="https://apps.apple.com/app/matraqa-tech/id6766811584" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 2. COMPANY APP SECTION ================= -->
                <div x-show="app_tab === 'company'" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                            <span>{{ __('Company App') }} (Matraqa Company)</span>
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">app=company</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Android Card (Company) -->
                        <div class="p-6 rounded-[2rem] bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/30 space-y-5">
                            <div class="flex items-center justify-between border-b border-emerald-100 dark:border-emerald-900/30 pb-3">
                                <span class="font-black text-slate-800 dark:text-white text-sm">Android (Google Play)</span>
                                <span class="px-3 py-0.5 text-[11px] font-black rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Google Play</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Latest Version') }} (Android)</label>
                                <input type="text" name="company_android_latest_version" value="{{ old('company_android_latest_version', $items['company_android_latest_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Minimum Supported Version') }} (Force Update)</label>
                                <input type="text" name="company_android_minimum_version" value="{{ old('company_android_minimum_version', $items['company_android_minimum_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Google Play Store URL') }}</label>
                                <input type="url" name="company_android_store_url" value="{{ old('company_android_store_url', $items['company_android_store_url'] ?? '') }}" placeholder="https://play.google.com/store/apps/details?id=com.isky.matraqa.company&pcampaignid=web_share" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                        </div>

                        <!-- iOS Card (Company) -->
                        <div class="p-6 rounded-[2rem] bg-sky-50/40 dark:bg-sky-950/10 border border-sky-100 dark:border-sky-900/30 space-y-5">
                            <div class="flex items-center justify-between border-b border-sky-100 dark:border-sky-900/30 pb-3">
                                <span class="font-black text-slate-800 dark:text-white text-sm">iOS (App Store)</span>
                                <span class="px-3 py-0.5 text-[11px] font-black rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">App Store</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Latest Version') }} (iOS)</label>
                                <input type="text" name="company_ios_latest_version" value="{{ old('company_ios_latest_version', $items['company_ios_latest_version'] ?? '1.0.6') }}" placeholder="1.0.6" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Minimum Supported Version') }} (Force Update)</label>
                                <input type="text" name="company_ios_minimum_version" value="{{ old('company_ios_minimum_version', $items['company_ios_minimum_version'] ?? '1.0.6') }}" placeholder="1.0.6" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Apple App Store URL') }}</label>
                                <input type="url" name="company_ios_store_url" value="{{ old('company_ios_store_url', $items['company_ios_store_url'] ?? '') }}" placeholder="https://apps.apple.com/app/matraqa-company/id6766896302" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 3. TECHNICIAN APP SECTION ================= -->
                <div x-show="app_tab === 'technician'" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span>{{ __('Technician App') }} (Matraqa Technician)</span>
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">app=technician</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Android Card (Technician) -->
                        <div class="p-6 rounded-[2rem] bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/30 space-y-5">
                            <div class="flex items-center justify-between border-b border-emerald-100 dark:border-emerald-900/30 pb-3">
                                <span class="font-black text-slate-800 dark:text-white text-sm">Android (Google Play)</span>
                                <span class="px-3 py-0.5 text-[11px] font-black rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Google Play</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Latest Version') }} (Android)</label>
                                <input type="text" name="technician_android_latest_version" value="{{ old('technician_android_latest_version', $items['technician_android_latest_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Minimum Supported Version') }} (Force Update)</label>
                                <input type="text" name="technician_android_minimum_version" value="{{ old('technician_android_minimum_version', $items['technician_android_minimum_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Google Play Store URL') }}</label>
                                <input type="url" name="technician_android_store_url" value="{{ old('technician_android_store_url', $items['technician_android_store_url'] ?? '') }}" placeholder="https://play.google.com/store/apps/details?id=com.isky.matraqa.technician&pcampaignid=web_share" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono" dir="ltr">
                            </div>
                        </div>

                        <!-- iOS Card (Technician) -->
                        <div class="p-6 rounded-[2rem] bg-sky-50/40 dark:bg-sky-950/10 border border-sky-100 dark:border-sky-900/30 space-y-5">
                            <div class="flex items-center justify-between border-b border-sky-100 dark:border-sky-900/30 pb-3">
                                <span class="font-black text-slate-800 dark:text-white text-sm">iOS (App Store)</span>
                                <span class="px-3 py-0.5 text-[11px] font-black rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">App Store</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Latest Version') }} (iOS)</label>
                                <input type="text" name="technician_ios_latest_version" value="{{ old('technician_ios_latest_version', $items['technician_ios_latest_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Minimum Supported Version') }} (Force Update)</label>
                                <input type="text" name="technician_ios_minimum_version" value="{{ old('technician_ios_minimum_version', $items['technician_ios_minimum_version'] ?? '1.0.0') }}" placeholder="1.0.0" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-700 dark:text-slate-200">{{ __('Apple App Store URL') }}</label>
                                <input type="url" name="technician_ios_store_url" value="{{ old('technician_ios_store_url', $items['technician_ios_store_url'] ?? '') }}" placeholder="https://apps.apple.com/app/matraqa-technician/id6766959106" class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono" dir="ltr">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Messages Box -->
                <div class="p-6 rounded-[2rem] bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 space-y-4">
                    <h3 class="font-black text-slate-800 dark:text-white text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        <span>{{ __('Custom Update Message') }}</span>
                    </h3>

                    <!-- Message Arabic -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 text-right">
                            {{ __('Update Message (Arabic)') }}
                        </label>
                        <input type="text" name="app_update_message_ar" value="{{ old('app_update_message_ar', $items['app_update_message_ar'] ?? 'توجد نسخة جديدة من التطبيق. يرجى التحديث للمتابعة.') }}" placeholder="توجد نسخة جديدة من التطبيق. يرجى التحديث للمتابعة." class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-right" dir="rtl">
                    </div>

                    <!-- Message English -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 text-left">
                            {{ __('Update Message (English)') }}
                        </label>
                        <input type="text" name="app_update_message_en" value="{{ old('app_update_message_en', $items['app_update_message_en'] ?? 'A new version of the app is available. Please update to continue.') }}" placeholder="A new version of the app is available. Please update to continue." class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-left" dir="ltr">
                    </div>
                </div>

            </div>

            <!-- Form Actions -->
            <div class="flex items-center {{ app()->getLocale() == 'ar' ? 'justify-start' : 'justify-end' }} gap-4 pt-6 border-t border-slate-100 dark:border-white/5">
                <button type="submit" class="px-8 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-2xl text-sm font-black hover:opacity-90 transition-all shadow-lg shadow-indigo-500/10 min-w-[140px]">
                    {{ __('Save') }}
                </button>
                <button type="reset" class="px-8 py-4 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-2xl text-sm font-black hover:bg-slate-200 transition-all min-w-[140px]">
                    {{ __('Cancel') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection