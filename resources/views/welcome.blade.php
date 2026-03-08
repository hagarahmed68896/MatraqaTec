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

<div :class="darkMode ? 'h-28 md:h-32' : 'h-20'" class="max-w-7xl mx-auto px-6 flex justify-between items-center flex-row-reverse transition-all duration-300">            <!-- Logo (Right in RTL) -->
            <a href="#" class="flex items-center">
                <img :src="darkMode ? '{{ asset('assets/images/163cf4a33948b9671c182f9d0aa410eaab570a58.png') }}' : '{{ asset('assets/images/41b68e035292ba7aa97a9bb8b16143cb90992358.png') }}'" 
                     :class="darkMode ? 'h-24 md:h-28' : 'h-10'" 
                     class="object-contain transition-all duration-500" alt="MatraqaTec">
            </a>
            
            <!-- Actions (Left in RTL) -->
            <div class="flex items-center gap-4">
                <!-- Theme Toggle -->
                <button @click="darkMode = !darkMode" class="w-10 h-10 flex items-center justify-center border border-slate-200 dark:border-slate-700 rounded-xl bg-gray-700 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-all hover:bg-slate-100 ">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <svg x-show="darkMode" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.727 12.727L12 12l8.485 8.485z"></path></svg>
                </button>

                <!-- Language Dropdown -->
         <div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="h-10 w-30 flex items-center justify-center border border-slate-200 dark:border-slate-700 rounded-xl bg-gray-700 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-all hover:bg-slate-100">
        
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
                <a href="#" class="hidden md:flex px-6 py-2.5 bg-[#1A1A31] border border-slate-200 dark:border-slate-700 dark:bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-md hover:bg-black transition-all active:scale-95">
                    {{ __('Download App') }}
                </a>
            </div>
        </div>
