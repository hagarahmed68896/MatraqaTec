<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('MatraqaTec - Your Trusted Partner for Home Services') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/dubai-font" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Dubai', 'Tajawal', sans-serif; overflow-x: hidden; }
        .hero-bg { background-color: #C8C9E0; }
    </style>
</head>
<body class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white antialiased transition-colors duration-300">

    <!-- Navbar -->
<nav x-data="{ scrolled: false }" 
     @scroll.window="scrolled = (window.pageYOffset > 50)"
     :class=" scrolled ? 'bg-white dark:bg-[#1A1A31] shadow-lg' : 'bg-white dark:bg-[#1A1A31] border-b border-slate-100 dark:border-slate-800 shadow-sm'"
     class="w-full z-50 transition-all duration-500 ">

<div class="max-w-6xl mx-auto px-6 h-20 flex justify-between items-center flex-row-reverse">            <!-- Logo (Right in RTL) -->
            <a href="#" class="flex items-center">
                <img src="{{ asset('assets/images/41b68e035292ba7aa97a9bb8b16143cb90992358.png') }}" class="h-10 object-contain" alt="MatraqaTec">
            </a>
            
            <!-- Actions (Left in RTL) -->
            <div class="flex items-center gap-4">
                <!-- Theme Toggle -->
                <button @click="darkMode = !darkMode" class="w-10 h-10 flex items-center justify-center border border-slate-200 dark:border-slate-700 rounded-xl bg-gray-700 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-all hover:bg-slate-100 dark:hover:bg-slate-700">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <svg x-show="darkMode" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.727 12.727L12 12l8.485 8.485z"></path></svg>
                </button>

                <!-- Language Dropdown -->
         <div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="h-10 w-30 flex items-center justify-center border border-slate-200 dark:border-slate-700 rounded-xl bg-gray-700 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-all hover:bg-slate-100 dark:hover:bg-slate-700">
        
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-globe mx-2" viewBox="0 0 16 16">
            <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7.03 7.03 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5zm6.853 3.472A7 7 0 0 0 13.745 12H11.91a9.3 9.3 0 0 1-.64 1.539 7 7 0 0 1-.597.933M8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z"/>
        </svg>

        <span class="text-sm font-bold mx-2">
            @if(app()->getLocale() == 'ar')
                اللغة العربية
            @else
                English
            @endif
        </span>
    </button>

    <div x-show="open" @click.outside="open=false"
         class="absolute mt-2 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 p-1 z-50 overflow-hidden">
        <a href="{{ route('admin.switch-language', 'ar') }}" class="block px-4 py-2 text-sm font-medium text-right hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">اللغة العربية</a>
        <a href="{{ route('admin.switch-language', 'en') }}" class="block px-4 py-2 text-sm font-medium text-right hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">English</a>
    </div>
</div>

                <!-- Download App (Hidden on Mobile) -->
                <a href="#" class="hidden md:flex px-6 py-2.5 bg-[#1A1A31] dark:bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-md hover:bg-black transition-all active:scale-95">
                    {{ __('Download App') }}
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg dark:bg-[#111122] transition-colors duration-300 relative pt-40 lg:pt-48 pb-10 overflow-hidden flex items-center">
        <div class="max-w-6xl mx-auto px-6 relative z-10 flex flex-col lg:flex-row items-center justify-center gap-12 w-full">
           
            <!-- Hero Content (Right side) -->
            <div class="flex-1 space-y-8 pb-20 lg:pb-32">
                <div class="flex">
                    <div class="inline-flex gap-2 px-5 font-bold py-2 bg-white/10 backdrop-blur-md rounded-full text-xs text-black dark:text-white shadow-sm">
                    <span class="text-rose-500 text-sm">🛠️</span>    
                    {{ __('The right technician at the right time.') }}
                    </div>
                </div>
                
                <h1 class="text-3xl lg:text-5xl  tracking-tight mb-2">
                    <span class="font-black font-bold text-[#1A1A31]">{{ __('Your Trusted Partner') }}</span>
                    <span class="text-[#4A4A5C] font-bold">{{ __('for Home Services') }}</span>
                </h1>
                
                <p class="text-[24px]  text-[#4A4A5C] dark:text-white tracking-tight mb-2">
                    {{ __('Find trusted professionals to meet all your repair, maintenance, and home improvement needs easily and safely.') }}
                </p>
                
                <div class="pt-6 flex">
                    <button class="px-8 py-4 bg-[#1A1A31] dark:bg-indigo-600 text-white rounded-2xl font-bold hover:bg-black dark:hover:bg-indigo-700 transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                        {{ __('Download App Now') }}
                    </button>
                </div>
            </div>
             
            <!-- Hero Graphics (Phone Mockup - Left side) -->
            <div class="flex-1 relative w-full flex justify-center lg:justify-end">
                <div class="relative w-[280px]  rounded-[1rem] border-[12px] border-[#1A1A31] dark:border-slate-800 overflow-hidden flex flex-col duration-500">
                    <!-- Notch / Dynamic Island -->
                    <div class="absolute top-0 inset-x-0 h-10 flex justify-center z-30">
                        <div class="mt-3 w-24 h-6 bg-[#1A1A31] dark:bg-slate-800 rounded-full"></div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 w-full px-1 pb-1 flex flex-col relative rounded-b-[2.5rem] overflow-hidden">
                        <img src="{{ asset('assets/images/Screenshot 2026-03-02 124457.png') }}" class="w-full h-full object-cover object-bottom rounded-b-[2rem]" alt="App Preview">
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Features Section -->
    <section class="dark:bg-[#1A1A31] transition-colors duration-300 py-12 relative z-20">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-24">
         <!-- Text Side -->
                <div class="flex-1 space-y-10 ">
                    <div class="space-y-6">
                        <span class="inline-block px-6 py-2 bg-slate-100 dark:bg-slate-800 text-[#1A1A31]  font-bold text-sm tracking-wide rounded-full">{{ __('About Us') }}</span>
                        <h2 class="text-4xl lg:text-5xl font-black text-[#1A1A31] dark:text-white leading-[1.2]">
                            {{ __('Smart and Safe Home Services') }}
                        </h2>
                        <p class="text-xl text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                            {{ __('Find trusted technicians for your home tasks easily. We guarantee quality and speed in all repairs from plumbing to electricity.') }}
                        </p>
                    </div>

                    <div class="space-y-8 pt-4">
                        <div class="flex gap-6 items-start text-right group">
                            <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-[#1A1A31] dark:text-white mb-2">{{ __('Quick and Easy Booking') }}</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-base leading-relaxed">{{ __('Booking our services is easier than you expect, just choose the service and time.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-6 items-start text-right group">
                            <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-[#1A1A31] dark:text-white mb-2">{{ __('Available 24/7') }}</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-base leading-relaxed">{{ __('Our platform is always available, connecting you with available specialists when needed.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>           
    <!-- Graphic Side -->
<div class="flex-1 w-full flex justify-center items-center relative">
    
    <div class="relative w-[260px] md:w-[280px] z-30 group">
     <div class="w-full rounded-[1.8rem] relative">
    <img src="{{ asset('assets/images/iphone13.png') }}" class="w-30 h-30"
         alt="App Preview">
</div>
        
        <div class="absolute top-8 left-1  w-24 h-5 bg-slate-900 rounded-b-xl z-20"></div>
    </div>

    <div class="hidden lg:block absolute -right-8 top-1/4 w-[200px] bg-white/90 dark:bg-slate-800/95 backdrop-blur-md rounded-2xl p-4 shadow-2xl border border-slate-100 dark:border-slate-700 z-30 transform hover:-translate-y-2 transition-all duration-300">
        <p class="text-[12px] font-bold mb-3 text-slate-400 uppercase tracking-wider">{{ __('Our Services') }}</p>
        <div class="grid grid-cols-3 gap-3">
            @foreach($services->take(6) as $service)
                <div class="flex flex-col items-center gap-1">
                    <div class="w-10 h-10 bg-emerald-50 dark:bg-slate-700 rounded-xl flex items-center justify-center shadow-sm">
                        @if($service->icon)
                            <img src="{{ asset($service->icon) }}" class="w-6 h-6 object-contain">
                        @else
                            <span class="text-xl">🛠️</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="absolute -bottom-6 -left-4 bg-white/90 dark:bg-slate-800/95 backdrop-blur-md px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-100 dark:border-slate-700 z-30 transform hover:scale-105 transition-all duration-300">
        <div class="flex -space-x-3">
            @foreach($sample_avatars->take(3) as $user)
                <img src="{{ asset($user->avatar) }}" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-800 object-cover shadow-md">
            @endforeach
        </div>
        <div>
            <div class="flex items-center gap-1">
                <span class="text-sm font-black text-[#1A1A31] dark:text-white">+{{ $happy_customers_count }}</span>
                <span class="text-xs text-orange-400">★</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase leading-none">{{ __('Happy Users') }}</p>
        </div>
    </div>
</div>  
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="bg-white dark:bg-[#111122] transition-colors duration-300 py-20 border-t border-slate-100 dark:border-slate-800">
        <div class="max-w-6xl mx-auto px-6">

            <!-- Header -->
            <div class="text-center mb-16">
                <span class="inline-block px-5 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold text-sm rounded-full mb-6">{{ __('View Reviews') }}</span>
                <h2 class="text-3xl lg:text-4xl font-black text-[#1A1A31] dark:text-white mb-4">{{ __('What Our Customers Say') }}</h2>
                <p class="text-[24px] mb-8 text-[#4A4A5C]  max-w-xl mx-auto leading-relaxed">
                    {{ __('Our platform connects you with thousands of trusted technicians, providing seamless access to all home services through our application.') }}
                </p>
            </div>

            <!-- Carousel -->
            @php
                $reviewItems = $reviews->isNotEmpty() ? $reviews : collect([
                    (object)['user' => (object)['name' => __('Ahmed Khaled'), 'avatar' => null], 'rating' => 4, 'comment' => __('Excellent app, saved me time and effort in searching for trusted technicians.')],
                    (object)['user' => (object)['name' => __('Ahmed Ali'), 'avatar' => null], 'rating' => 4, 'comment' => __('Best experience ever in ordering home maintenance services! Clear and suitable prices.')],
                    (object)['user' => (object)['name' => __('Sami Mansour'), 'avatar' => null], 'rating' => 5, 'comment' => __('Best service I have found so far, the service is fast and the technician was professional.')],
                    (object)['user' => (object)['name' => __('Ahmed Mossad'), 'avatar' => null], 'rating' => 4, 'comment' => __('High-quality service and full commitment to appointments. I highly recommend it.')],
                ]);
            @endphp

            <div x-data="{ current: 0, total: {{ count($reviewItems) }}, perPage: 4 }" class="relative">

                <!-- Navigation Arrow (Left) -->
                <button @click="current = current > 0 ? current - 1 : 0"
                        class="absolute -left-5 top-1/2 -translate-y-1/2 w-10 h-10 bg-white dark:bg-slate-800 rounded-full shadow-lg border border-slate-100 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-[#1A1A31] dark:hover:text-white transition-all z-10 hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <!-- Cards Container -->
                <div class="overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($reviewItems as $review)
                            <div class="bg-white dark:bg-slate-800/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                                <!-- Top: Avatar + Name + Stars -->
                                <div class="flex items-center gap-4 mb-4 flex-row-reverse text-right">
                                    <!-- Avatar -->
                                    <div class="w-12 h-12 rounded-full bg-[#1A1A31] dark:bg-slate-700 text-white flex items-center justify-center font-black text-lg flex-shrink-0 overflow-hidden shadow-md">
                                        @if($review->user && $review->user->avatar)
                                            <img src="{{ asset($review->user->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ mb_substr($review->user->name ?? 'U', 0, 1) }}
                                        @endif
                                    </div>
                                    <!-- Name + Stars -->
                                    <div class="flex-1">
                                        <h4 class="font-bold text-[#1A1A31] dark:text-white text-sm leading-tight mb-1">{{ $review->user->name ?? __('User') }}</h4>
                                        <div class="flex gap-0.5">
                                            @for($i = 0; $i < 5; $i++)
                                                <span class="text-sm text-[#FFB300] {{ $i < $review->rating ? 'text-yellow-400' : 'text-yellow-400 dark:text-slate-600' }}">★</span>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <!-- Comment -->
                                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed text-right italic line-clamp-4">"{{ $review->comment }}"</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </section>

 <section class="bg-white mb-8 dark:bg-[#1A1A31] transition-colors duration-300 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <div class="hero-bg dark:bg-[#111122] rounded-[3rem] relative border-4 border-white dark:border-slate-800 shadow-2xl">

            {{-- ═══════════════════════════════════════ --}}
            {{-- MOBILE LAYOUT: phone | text | phone     --}}
            {{-- ═══════════════════════════════════════ --}}
            <div class="flex lg:hidden items-stretch min-h-[300px] overflow-hidden rounded-[3rem]">

                {{-- Left phone --}}
                <div class="w-[30%] self-end">
                    <div class="h-56 rounded-tr-[2rem] border-4 border-b-0 border-r-0 border-[#1A1A31] overflow-hidden shadow-xl bg-[#C8C9E0] -mb-1">
                        <img src="{{ asset('assets/images/iphone13 (2).png') }}"
                             class="w-full h-full object-cover object-top" alt="App Preview 1">
                    </div>
                </div>

                {{-- Center text --}}
                <div class="flex-1 px-3 py-8 flex flex-col items-center justify-center text-center gap-4 z-10">
                    <h2 class="text-lg font-black text-[#1A1A31] dark:text-white leading-snug">
                        {{ __('One App to Book Fast and Reliable Home Services') }}
                    </h2>
                    <p class="text-xs text-[#4A4A5C] dark:text-slate-400 leading-relaxed">
                        {{ __('Find trusted technicians for cleaning, plumbing, electricity, and general repairs.') }}
                    </p>
                    <div class="flex flex-col gap-2 items-center">
                        <a href="#" class="opacity-80 hover:opacity-100 transition-opacity">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="App Store" class="h-8">
                        </a>
                        <a href="#" class="opacity-80 hover:opacity-100 transition-opacity">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" class="h-8">
                        </a>
                    </div>
                </div>

                {{-- Right phone --}}
                <div class="w-[30%] self-start">
                    <div class="h-56 rounded-bl-[2rem] border-4 border-t-0 border-l-0 border-[#1A1A31] overflow-hidden shadow-xl bg-[#C8C9E0] -mt-1">
                        <img src="{{ asset('assets/images/iphone13 (1).png') }}"
                             class="w-full h-full object-cover object-top" alt="App Preview 2">
                    </div>
                </div>

            </div>

            {{-- ═══════════════════════════════════════════════════════ --}}
            {{-- DESKTOP LAYOUT: text left + staggered phones right     --}}
            {{-- ═══════════════════════════════════════════════════════ --}}
            <div class="hidden overflow-hidden lg:flex flex-row items-center justify-between gap-12 px-8 min-h-[450px]">

                {{-- Text side --}}
                <div class="flex-1 text-right relative z-10 space-y-8 py-12">
                    <h2 class="text-3xl lg:text-4xl font-black text-[#1A1A31] dark:text-white leading-[1.3]">
                        {{ __('One App to Book Fast and Reliable Home Services') }}
                    </h2>
                    <p class="text-xl text-[#4A4A5C] dark:text-slate-400 leading-relaxed">
                        {{ __('Find trusted technicians for cleaning, plumbing, electricity, and general repairs.') }}
                    </p>
                    <div class="flex gap-6">
                        <a href="#" class="h-10 opacity-70 hover:opacity-100 transition-opacity">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="App Store" class="h-10">
                        </a>
                        <a href="#" class="h-10 opacity-70 hover:opacity-100 transition-opacity">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" class="h-10">
                        </a>
                    </div>
                </div>

                {{-- Staggered phones --}}
                <div class="flex-1 relative h-72">
                    {{-- Phone 1: rises above the card (uses -translate-y so it pops above) --}}
                    <div class="absolute bottom-4 left-0 w-56 h-96 z-10 -translate-y-16">
                        <div class="overflow-hidden h-full rounded-[2.5rem] border-8 border-[#1A1A31] shadow-2xl bg-[#C8C9E0]">
                            <img src="{{ asset('assets/images/iphone13 (2).png') }}"
                                 class="w-full h-full object-cover object-top" alt="App Preview Top">
                        </div>
                    </div>
                    {{-- Phone 2: sits at bottom edge --}}
                    <div class="absolute bottom-0 right-0 w-56 h-80 z-20">
                        <div class="overflow-hidden h-full rounded-[2.5rem] border-8 border-[#1A1A31] shadow-2xl bg-[#C8C9E0]">
                            <img src="{{ asset('assets/images/iphone13 (1).png') }}"
                                 class="w-full h-full object-cover object-top" alt="App Preview Bottom">
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

    <!-- Footer -->
    <footer class="bg-[#1A1A31] dark:bg-[#0B0B1A] pt-20 pb-12 text-white transition-colors duration-300 border-t-4 border-white/10 dark:border-white/5">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col items-center text-center space-y-10 mb-20">
                <img src="{{ asset('assets/images/9802a3c60b5a7d4a948f48f2ddfe26cb7d01812f (1).png') }}" class="h-24 mb-4 mt-8 invert" alt="MatraqaTec">
                <p class="text-slate-300 text-lg mb-4 font-medium max-w-2xl mx-auto leading-relaxed italic opacity-80">
                    "{{ __('MatraqaTec is a leading service platform in the region that connects you with the best specialized technicians.') }}"
                </p>
                <!-- Quick App Downloads in Footer -->
                <div class="flex gap-6 mb-6">
                    <a href="#" class="h-10 opacity-70 hover:opacity-100 transition-opacity">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="App Store" class="h-10">
                    </a>
                    <a href="#" class="h-10 opacity-70 hover:opacity-100 transition-opacity">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" class="h-10">
                    </a>
                </div>
            </div>
            
            <div class="flex flex-row mt-4 justify-between items-center border-t border-white/10 
             pt-12 gap-8 text-slate-400 font-bold text-sm tracking-wide">
                {{-- Copyright - now on the right --}}
                <div class="uppercase tracking-widest text-right">&copy; 2026 {{ __('MatraqaTec') }} - {{ __('All rights reserved.') }}</div>
                
                {{-- Social Icons - now on the left --}}
                <div class="flex items-center gap-4 mt-4">
                    {{-- Instagram --}}
                    <a href="#" class="w-10 h-10 rounded-full bg-white flex items-center justify-center hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" fill="url(#ig-gradient)"/>
                            <defs>
                                <linearGradient id="ig-gradient" x1="12" y1="0" x2="12" y2="24" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#405DE6"/><stop offset="0.25" stop-color="#5851DB"/><stop offset="0.5" stop-color="#833AB4"/><stop offset="0.75" stop-color="#E1306C"/><stop offset="1" stop-color="#F77737"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </a>
                    {{-- X (Twitter) --}}
                    <a href="#" class="w-10 h-10 rounded-full bg-white flex items-center justify-center hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="black"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    {{-- LinkedIn --}}
                    <a href="#" class="w-10 h-10 rounded-full bg-white flex items-center justify-center hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#0077B5"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    {{-- TikTok --}}
                    <a href="#" class="w-10 h-10 rounded-full bg-white flex items-center justify-center hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.673a2.893 2.893 0 0 1-5.201 1.743l-.002-.001.002.001a2.893 2.893 0 0 1 3.183-4.51v-3.5a6.329 6.329 0 0 0-3.932 1.321 6.393 6.393 0 0 0-2.688 5.174c0 3.533 2.864 6.397 6.398 6.397 3.535 0 6.391-2.857 6.398-6.39v-6.182a8.182 8.182 0 0 0 4.745 1.509v-3.448a4.83 4.83 0 0 1-1.687-.301z" fill="black"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
