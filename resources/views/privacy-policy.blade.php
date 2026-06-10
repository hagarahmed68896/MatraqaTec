<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Privacy Policy') }} - {{ __('Matraqa') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/dubai-font" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Dubai', 'Tajawal', sans-serif; overflow-x: hidden; }
        .bg-gradient-premium {
            background: linear-gradient(135deg, rgba(26, 26, 49, 0.02) 0%, rgba(74, 74, 92, 0.05) 100%);
        }
        .dark .bg-gradient-premium {
            background: linear-gradient(135deg, rgba(26, 26, 49, 0.5) 0%, rgba(17, 17, 34, 0.8) 100%);
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#111122] text-[#1A1A31] dark:text-white antialiased transition-colors duration-300 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="w-full z-50 bg-white dark:bg-[#1A1A31] border-b border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300">
        <div class="h-24 max-w-7xl mx-auto px-6 flex justify-between items-center flex-row-reverse">
            <!-- Logo (Right in RTL) -->
            <a href="/" class="flex items-center">
                <img :src="darkMode ? '{{ asset('assets/images/163cf4a33948b9671c182f9d0aa410eaab570a58.png') }}' : '{{ asset('assets/images/41b68e035292ba7aa97a9bb8b16143cb90992358.png') }}'" 
                     :class="darkMode ? 'h-24 md:h-28' : 'h-12 md:h-14'" 
                     class="object-contain transition-all duration-500" alt="MatraqaTec">
            </a>
            
            <!-- Actions (Left in RTL) -->
            <div class="flex items-center gap-4">
                <!-- Return Home link -->
                <a href="/" class="text-sm font-bold text-[#1A1A31] dark:text-slate-300 hover:text-green-500 dark:hover:text-green-400 transition-colors">
                    {{ __('Home') }}
                </a>

                <!-- Theme Toggle -->
                <button @click="darkMode = !darkMode" class="w-10 h-10 flex items-center justify-center border border-slate-200 
                dark:border-slate-700 rounded-xl bg-[#1A1A31] dark:bg-[#1A1A31] text-white dark:text-slate-300 transition-all hover:bg-opacity-90 active:scale-95">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <svg x-show="darkMode" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.727 12.727L12 12l8.485 8.485z"></path></svg>
                </button>

                <!-- Language Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="h-10 px-4 flex items-center justify-center border border-slate-200 
                    dark:border-slate-700 rounded-xl bg-[#1A1A31] dark:bg-[#1A1A31] text-white dark:text-slate-300 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe mx-1" viewBox="0 0 16 16">
                            <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7.03 7.03 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5zm6.853 3.472A7 7 0 0 0 13.745 12H11.91a9.3 9.3 0 0 1-.64 1.539 7 7 0 0 1-.597.933M8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z"/>
                        </svg>
                        <span class="text-sm font-bold mx-1">
                            @if(app()->getLocale() == 'ar')
                                العربية
                            @else
                                EN
                            @endif
                        </span>
                    </button>

                    <div x-show="open" @click.outside="open=false"
                         class="absolute mt-2 w-32 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 p-1 z-50 overflow-hidden {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }}">
                        <a href="{{ route('admin.switch-language', 'ar') }}" class="block px-4 py-2 text-sm font-medium text-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">العربية</a>
                        <a href="{{ route('admin.switch-language', 'en') }}" class="block px-4 py-2 text-sm font-medium text-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">English</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content Header Section -->
    <header class="py-12 bg-white dark:bg-[#1A1A31] border-b border-slate-100 dark:border-slate-800 transition-colors duration-300">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h1 class="text-3xl md:text-5xl font-black text-[#1A1A31] dark:text-white leading-tight mb-4">
                {{ __('Privacy Policy') }}
            </h1>
            <p class="text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto">
                {{ __('We care about your privacy. Read our policy details to understand how we protect and handle your personal data.') }}
            </p>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-6 py-12">
        <!-- Main Content container -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-3xl border border-slate-100 dark:border-slate-800/80 shadow-xl p-8 md:p-12 transition-colors duration-300">
            @if($policies->count() > 0)
                <div class="space-y-10">
                    @foreach($policies as $policy)
                        <div class="border-b last:border-0 border-slate-100 dark:border-slate-800/50 pb-8 last:pb-0">
                            <h2 class="text-2xl font-black mb-4 text-[#1A1A31] dark:text-white">
                                {{ app()->getLocale() == 'ar' ? ($policy->title_ar ?? $policy->title_en) : ($policy->title_en ?? $policy->title_ar) }}
                            </h2>
                            <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 leading-relaxed space-y-4 text-base md:text-lg">
                                {!! nl2br(e(app()->getLocale() == 'ar' ? ($policy->content_ar ?? $policy->content_en) : ($policy->content_en ?? $policy->content_ar))) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[#1A1A31] dark:bg-[#0B0B1A] py-12 text-white transition-colors duration-300 border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col items-center text-center space-y-8">
                <img src="{{ asset('assets/images/logo-02 1.svg') }}" class="h-24 brightness-0 invert" alt="MatraqaTec">
                
                <p class="text-slate-300 text-sm max-w-xl mx-auto leading-relaxed italic opacity-85">
                    "{{ __('MatraqaTec is a leading service platform in the region that connects you with the best specialized technicians.') }}"
                </p>

                <!-- Quick App Downloads -->
                <div class="flex gap-4">
                    <a href="#" class="h-9 opacity-80 hover:opacity-100 transition-opacity">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="App Store" class="h-9">
                    </a>
                    <a href="#" class="h-9 opacity-80 hover:opacity-100 transition-opacity">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" class="h-9">
                    </a>
                </div>
            </div>
            
            <div class="flex md:flex-row flex-col mt-8 justify-between items-center border-t border-white/10 pt-8 gap-4 text-slate-400 font-bold text-xs tracking-wide">
                <div class="uppercase tracking-widest text-center md:text-right">&copy; 2026 {{ __('MatraqaTec') }} - {{ __('All rights reserved.') }}</div>
                
                <!-- Social Links -->
                <div class="flex items-center gap-4">
                    @foreach($social_links as $link)
                        @php
                            $icon = strtolower($link->icon);
                            $url = $link->url;
                        @endphp
                        <a href="{{ $url }}" target="_blank" class="w-8 h-8 rounded-full bg-white flex items-center justify-center hover:scale-110 transition-transform shadow-lg" title="{{ $link->name }}">
                            @if(str_contains($icon, 'instagram'))
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" fill="url(#ig-gradient-{{ $link->id }})"/>
                                    <defs>
                                        <linearGradient id="ig-gradient-{{ $link->id }}" x1="12" y1="0" x2="12" y2="24" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#405DE6"/><stop offset="0.25" stop-color="#5851DB"/><stop offset="0.5" stop-color="#833AB4"/><stop offset="0.75" stop-color="#E1306C"/><stop offset="1" stop-color="#F77737"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            @elseif(str_contains($icon, 'twitter') || str_contains($icon, 'x'))
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="black"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            @elseif(str_contains($icon, 'linkedin'))
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#0077B5"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            @elseif(str_contains($icon, 'tiktok'))
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.673a2.893 2.893 0 0 1-5.201 1.743l-.002-.001.002.001a2.893 2.893 0 0 1 3.183-4.51v-3.5a6.329 6.329 0 0 0-3.932 1.321 6.393 6.393 0 0 0-2.688 5.174c0 3.533 2.864 6.397 6.398 6.397 3.535 0 6.391-2.857 6.398-6.39v-6.182a8.182 8.182 0 0 0 4.745 1.509v-3.448a4.83 4.83 0 0 1-1.687-.301z" fill="black"/>
                                </svg>
                            @elseif(str_contains($icon, 'facebook'))
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            @else
                                <span class="text-[10px] uppercase font-bold text-black">{{ substr($link->name, 0, 2) }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