</nav>

    <!-- Hero Section -->
    <section class="hero-bg dark:bg-[#111122] transition-colors duration-300 relative pt-40
     md:pt-48 pt-20 pb-10 overflow-hidden flex items-center">
        <div class="max-w-7xl mx-auto px-6 relative z-10 flex flex-col lg:flex-row items-center justify-center gap-12 w-full">
           
            <!-- Hero Content (Right side) -->
            <div class="flex-1 space-y-8 pb-20 lg:pb-32 md:mt-4 mt-8">
                <div class="flex">
                    <div class="inline-flex gap-2 px-5  font-bold py-2 bg-white/10 backdrop-blur-md rounded-full text-xs text-black dark:text-[#1A1A31] shadow-sm">
                    <span class="text-rose-500 text-sm">🛠️</span>    
                    {{ __('The right technician at the right time.') }}
                    </div>
                </div>
                
                <h1 class="text-3xl lg:text-5xl  tracking-tight mb-2">
                    <span class="font-black font-bold text-[#1A1A31]">{{ __('Your Trusted Partner') }}</span>
                    <span class="text-[#4A4A5C] font-bold">{{ __('for Home Services') }}</span>
                </h1>
                
                <p class="text-xl text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
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
                <div class="relative w-[350px]  rounded-[1rem] border-[12px] border-[#1A1A31] dark:border-slate-800 overflow-hidden flex flex-col duration-500">
                    <!-- Notch / Dynamic Island -->
                    <div class="absolute top-0 inset-x-0 h-10 flex justify-center z-30">
                        <div class="mt-3 w-32 h-6 bg-[#1A1A31] dark:bg-slate-800 rounded-full"></div>
                    </div>
                    
                    <div class="flex-1 w-full px-1 pb-1 flex flex-col relative rounded-b-[2.5rem] overflow-hidden">
                        <img src="{{ asset('assets/images/Screenshot 2026-03-02 124457.png') }}" class="w-[110%] max-w-none transform ltr:-translate-x-4 rtl:translate-x-4 h-full object-cover object-bottom rounded-b-[2rem]" alt="App Preview">
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Features Section -->
    <style>
        @media (max-width: 1024px) {
            .feature-card-happy-users {
                left: -1rem !important;
                bottom: 5% !important;
                max-width: 90% !important;
            }
            .feature-card-services {
                right: -1rem !important;
                top: 15% !important;
                max-width: 95% !important;
            }
        }
    </style>
    <section class="dark:bg-[#1A1A31] transition-colors duration-300 py-10 relative z-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-24">
         <!-- Text Side -->
                <div class="flex-1 space-y-10 ">
                    <div class="space-y-6">
                        <span class="inline-block px-6 py-2 bg-slate-100 dark:bg-slate-800 text-[#1A1A31] dark:text-slate-400 font-bold text-sm tracking-wide rounded-full">{{ __('About Us') }}</span>
                        <h2 class="text-4xl lg:text-5xl font-black text-[#1A1A31] dark:text-white leading-[1.2]">
                            {{ __('Smart and Safe Home Services') }}
                        </h2>
                        <p class="text-xl text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                            {{ __('Find trusted technicians for your home tasks easily. We guarantee quality and speed in all repairs from plumbing to electricity.') }}
                        </p>
                    </div>

                    <div class="space-y-8 pt-4">
                        <div class="flex gap-6 items-start text-right group">
                            <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-[#1A1A31] dark:text-white mb-2">{{ __('Quick and Easy Booking') }}</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-base leading-relaxed">{{ __('Booking our services is easier than you expect, just choose the service and time.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-6 items-start text-right group">
                            <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-200">
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
           <div class="flex-1 relative w-full flex justify-center py-10 lg:pl-10">
    
               <!-- Main Wrapper for Graphic and Overlays -->
               <div class="relative w-[280px]">
                   
                   <!-- Phone Mockup Frame -->
                   <div class="relative w-full rounded-[3rem] border-[12px] border-[#1A1A31] dark:border-slate-800 overflow-hidden flex flex-col z-30 bg-white shadow-2xl h-[500px]">
             
                       <!-- Image -->
                       <img src="{{ asset('assets/images/iphone13_4_283x570.png') }}" 
                            class="w-[110%] max-w-none transform -translate-x-4 h-full object-cover object-bottom" 
                            alt="App Preview">
                   </div>

                   <!-- Bottom Left Card (Happy Users) -->
                   <div class="feature-card-happy-users absolute bg-white dark:bg-slate-800/95 px-5 py-4 rounded-3xl 
                   shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] items-center
                   border border-slate-100 dark:border-slate-700 z-50 transform 
                   hover:scale-105 transition-all duration-300"
                        style="bottom: 10%; right: auto; left: -45%; width: max-content;">
                       <div class="flex -space-x-3 rtl:space-x-reverse">
                           @foreach($sample_avatars->take(5) as $user)
                               <img src="{{ asset($user->avatar) }}" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-800 object-cover shadow-sm">
                           @endforeach
                       </div>
                       <div class="flex flex-col text-right mt-2">
                           <div class="flex items-center justify-end gap-1 mb-0.5">
                                                                                     <p class="text-[12px] font-bold text-slate-600 dark:text-slate-300 uppercase leading-none">{{ __('Happy Users') }}</p>   
                           <span class="text-xs text-yellow-400">★</span>
                               <span class="text-sm font-black text-[#1A1A31] dark:text-white">4.5</span>

                           </div>
                       </div>
                   </div>

                   <!-- Top Right Card (Services) -->
                   <div class="feature-card-services flex absolute bg-white dark:bg-slate-800/95 rounded-3xl p-5 shadow-[0_20px_50px_-10px_rgba(0,0,0,0.15)]
                    border border-slate-100 dark:border-slate-700 z-50 transform 
                    hover:-translate-y-2 hover:scale-105 transition-all duration-300 overflow-hidden"
                        style="top: 25%; left: auto; right: -40%; width: max-content;">
                       <div class="flex gap-4 w-full">
                           @foreach($services->take(4) as $service)
                               <div class="flex flex-col items-center justify-center gap-2">
                                   <div class="w-10 h-10 bg-slate-50 dark:bg-slate-700/50 rounded-2xl flex items-center justify-center shadow-sm border border-slate-100/50 dark:border-slate-600">
                                       @if($service->icon)
                                           <img src="{{ asset($service->icon) }}" class="w-6 h-6 object-contain">
                                       @else
                                           <span class="text-lg">🛠️</span>
                                       @endif
                                   </div>
                                   <span class="text-[10px] whitespace-nowrap font-bold text-[#1A1A31] dark:text-slate-300 text-center leading-tight">
                                       {{ app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en }}
                                   </span>
                               </div>
                           @endforeach
                       </div>
                   </div>

               </div>
           </div>

            </div>
        </div>
        
    </section>

    <!-- Testimonials Section -->
    <section class="bg-white dark:bg-[#1A1A31] transition-colors duration-300 py-12">
        <div class="max-w-7xl mx-auto px-6">

            <!-- Header -->
            <div class="text-center mb-16">
                <span class="inline-block px-5 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold text-sm rounded-full mb-6">{{ __('View Reviews') }}</span>
                <h2 class="text-3xl lg:text-4xl font-black text-[#1A1A31] dark:text-white mb-4">{{ __('What Our Customers Say') }}</h2>
                <p class="text-xl text-slate-500 dark:text-slate-400 leading-relaxed font-medium mb-8">
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

            <div x-data="{ 
                current: 0, 
                total: {{ count($reviewItems) }},
                perPage: 4,
                autoplayInterval: null,
                touchStartX: 0,
                touchEndX: 0,
                updatePerPage() {
                    if (window.innerWidth < 768) this.perPage = 1;
                    else if (window.innerWidth < 1024) this.perPage = 2;
                    else this.perPage = 4;
                    // Reset current if it's out of bounds after resize
                    if (this.current > this.total - this.perPage) {
                        this.current = Math.max(0, this.total - this.perPage);
                    }
                },
                next() {
                    if (this.current < this.total - this.perPage) {
                        this.current++;
                    } else {
                        this.current = 0;
                    }
                },
                prev() {
                    if (this.current > 0) {
                        this.current--;
                    } else {
                        this.current = Math.max(0, this.total - this.perPage);
                    }
                },
                goTo(index) {
                    this.current = Math.min(index, this.total - this.perPage);
                },
                handleTouchStart(e) {
                    this.touchStartX = e.changedTouches[0].screenX;
                },
                handleTouchEnd(e) {
                    this.touchEndX = e.changedTouches[0].screenX;
                    this.handleSwipe();
                },
                handleSwipe() {
                    const threshold = 50;
                    const diff = this.touchStartX - this.touchEndX;
                    const isRtl = document.documentElement.dir === 'rtl';
                    
                    if (Math.abs(diff) > threshold) {
                        if (diff > 0) {
                            // Swiped Left
                            isRtl ? this.prev() : this.next();
                        } else {
                            // Swiped Right
                            isRtl ? this.next() : this.prev();
                        }
                    }
                },
                startAutoplay() {
                    // Autoplay disabled per user request
                },
                stopAutoplay() {
                    // Autoplay disabled per user request
                }
            }" 
            x-init="updatePerPage(); window.addEventListener('resize', () => updatePerPage())"
            class="relative group">

                <!-- Navigation Arrow (Left) -->
                <button x-show="total > perPage" 
                        @click="prev()"
                        class="absolute left-2 md:-left-4 lg:-left-6 top-1/2 -translate-y-1/2 w-10 h-10 lg:w-14 lg:h-14 bg-white text-[#1A1A31] border border-slate-100 dark:border-slate-700 rounded-full shadow-xl flex items-center justify-center transition-all z-20 hover:bg-[#1A1A31] hover:text-[#1A1A31] hover:scale-110 active:scale-95"
                        style="left: 0.5rem; right: auto;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <!-- Navigation Arrow (Right) -->
                <button x-show="total > perPage"
                        @click="next()"
                        class="absolute right-2 md:-right-4 lg:-right-6 top-1/2 -translate-y-1/2 w-10 h-10 lg:w-14 lg:h-14 bg-white text-[#1A1A31] border border-slate-100 dark:border-slate-700 rounded-full shadow-xl flex items-center justify-center transition-all z-20 hover:bg-[#1A1A31] hover:text-[#1A1A31] hover:scale-110 active:scale-95"
                        style="right: 0.5rem; left: auto;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div class="overflow-hidden px-1 pb-12"
                     @touchstart="handleTouchStart($event)"
                     @touchend="handleTouchEnd($event)">
                    <div class="flex transition-transform duration-700 ease-in-out"
                         :style="`gap: 2rem; transform: translateX(calc(${current * (document.documentElement.dir === 'rtl' ? 1 : -1)} * (100% + 2rem) / ${perPage}))`"
                    >
                        @foreach($reviewItems as $review)
                            <div class="flex-shrink-0 mx-2 bg-white dark:bg-slate-800/40 rounded-[2.5rem] p-8 lg:p-10 border border-slate-50 dark:border-slate-700/50 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500"
                                 :style="'width: calc((100% - (' + (perPage - 1) + ' * 2rem)) / ' + perPage + ')'">
                                
                                <!-- Top Info: Avatar + Name (RTL) -->
                                <div class="flex items-center gap-5 mb-6 flex-row-reverse text-right">
                                    <!-- Avatar -->
                                    <div class="w-14 h-14 rounded-full bg-[#1A1A31] dark:bg-slate-700 text-white dark:text-slate-400 flex items-center justify-center font-black text-2xl flex-shrink-0 overflow-hidden shadow-lg border-2 border-white dark:border-slate-800">
                                        @if($review->user && $review->user->avatar)
                                            <img src="{{ asset($review->user->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center font-bold text-indigo-600 bg-indigo-50">
                                                {{ mb_substr($review->user->name ?? 'U', 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <!-- Name + Stars -->
                                    <div class="flex-1 text-primary">
                                        <h4 class="font-bold text-lg leading-tight mb-2" style="color: #1A1A31 !important;">{{ $review->user->name ?? __('User') }}</h4>
                                        <div class="flex gap-0.5 justify-end">
                                            @for($i = 0; $i < 5; $i++)
                                                <span class="text-2xl font-bold" style="color: {{ $i < (int)($review->rating ?? $review->stars ?? 5) ? '#FFD700' : '#E2E8F0' }}; line-height: 1;">★</span>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <!-- Comment -->
                                <p class="text-slate-600 text-sm leading-[1.8] text-right" style="color: #1A1A31 !important;">
                                    "{{ $review->comment }}"
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination Dots -->
                <div class="flex justify-center gap-2 mt-4">
                    <template x-for="i in (total > perPage ? total - perPage + 1 : 1)" :key="i">
                        <button @click="goTo(i-1)" 
                                :class="current === (i-1) ? 'w-8 bg-indigo-600' : 'w-2 bg-slate-300 dark:bg-slate-700 hover:bg-slate-400'"
                                class="h-2 rounded-full transition-all duration-300"></button>
                    </template>
                </div>
            </div>

        </div>
    </section>

 <section class="bg-white mb-8 dark:bg-[#1A1A31] transition-colors duration-300 py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="hero-bg dark:bg-[#111122] rounded-[3rem] relative border-4 border-white dark:border-slate-800 shadow-2xl">

           
            {{-- ═══════════════════════════════════════ --}}
            {{-- MOBILE LAYOUT: Modern Tilted Design     --}}
            {{-- ═══════════════════════════════════════ --}}
            <div class="flex lg:hidden  flex-col items-center py-8  overflow-hidden rounded-[3rem] relative">
                
                {{-- Decorative Glow Background --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-indigo-500/20 blur-[100px] rounded-full pointer-events-none"></div>

                {{-- Text Content --}}
                <div class="px-6 text-center space-y-6 z-50 relative">
                    <h2 class="text-3xl font-black text-[#1A1A31] dark:text-white leading-tight">
                        {{ __('One App to Book Fast and Reliable Home Services') }}
                    </h2>
                    <p class="text-base text-[#4A4A5C] dark:text-slate-400 leading-relaxed max-w-xs mx-auto font-medium">
                        {{ __('Find trusted technicians for cleaning, plumbing, electricity, and general repairs.') }}
                    </p>
                    <div class="flex gap-4 justify-center pt-2">
                        <a href="#" class="shadow-xl hover:scale-105 transition-transform active:scale-95">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="App Store" class="h-10">
                        </a>
                        <a href="#" class="shadow-xl hover:scale-105 transition-transform active:scale-95">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" class="h-10">
                        </a>
                    </div>
                </div>

                <!-- {{-- Modern Tilted Graphics Section --}}
                <div class="relative w-full h-[380px] mt-16 flex justify-center">
                    
                    {{-- Secondary Phone (Back/Tilted) --}}
                    <div class="absolute left-[5%] bottom-0 w-48 z-10 transform -rotate-[15deg] translate-y-20 opacity-60 blur-[1px]">
                        <div class="h-80 rounded-[2.5rem] border-[10px] border-[#1A1A31] dark:border-slate-800 overflow-hidden shadow-2xl bg-[#C8C9E0]">
                            <img src="{{ asset('assets/images/iphone13 (2).png') }}"
                                 class="w-full h-full object-cover object-top" alt="App Preview Back">
                        </div>
                    </div>

                    {{-- Main Phone (Front/Straight) --}}
                    <div class="absolute left-1/2 -translate-x-1/2 bottom-0 w-52 z-20 transform translate-y-4">
                        <div class="h-80 rounded-[3rem] border-[12px] border-[#1A1A31] dark:border-slate-800 overflow-hidden shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] bg-white">
                            <img src="{{ asset('assets/images/iphone13 (1).png') }}"
                                 class="w-full h-full object-cover object-top" alt="App Preview Front">
                        </div>
                    </div>

                    {{-- Subtle Right Element (Optional Glow/Accent) --}}
                    <div class="absolute right-[5%] bottom-1/2 translate-y-1/2 w-32 h-32 bg-indigo-400/10 rounded-full blur-3xl"></div>
                </div> -->

            </div>

            {{-- ═══════════════════════════════════════════════════════ --}}
            {{-- DESKTOP LAYOUT: text left + staggered phones right     --}}
            {{-- ═══════════════════════════════════════════════════════ --}}
            <div class="hidden overflow-hidden lg:flex flex-row items-center justify-between px-8 min-h-[700px]"
            style="height: 400px;">

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
                <div class="flex-1 rtl:mr-4 justify-end" style="position: relative; ">
                    {{-- Phone 1: rises above the card (uses -translate-y so it pops above) --}}
                    <div style="position: absolute; left: 0;  width: 270px; z-index: 10;">
                        <div class="overflow-hidden h-full rounded-[2.5rem]
                         border-8 border-[#1A1A31] shadow-2xl bg-[#C8C9E0]">
                            <img src="{{ asset('assets/images/iphone13 (2).png') }}"
                                 class="w-full h-full object-cover object-top" alt="App Preview Top">
                        </div>
                    </div>
                    {{-- Phone 2: sits at bottom edge --}}
                    <div style="position: absolute; bottom: 0;
                     right: 0; width: 270px; height: full; z-index: 20;">
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
        <div class="max-w-7xl mx-auto px-6">
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
            
            <div class="flex md:flex-row flex-col mt-4 justify-between items-center border-t border-white/10 
             md:pt-12 pt-4 gap-8 text-slate-400 font-bold text-sm tracking-wide">
                {{-- Copyright - now on the right --}}
                <div class="uppercase tracking-widest text-right">&copy; 2026 {{ __('MatraqaTec') }} - {{ __('All rights reserved.') }}</div>
                
                {{-- Social Icons - now on the left --}}
                <div class="flex items-center gap-4 mt-4">
                    @foreach($social_links as $link)
                        @php
                            $icon = strtolower($link->icon);
                            $url = $link->url;
                        @endphp
                        
                        <a href="{{ $url }}" target="_blank" class="w-10 h-10 rounded-full bg-white flex items-center justify-center hover:scale-110 transition-transform shadow-lg" title="{{ $link->name }}">
                            @if(str_contains($icon, 'instagram'))
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" fill="url(#ig-gradient-{{ $link->id }})"/>
                                    <defs>
                                        <linearGradient id="ig-gradient-{{ $link->id }}" x1="12" y1="0" x2="12" y2="24" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#405DE6"/><stop offset="0.25" stop-color="#5851DB"/><stop offset="0.5" stop-color="#833AB4"/><stop offset="0.75" stop-color="#E1306C"/><stop offset="1" stop-color="#F77737"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            @elseif(str_contains($icon, 'twitter') || str_contains($icon, 'x'))
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="black"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            @elseif(str_contains($icon, 'linkedin'))
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#0077B5"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            @elseif(str_contains($icon, 'tiktok'))
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.673a2.893 2.893 0 0 1-5.201 1.743l-.002-.001.002.001a2.893 2.893 0 0 1 3.183-4.51v-3.5a6.329 6.329 0 0 0-3.932 1.321 6.393 6.393 0 0 0-2.688 5.174c0 3.533 2.864 6.397 6.398 6.397 3.535 0 6.391-2.857 6.398-6.39v-6.182a8.182 8.182 0 0 0 4.745 1.509v-3.448a4.83 4.83 0 0 1-1.687-.301z" fill="black"/>
                                </svg>
                            @elseif(str_contains($icon, 'facebook'))
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            @else
                                <span class="text-xs uppercase font-bold">{{ substr($link->name, 0, 2) }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
