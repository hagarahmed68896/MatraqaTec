<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Verification') }} - {{ __('MatraqaTec') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .otp-input {
            @apply w-14 h-16 md:w-16 md:h-20 text-center text-3xl font-black rounded-2xl bg-white dark:bg-white/5 border-2 border-slate-200 dark:border-white/10 shadow-sm transition-all focus:outline-none focus:ring-0 focus:border-primary dark:focus:border-primary dark:text-white transform focus:scale-105;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .otp-input:focus {
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2), 0 4px 6px -2px rgba(79, 70, 229, 0.1);
        }
        .otp-input:not(:placeholder-shown) {
            @apply border-primary bg-primary/5 dark:bg-white/10;
        }
        .bg-gradient-premium {
            background: radial-gradient(circle at top right, #1A1A31 0%, #0F0F1E 100%);
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#0F0F1E] transition-colors duration-500 overflow-x-hidden">
    
    <div class="fixed inset-0 overflow-hidden pointer-events-none opacity-0 dark:opacity-40 transition-opacity duration-1000">
        <div class="absolute top-[-5%] left-[-5%] w-[30%] h-[30%] bg-secondary/30 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-5%] right-[-5%] w-[40%] h-[40%] bg-primary-light/20 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
        
        <div class="w-full max-w-5xl flex flex-col lg:flex-row-reverse bg-white dark:bg-white/5 backdrop-blur-3xl rounded-[3rem] shadow-[0_30px_100px_-20px_rgba(0,0,0,0.15)] border border-slate-200 dark:border-white/10 overflow-hidden animate-in fade-in zoom-in duration-700">
            
            <div class="w-full lg:w-1/2 p-10 lg:p-16 flex flex-col justify-between">
                
                <div class="flex items-center justify-between mb-10">
                    <button id="themeToggle" class="w-12 h-12 glass-card dark:glass-card-dark rounded-2xl flex items-center justify-center text-slate-500 dark:text-slate-300 hover:scale-110 active:scale-95 transition-all">
                        <svg id="sunIcon" class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.727 12.727L12 12l8.485 8.485z"></path></svg>
                        <svg id="moonIcon" class="w-6 h-6 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
                    </button>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-12 brightness-0 dark:brightness-0 dark:invert transition-all duration-500">
                </div>

                <div class="max-w-sm mx-auto w-full flex-grow flex flex-col justify-center py-4">
                    <div class="mb-10 text-center">
                        <div class="w-20 h-20 bg-primary/10 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-primary">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3c1.22 0 2.383.218 3.46.613m-3.46 7.387a4 4 0 11-8 0 4 4 0 018 0zM12 11c1.22 0 2.383-.218 3.46-.613M12 11V9c0-1.657 1.343-3 3-3m-3 5C10.78 11.26 9.31 11.74 8 12.5M12 11c1.33 0 2.66.41 3.84 1.25L21 17"></path></svg>
                        </div>
                        <h1 class="text-4xl font-black text-slate-900 dark:text-white mb-3 tracking-tight">{{ __('Verification') }}</h1>
                        <p class="text-slate-500 dark:text-slate-400 text-base font-medium leading-relaxed px-4">
                            {{ __('Enter the verification code sent to your phone') }}
                        </p>
                    </div>

                    @if(session('error'))
                        <div class="mb-8 p-5 bg-red-500/10 border border-red-500/20 rounded-[1.5rem] flex items-center gap-4 text-red-500 animate-shake">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <p class="text-sm font-bold leading-tight">{{ session('error') }}</p>
                        </div>
                    @endif

                    <form id="otpForm" action="{{ route('admin.post-verify') }}" method="POST" class="space-y-12">
                        @csrf
                        <div class="flex justify-center gap-2 md:gap-4 dir-ltr" id="otpContainer">
                            <input type="text" name="otp[]" maxlength="1" autofocus class="otp-input" inputmode="numeric" placeholder=" ">
                            <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric" placeholder=" ">
                            <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric" placeholder=" ">
                            <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric" placeholder=" ">
                        </div>

                        <div class="space-y-6">
                            <button type="submit" class="w-full py-5 bg-primary text-white rounded-[1.5rem] text-xl font-black hover:bg-primary-light transform active:scale-[0.98] transition-all shadow-xl shadow-primary/20">
                                {{ __('Verify') }}
                            </button>

                            <div class="text-center">
                                <button type="button" id="resendBtn" disabled class="text-slate-400 bg-slate-100 dark:bg-white/5 px-8 py-3 rounded-2xl text-sm font-bold cursor-not-allowed transition-all">
                                    {{ __('Resend Code') }} <span id="timer" class="text-primary font-black ml-1">60s</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-12 text-center">
                        <a href="{{ route('admin.login') }}" class="text-slate-400 hover:text-primary transition-colors font-black text-sm uppercase tracking-widest">{{ __('Back to Login') }}</a>
                    </div>
                </div>

                <div class="text-center mt-10 opacity-0 pointer-events-none">
                    <p class="text-xs text-slate-400 font-medium tracking-[0.3em] uppercase">{{ __('MatraqaTec Admin') }}</p>
                </div>
            </div>

            <div class="w-full lg:w-1/2 bg-[#1A1A31] relative hidden lg:flex flex-col items-center justify-center p-16 text-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-premium opacity-50"></div>
                <div class="relative z-10 w-full max-w-sm">
                    <div class="mb-12 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <h2 class="text-4xl font-black text-white mb-4 leading-tight tracking-tight">
                            {{ app()->getLocale() == 'ar' ? 'لوحة تحكم مطرقة تك' : 'MatraqaTec Control Panel' }}
                        </h2>
                        <p class="text-white/70 text-base font-medium leading-relaxed">
                            {{ app()->getLocale() == 'ar' ? 'نظام متكامل لإدارة عمليات الصيانة، الشركات والفنيين بكفاءة.' : 'Integrated system for efficient maintenance, company, and technician management.' }}
                        </p>
                    </div>
                    <div class="mb-10 inline-block p-4 glass-card rounded-[2.5rem] shadow-2xl animate-float">
                        <img src="{{ asset('assets/images/dashboard-preview-dark.png') }}" alt="Preview" class="rounded-3xl w-full object-cover">
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

        // OTP Input Logic
        const inputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('otpForm');

        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                
                // Auto-submit when all 4 are filled
                const allFilled = Array.from(inputs).every(i => i.value.length === 1);
                if (allFilled) {
                    setTimeout(() => form.submit(), 200);
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Allow only numbers
            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });

        // Timer Logic
        let seconds = 60;
        const timerSpan = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');
        const countdown = setInterval(() => {
            seconds--;
            if (timerSpan) timerSpan.textContent = `${seconds}s`;
            if (seconds <= 0) {
                clearInterval(countdown);
                resendBtn.disabled = false;
                resendBtn.classList.remove('cursor-not-allowed', 'text-slate-400');
                resendBtn.classList.add('text-primary', 'dark:text-white', 'cursor-pointer', 'bg-primary/5');
                if (timerSpan) timerSpan.textContent = '';
                resendBtn.innerHTML = '{{ __("Resend Code") }}';
            }
        }, 1000);

        updateTheme();
    </script>
</body>
</html>
