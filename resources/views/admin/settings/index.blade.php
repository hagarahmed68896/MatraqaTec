@extends('layouts.admin')

@section('title', __('System Settings'))
@section('page_title', __('System Settings'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-8 shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- App Identity -->
                <div class="space-y-4">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white border-b border-slate-50 dark:border-white/5 pb-2">{{ __('App Identity') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('App Name (Arabic)') }}</label>
                            <input type="text" name="app_name_ar" value="{{ $data['app_name_ar'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('App Name (English)') }}</label>
                            <input type="text" name="app_name_en" value="{{ $data['app_name_en'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="space-y-4">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white border-b border-slate-50 dark:border-white/5 pb-2">{{ __('Contact Information') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Support Email') }}</label>
                            <input type="email" name="contact_email" value="{{ $data['contact_email'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Support Phone') }}</label>
                            <input type="text" name="contact_phone" value="{{ $data['contact_phone'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Platform Behavior -->
                <div class="space-y-4">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white border-b border-slate-50 dark:border-white/5 pb-2">{{ __('Order Settings') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Acceptance Duration (Mins)') }}</label>
                            <input type="number" name="order_acceptance_duration" value="{{ $data['order_acceptance_duration'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Photos Required (Before)') }}</label>
                            <input type="number" name="required_photos_before_count" value="{{ $data['required_photos_before_count'] }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- System Preferences -->
                <div class="space-y-4">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white border-b border-slate-50 dark:border-white/5 pb-2">{{ __('Localization') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Default Language') }}</label>
                            <select name="default_language" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                                <option value="ar" {{ $data['default_language'] == 'ar' ? 'selected' : '' }}>العربية</option>
                                <option value="en" {{ $data['default_language'] == 'en' ? 'selected' : '' }}>English</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('System Mode') }}</label>
                            <select name="system_mode" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                                <option value="light" {{ $data['system_mode'] == 'light' ? 'selected' : '' }}>Light</option>
                                <option value="dark" {{ $data['system_mode'] == 'dark' ? 'selected' : '' }}>Dark</option>
                                <option value="auto" {{ $data['system_mode'] == 'auto' ? 'selected' : '' }}>System Default</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-slate-100 dark:border-white/5">
                <button type="submit" class="px-12 py-4 bg-primary text-white rounded-[1.5rem] font-bold hover:bg-primary-light transition-all shadow-xl shadow-primary/25">
                    {{ __('Save All Settings') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection