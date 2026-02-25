@extends('layouts.admin')

@section('title', __('Social Links') . ' - ' . __('MatraqaTec'))

@section('content')
<div x-data="{ 
    mobile: '{{ old('contact_mobile', $contact['mobile'] ?? '') }}',
    email: '{{ old('contact_email', $contact['email'] ?? '') }}',
    socials: [
        @foreach($socialLinks as $link)
        { platform: '{{ $link->icon }}', name: '{{ $link->name }}', url: '{{ $link->url }}' },
        @endforeach
    ],
    newPlatform: '',
    newUrl: '',
    showPlatformDropdown: false,
    platforms: [
        { id: 'facebook', name: '{{ __('Facebook') }}', icon: 'facebook' },
        { id: 'instagram', name: '{{ __('Instagram') }}', icon: 'instagram' },
        { id: 'twitter', name: '{{ __('Twitter') }}', icon: 'twitter' },
        { id: 'linkedin', name: '{{ __('LinkedIn') }}', icon: 'linkedin' },
        { id: 'tiktok', name: '{{ __('TikTok') }}', icon: 'tiktok' }
    ],
    addSocial() {
        if (this.newPlatform && this.newUrl) {
            const platform = this.platforms.find(p => p.id === this.newPlatform);
            this.socials.push({ 
                platform: this.newPlatform, 
                name: platform ? platform.name : this.newPlatform, 
                url: this.newUrl 
            });
            this.newPlatform = '';
            this.newUrl = '';
        }
    },
    removeSocial(index) {
        this.socials.splice(index, 1);
    }
}" class="space-y-8 animate-in fade-in slide-in-from-bottom duration-700">

    <!-- Page Title -->
    <div class="flex items-center justify-center py-4">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Social Links') }}</h2>
    </div>

    <!-- Settings Card -->
    <div class="bg-white dark:bg-[#1A1A31] p-10 rounded-[2.5rem] border border-slate-100 dark:border-white/5 shadow-sm max-w-4xl mx-auto">
        <form action="{{ route('admin.social-links.update', 1) }}" method="POST" class="space-y-10">
            @csrf
            @method('PUT')
            
            <!-- Mobile Number -->
            <div class="space-y-4">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Mobile Number') }}
                </label>
                <div class="relative group">
                    <input type="text" name="contact_mobile" x-model="mobile" placeholder="{{ __('+966 Enter mobile number') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                </div>
            </div>

            <!-- Email Address -->
            <div class="space-y-4">
                <label class="block text-sm font-black text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Email Address') }}
                </label>
                <div class="relative group">
                    <input type="email" name="contact_email" x-model="email" placeholder="{{ __('Enter email address') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                </div>
            </div>

            <!-- Social Media Links -->
            <div class="space-y-6">
                <label class="block text-sm font-black mt-8 text-slate-800 dark:text-white text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    {{ __('Social Media Platform Links') }}
                </label>

                <!-- Platform Inputs Row -->
                <div class="flex flex-col md:flex-row items-center gap-4">
                    <!-- Add Button -->
                    <button type="button" @click="addSocial()" class="w-full md:w-auto px-8 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-xl text-sm font-black hover:opacity-90 transition-all shadow-lg active:scale-95">
                        {{ __('Add') }}
                    </button>

                    <!-- URL Input -->
                    <input type="text" x-model="newUrl" placeholder="{{ __('Enter platform link') }}" class="flex-1 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white placeholder-slate-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">

                    <!-- Platform Selector -->
                    <div class="relative w-full md:w-64" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.away="open = false" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white flex items-center justify-between transition-all">
                            <span x-text="newPlatform ? platforms.find(p => p.id === newPlatform).name : '{{ __('Select Platform') }}'"></span>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" x-transition class="absolute bottom-full mb-2 w-full bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50">
                            <div class="p-2 space-y-1">
                                <template x-for="p in platforms" :key="p.id">
                                    <button type="button" @click="newPlatform = p.id; open = false" class="w-full flex items-center justify-between px-4 py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-all group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-4 h-4 rounded-full border-2 border-slate-200 dark:border-white/10 flex items-center justify-center group-hover:border-primary transition-all">
                                                <div x-show="newPlatform === p.id" class="w-2 h-2 rounded-full bg-primary"></div>
                                            </div>
                                            <span class="text-xs font-black text-slate-600 dark:text-slate-300 group-hover:text-primary transition-all" x-text="p.name"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Links List -->
                <div class="space-y-4 pt-4">
                    <template x-for="(social, index) in socials" :key="index">
                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" :name="'social_links['+index+'][platform]'" :value="social.platform">
                            <input type="hidden" :name="'social_links['+index+'][name]'" :value="social.name">
                            <input type="hidden" :name="'social_links['+index+'][url]'" :value="social.url">

                            <!-- Delete Button -->
                            <button type="button" @click="removeSocial(index)" class="w-full md:w-14 h-14 flex items-center justify-center bg-slate-100 dark:bg-white/5 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all group active:scale-95">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>

                            <!-- Display Text -->
                            <div class="flex-1 bg-slate-50/50 dark:bg-white/5 border border-dashed border-slate-200 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-400 dark:text-slate-500 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                                <span x-text="social.url"></span>
                            </div>

                            <!-- Platform Fixed Display -->
                            <div class="w-full md:w-64 bg-slate-50/50 dark:bg-white/5 border border-dashed border-slate-200 dark:border-white/10 rounded-xl px-6 py-4 text-sm font-bold text-slate-300 dark:text-slate-600 flex items-center justify-between">
                                <span x-text="social.name"></span>
                                <svg class="w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-center gap-4 mt-4 pt-10 border-t border-slate-100 dark:border-white/5">
                <button type="button" onclick="window.history.back()" class="px-12 py-4 bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-500 rounded-2xl text-sm font-black hover:bg-slate-200 transition-all min-w-[140px]">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="px-12 py-4 bg-[#1A1A31] dark:bg-primary text-white rounded-2xl text-sm font-black hover:opacity-90 transition-all shadow-lg shadow-indigo-500/10 min-w-[140px] active:scale-95">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection