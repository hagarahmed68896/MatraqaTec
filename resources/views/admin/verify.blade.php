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
            width: 3rem;
            height: 3.5rem;
            flex-shrink: 0;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 900;
            border-radius: 0.75rem;
            background-color: #fff;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);
            transition: all .2s;
            outline: none;
            color: #1e293b;
        }
        .otp-input:focus {
            border-color: #1A1A31;
            box-shadow: 0 10px 15px -3px rgba(79,70,229,.2), 0 4px 6px -2px rgba(79,70,229,.1);
            transform: scale(1.05);
        }
        .otp-input:not(:placeholder-shown) {
            border-color: #1A1A31;
            background-color: rgba(26,26,49,.05);
        }
        .dark .otp-input {
            background-color: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }
        .dark .otp-input:focus {
            border-color: #6366f1 !important;
        }
        .dark .otp-input:not(:placeholder-shown) {
            background-color: rgba(99,102,241,.15) !important;
            border-color: #6366f1 !important;
        }
        @media (min-width: 768px) {
            .otp-input {
                width: 3.75rem;
                height: 4.5rem;
                font-size: 1.5rem;
                border-radius: 1rem;
            }
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
                        <div class="w-40 h-40  rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-primary">
<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-patch-check" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M10.354 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
  <path d="m10.273 2.513-.921-.944.715-.698.622.637.89-.011a2.89 2.89 0 0 1 2.924 2.924l-.01.89.636.622a2.89 2.89 0 0 1 0 4.134l-.637.622.011.89a2.89 2.89 0 0 1-2.924 2.924l-.89-.01-.622.636a2.89 2.89 0 0 1-4.134 0l-.622-.637-.89.011a2.89 2.89 0 0 1-2.924-2.924l.01-.89-.636-.622a2.89 2.89 0 0 1 0-4.134l.637-.622-.011-.89a2.89 2.89 0 0 1 2.924-2.924l.89.01.622-.636a2.89 2.89 0 0 1 4.134 0l-.715.698a1.89 1.89 0 0 0-2.704 0l-.92.944-1.32-.016a1.89 1.89 0 0 0-1.911 1.912l.016 1.318-.944.921a1.89 1.89 0 0 0 0 2.704l.944.92-.016 1.32a1.89 1.89 0 0 0 1.912 1.911l1.318-.016.921.944a1.89 1.89 0 0 0 2.704 0l.92-.944 1.32.016a1.89 1.89 0 0 0 1.911-1.912l-.016-1.318.944-.921a1.89 1.89 0 0 0 0-2.704l-.944-.92.016-1.32a1.89 1.89 0 0 0-1.912-1.911z"/>
</svg>                        </div>
                        <h1 class="text-4xl font-black text-slate-900 dark:text-white mb-3 tracking-tight">{{ __('Verification') }}</h1>
                        <p class="text-slate-500 dark:text-white text-base font-medium leading-relaxed px-4">
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
                                <button type="button" id="resendBtn" disabled class="text-slate-400 dark:text-white/50 bg-slate-100 dark:bg-white/5 px-8 py-3 rounded-2xl text-sm font-bold cursor-not-allowed transition-all">
                                    {{ __('Resend Code') }} <span id="timer" class="text-primary dark:text-white font-black ml-1">60s</span>
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
