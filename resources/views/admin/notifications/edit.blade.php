@extends('layouts.admin')

@section('title', __('Edit Notification'))
@section('page_title', __('Edit Notification'))

@section('content')
<div class="max-w-4xl mx-auto pb-20" x-data="{ 
    type: '{{ $item->type }}',
    audience: '{{ $item->target_audience }}',
    status: '{{ $item->status }}'
}">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('admin.broadcast-notifications.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-primary transition-colors font-bold group">
            <div class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center border border-slate-100 dark:border-white/5 group-hover:bg-primary/10">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </div>
            <span class="text-xl font-black text-slate-800 dark:text-white">{{ __('Edit Notification Data') }}</span>
        </a>
    </div>

    <form action="{{ route('admin.broadcast-notifications.update', $item->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 md:p-12 border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="mb-10 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">{{ __('Notification Data') }}</h3>
                <div class="h-1.5 w-12 bg-primary rounded-full"></div>
            </div>

            <div class="space-y-8">
                <!-- Type and Audience Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Notification Type -->
                    <div class="space-y-4">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Notification Type') }}</label>
                        <select name="type" required 
                                class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none border-r-[16px] border-r-transparent">
                            <option value="">{{ __('Select Notification Type') }}</option>
                            @foreach(['alert', 'reminder', 'notification'] as $t)
                                <option value="{{ $t }}" {{ old('type', $item->type) == $t ? 'selected' : '' }}>{{ __($t) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Target Audience -->
                    <div class="space-y-4">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Target Audience') }}</label>
                        <select name="target_audience" required 
                                class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none border-r-[16px] border-r-transparent">
                            <option value="">{{ __('Select Target Audience') }}</option>
                            @foreach(['all', 'clients', 'companies', 'technicians'] as $aud)
                                <option value="{{ $aud }}" {{ old('target_audience', $item->target_audience) == $aud ? 'selected' : '' }}>{{ __($aud) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Title Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Notification Title (Arabic)') }}</label>
                        <input type="text" name="title_ar" value="{{ old('title_ar', $item->title_ar) }}" required placeholder="{{ __('Enter Title') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                    <div class="space-y-3 text-left">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Notification Title (English)') }}</label>
                        <input type="text" name="title_en" value="{{ old('title_en', $item->title_en) }}" required dir="ltr" placeholder="{{ __('Enter Title') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                </div>

                <!-- Body Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Notification Body (Arabic)') }}</label>
                        <textarea name="body_ar" rows="4" required placeholder="{{ __('Enter Body') }}"
                                  class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none resize-none">{{ old('body_ar', $item->body_ar) }}</textarea>
                    </div>
                    <div class="space-y-3 text-left">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Notification Body (English)') }}</label>
                        <textarea name="body_en" rows="4" required dir="ltr" placeholder="{{ __('Enter Body') }}"
                                  class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none resize-none">{{ old('body_en', $item->body_en) }}</textarea>
                    </div>
                </div>

                <!-- Status -->
                <div class="pt-4">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2 mb-4">{{ __('Status') }}</label>
                    <select name="status" required 
                            class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none border-r-[16px] border-r-transparent">
                        <option value="">{{ __('Select Status') }}</option>
                        @foreach(['sent', 'scheduled', 'unsent'] as $s)
                            <option value="{{ $s }}" {{ old('status', $item->status) == $s ? 'selected' : '' }}>{{ __($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4 mt-12 pt-8 border-t border-slate-100 dark:border-white/5">
                <button type="submit" class="flex-1 py-5 bg-[#1A1A31] text-white rounded-[2rem] font-black hover:bg-black transition-all shadow-xl shadow-black/20 uppercase tracking-widest text-sm">
                    {{ __('Save') }}
                </button>
                <a href="{{ route('admin.broadcast-notifications.index') }}" class="px-12 py-5 bg-slate-100 dark:bg-white/5 text-slate-500 rounded-[2rem] font-black hover:bg-slate-200 transition-all uppercase tracking-widest text-sm text-center">
                    {{ __('Cancel') }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
