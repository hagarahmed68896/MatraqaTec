<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Login') }} - {{ __('MatraqaTec') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .bg-gradient-premium {
            background: radial-gradient(circle at top right, #1A1A31 0%, #0F0F1E 100%);
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#0F0F1E] transition-colors duration-500 overflow-x-hidden">
    
    <!-- Animated Background Orbs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none opacity-0 dark:opacity-40 transition-opacity duration-1000">
        <div class="absolute top-[-5%] left-[-5%] w-[30%] h-[30%] bg-secondary/30 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-5%] right-[-5%] w-[40%] h-[40%] bg-primary-light/20 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Main Layout -->
    <div class="relative min-h-screen flex items-center justify-center p-4 md:p-6 lg:p-10">
        
        <!-- Premium Login Card -->
        <div class="w-full max-w-6xl flex flex-col lg:flex-row-reverse bg-white dark:bg-white/5 backdrop-blur-3xl rounded-[2.5rem] shadow-[0_20px_100px_-10px_rgba(0,0,0,0.2)] border border-slate-200 dark:border-white/10 overflow-hidden animate-in fade-in zoom-in duration-700">
            
            <!-- Left Side-ish: Form -->
            <div class="w-full lg:w-1/2 p-10 lg:p-12 flex flex-col justify-between">
                
                <!-- Card Header -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <button id="themeToggle" class="w-10 h-10 glass-card dark:glass-card-dark rounded-xl flex items-center justify-center text-slate-500 dark:text-slate-300 hover:scale-110 active:scale-95 transition-all">
                            <svg id="sunIcon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.727 12.727L12 12l8.485 8.485z"></path></svg>
                            <svg id="moonIcon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
                        </button>

                        <!-- Lang Dropdown -->
                        <div class="relative group">
                            <button class="glass-card dark:glass-card-dark px-4 py-2 rounded-xl flex items-center gap-2 text-slate-700 dark:text-slate-200 text-xs font-black hover:bg-slate-50 dark:hover:bg-white/10 transition-all">
                                <span>{{ app()->getLocale() == 'ar' ? 'العربية' : 'English' }}</span>
                                <svg class="w-3 h-3 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="absolute {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} mt-2 w-40 bg-white dark:bg-[#1A1A31] rounded-xl shadow-2xl border border-slate-100 dark:border-white/10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                                <a href="{{ route('admin.switch-language', 'ar') }}" class="block px-5 py-3 text-xs font-bold text-slate-700 dark:text-white hover:bg-slate-50 dark:hover:bg-white/5 transition-colors {{ app()->getLocale() == 'ar' ? 'bg-primary/5 dark:bg-white/10' : '' }}">العربية</a>
                                <a href="{{ route('admin.switch-language', 'en') }}" class="block px-5 py-3 text-xs font-bold text-slate-700 dark:text-white hover:bg-slate-50 dark:hover:bg-white/5 transition-colors {{ app()->getLocale() == 'en' ? 'bg-primary/5 dark:bg-white/10' : '' }}">English</a>
                            </div>
                        </div>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-10 brightness-0 dark:brightness-0 dark:invert transition-all duration-500">
                </div>

                <!-- Form Content -->
                <div class="max-w-md mx-auto w-full flex-grow flex flex-col justify-center py-6">
                    <div class="mb-8 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <h1 class="text-4xl font-black text-slate-900 dark:text-white mb-2 tracking-tight leading-tight">{{ __('Login') }}</h1>
                        <p class="text-slate-500 dark:text-slate-400 text-lg font-medium leading-relaxed">{{ __('Enter details to access dashboard.') }}</p>
                    </div>

                    @if(session('error'))
                        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center gap-3 text-red-500 animate-shake">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <p class="text-sm font-bold">{{ session('error') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('admin.post-login') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="space-y-3">
                            <label class="block text-sm font-black text-slate-800 dark:text-slate-200" for="phone">{{ __('Phone Number') }}</label>
                            <div class="relative group">
                                <span class="absolute {{ app()->getLocale() == 'ar' ? 'right-6' : 'left-6' }} top-1/2 -translate-y-1/2 text-slate-400 font-black text-lg group-focus-within:text-primary transition-colors">966+</span>
                                <input type="text" name="phone" id="phone" required maxlength="9" inputmode="numeric" placeholder="5XXXXXXXX" class="w-full {{ app()->getLocale() == 'ar' ? 'pr-20' : 'pl-20' }} px-6 py-5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-3xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg font-bold dark:text-white">
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-sm font-black text-slate-800 dark:text-slate-200" for="password">{{ __('Password') }}</label>
                            <div class="relative group">
                                <input type="password" name="password" id="password" required placeholder="••••••••" class="w-full px-6 py-5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-3xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg font-bold dark:text-white">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" name="remember" class="peer sr-only">
                                    <div class="w-6 h-6 border-2 border-slate-300 dark:border-white/20 rounded-lg peer-checked:bg-secondary peer-checked:border-secondary transition-all"></div>
                                    <svg class="absolute inset-0 w-6 h-6 p-1 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="mx-2 text-sm text-slate-600 dark:text-slate-400 font-bold group-hover:text-primary transition-colors">{{ __('Remember Me') }}</span>
                            </label>
                            <a href="{{ route('admin.forgot-password') }}" class="text-sm text-slate-400 hover:text-primary transition-colors font-black underline underline-offset-4 decoration-slate-200 dark:decoration-white/10">{{ __('Forgot Password?') }}</a>
                        </div>

                        <button type="submit" class="w-full py-5 bg-primary text-white rounded-3xl text-xl font-black hover:bg-primary-light transform active:scale-[0.98] transition-all shadow-xl shadow-primary/20">
                            {{ __('Login') }}
                        </button>
                    </form>
                </div>

                <!-- Card Footer (Empty) -->
                <div class="text-center pt-4 opacity-0 pointer-events-none">
                    <p class="text-slate-400 dark:text-white/20 font-bold text-xs uppercase tracking-widest">{{ __('MatraqaTec Admin') }}</p>
                </div>
            </div>

            <!-- Right Side-ish: Illustration -->
            <div class="w-full lg:w-1/2 bg-[#1A1A31] relative hidden lg:flex flex-col items-center justify-center p-12 text-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-premium opacity-50"></div>
                
                <div class="relative z-10 w-full max-w-sm">
                    <div class="mb-10 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <h2 class="text-3xl font-black text-white mb-2 leading-tight tracking-tight">
                            {{ app()->getLocale() == 'ar' ? 'لوحة تحكم مطرقة تك' : 'MatraqaTec Control Panel' }}
                        </h2>
                        <p class="text-white/80 text-sm font-medium leading-relaxed">
                            {{ app()->getLocale() == 'ar' ? 'نظام متكامل لإدارة عمليات الصيانة، الشركات والفنيين بكفاءة.' : 'Integrated system for efficient maintenance, company, and technician management.' }}
                        </p>
                    </div>

                    <div class="mb-8 inline-block p-3 glass-card rounded-3xl shadow-2xl animate-float">
                        <img src="{{ asset('assets/images/dashboard-preview-dark.png') }}" alt="Preview" class="rounded-2xl w-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        const updateTheme = () => {
            const isDark = localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
        };

        const toggleTheme = () => {
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        };

        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        updateTheme();
    </script>
</body>
</html>
