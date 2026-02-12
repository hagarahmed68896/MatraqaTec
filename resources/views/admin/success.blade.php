<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Verification Successful') }} - {{ __('MatraqaTec') }}</title>
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
        
        <!-- Premium Success Card -->
        <div class="w-full max-w-6xl flex flex-col lg:flex-row-reverse bg-white dark:bg-white/5 backdrop-blur-3xl rounded-[2.5rem] shadow-[0_20px_100px_-10px_rgba(0,0,0,0.2)] border border-slate-200 dark:border-white/10 overflow-hidden animate-in fade-in zoom-in duration-700">
            
            <!-- Left Side: Success Message -->
            <div class="w-full lg:w-1/2 p-10 lg:p-12 flex flex-col justify-between items-center text-center">
                
                <!-- Card Header -->
                <div class="w-full flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <button id="themeToggle" class="w-10 h-10 glass-card dark:glass-card-dark rounded-xl flex items-center justify-center text-slate-500 dark:text-slate-300 hover:scale-110 active:scale-95 transition-all">
                            <svg id="sunIcon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.727 12.727L12 12l8.485 8.485z"></path></svg>
                            <svg id="moonIcon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
                        </button>

                        <!-- Lang Display (Static for Success) -->
                        <div class="glass-card dark:glass-card-dark px-4 py-2 rounded-xl flex items-center gap-2 text-slate-700 dark:text-slate-200 text-xs font-black">
                            <span>{{ app()->getLocale() == 'ar' ? 'العربية' : 'English' }}</span>
                        </div>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-10 brightness-0 dark:brightness-0 dark:invert transition-all duration-500">
                </div>

                <!-- Content -->
                <div class="max-w-md mx-auto w-full flex-grow flex flex-col justify-center py-6">
                    <!-- Dynamic Animated Checkmark Overlay -->
                    <div class="relative w-48 h-48 mx-auto mb-8 flex items-center justify-center">
                        <div class="absolute inset-0 bg-secondary/20 dark:bg-secondary/10 rounded-full animate-ping duration-1000"></div>
                        <div class="absolute inset-4 bg-secondary/30 dark:bg-secondary/20 rounded-full animate-pulse"></div>
                        <div class="relative w-32 h-32 bg-secondary rounded-full flex items-center justify-center shadow-2xl shadow-secondary/40 animate-in zoom-in spin-in-90 duration-700">
                            <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>

                    <h1 class="text-4xl font-black text-slate-900 dark:text-white mb-4 tracking-tight leading-tight">
                        {{ __('Verification Successful') }}
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-lg font-medium leading-relaxed mb-10">
                        {{ __('You can now access your dashboard.') }}
                    </p>

                    <a href="{{ route('admin.dashboard') }}" class="w-full py-5 bg-primary text-white rounded-3xl text-xl font-black h-20 flex items-center justify-center hover:bg-primary-light transform active:scale-[0.98] transition-all shadow-xl shadow-primary/20 group">
                        {{ __('Start Now') }}
                        <svg class="w-6 h-6 {{ app()->getLocale() == 'ar' ? 'mr-3' : 'ml-3' }} group-hover:translate-x-{{ app()->getLocale() == 'ar' ? '-2' : '2' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app()->getLocale() == 'ar' ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}"></path>
                        </svg>
                    </a>
                </div>

                <!-- Empty Footer for layout balance -->
                <div class="h-10"></div>
            </div>

            <!-- Right Side: Dashboard Preview Decoration -->
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
                    <!-- Decorative Dashboard Card -->
                    <div class="relative group">
                        <div class="absolute -inset-4 bg-gradient-to-tr from-secondary/50 via-primary/50 to-secondary/50 rounded-[2.5rem] blur-2xl opacity-30 group-hover:opacity-60 transition duration-1000"></div>
                        <div class="relative inline-block p-3 glass-card rounded-3xl shadow-2xl animate-float">
                            <img src="{{ asset('assets/images/dashboard-preview-dark.png') }}" alt="Preview" class="rounded-2xl w-full object-cover shadow-inner">
                        </div>
                    </div>
                </div>
                
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 p-8">
                    <div class="w-24 h-24 border border-white/10 rounded-full animate-spin-slow opacity-20"></div>
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
