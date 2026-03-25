<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('MatraqaTec Admin'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.05);
        }
        [x-cloak] { display: none !important; }
        .dark button:active, .dark a:active { color: white !important; }
    </style>
    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-900 dark:bg-primary-dark dark:text-slate-100 transition-colors duration-500 overflow-x-hidden">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden relative">
        
        <!-- Sidebar Overlay (Mobile) -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden"></div>

        <!-- Premium Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '{{ app()->getLocale() == 'ar' ? 'translate-x-full' : '-translate-x-full' }}'"
               class="fixed inset-y-0 {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} w-80 bg-white dark:bg-[#1A1A31] text-slate-600 dark:text-slate-300 flex-shrink-0 flex flex-col transition-all duration-300 shadow-xl z-50 border-{{ app()->getLocale() == 'ar' ? 'l' : 'r' }} border-slate-100 dark:border-white/5 lg:static lg:translate-x-0">
            <div class="p-8 flex items-center justify-center border-b border-slate-100 dark:border-white/5">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-12 brightness-0 dark:invert opacity-90">
            </div>
            
            <nav class="mt-6 flex-1 px-4 space-y-1 overflow-y-auto custom-scrollbar">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all group {{ request()->routeIs('admin.dashboard') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>{{ __('Admin Dashboard') }}</span>
                </a>

                <!-- Account Management Dropdown -->
                @if(auth()->user()->hasPermission('view supervisors') || auth()->user()->hasPermission('view individual customers') || auth()->user()->hasPermission('view corporate customers') || auth()->user()->hasPermission('view maintenance companies') || auth()->user()->hasPermission('view technicians') || auth()->user()->hasPermission('view roles and permissions'))
                <div x-data="{ open: {{ request()->is('admin/supervisors*') || request()->is('admin/customers*') || request()->is('admin/individual-customers*') || request()->is('admin/corporate-customers*') || request()->is('admin/maintenance-companies*') || request()->is('admin/technicians*') || request()->is('admin/technician-requests*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white {{ request()->is('admin/supervisors*') || request()->is('admin/customers*') || request()->is('admin/individual-customers*') || request()->is('admin/corporate-customers*') || request()->is('admin/maintenance-companies*') || request()->is('admin/technicians*') || request()->is('admin/technician-requests*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'text-primary dark:text-white font-black' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="font-bold">{{ __('Account Management') }}</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="space-y-1 {{ app()->getLocale() == 'ar' ? 'pr-12' : 'pl-12' }}">
                        @can('view supervisors')
                        <a href="{{ route('admin.supervisors.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.supervisors.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Supervisors') }}</a>
                        @endcan
                        @can('view individual customers')
                        <a href="{{ route('admin.individual-customers.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.individual-customers.*') || request()->routeIs('admin.corporate-customers.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Customers') }}</a>
                        @endcan
                        @can('view maintenance companies')
                        <a href="{{ route('admin.maintenance-companies.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.maintenance-companies.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Maintenance Companies') }}</a>
                        @endcan
                        @can('view technicians')
                        <a href="{{ route('admin.technician-requests.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.technician-requests.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Technician Requests') }}</a>
                        <a href="{{ route('admin.technicians.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.technicians.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Technicians') }}</a>
                        @endcan
                        @can('view roles and permissions')
                        <a href="{{ route('admin.roles.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Permissions') }}</a>
                        @endcan
                    </div>
                </div>
                @endif

                <!-- Blocked Users Dropdown -->
                @if(auth()->user()->hasPermission('view supervisors') || auth()->user()->hasPermission('view individual customers') || auth()->user()->hasPermission('view maintenance companies') || auth()->user()->hasPermission('view technicians'))
                <div x-data="{ open: {{ request()->is('admin/blocked*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white {{ request()->is('admin/blocked*') ? 'text-primary dark:text-white font-black' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                            <span class="font-bold">{{ __('Blocked Users') }}</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="space-y-1 {{ app()->getLocale() == 'ar' ? 'pr-12' : 'pl-12' }}">
                        @can('view individual customers')
                        <a href="{{ route('admin.blocked.customers') }}" class="block py-2 text-sm {{ request()->routeIs('admin.blocked.customers') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Customers') }}</a>
                        @endcan
                        @can('view maintenance companies')
                        <a href="{{ route('admin.blocked.companies') }}" class="block py-2 text-sm {{ request()->routeIs('admin.blocked.companies') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Maintenance Companies') }}</a>
                        @endcan
                        @can('view technicians')
                        <a href="{{ route('admin.blocked.technicians') }}" class="block py-2 text-sm {{ request()->routeIs('admin.blocked.technicians') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Technicians') }}</a>
                        @endcan
                        @can('view supervisors')
                        <a href="{{ route('admin.blocked.supervisors') }}" class="block py-2 text-sm {{ request()->routeIs('admin.blocked.supervisors') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Supervisors') }}</a>
                        @endcan
                    </div>
                </div>
                @endif

                <!-- Order Management Dropdown -->
                @can('view orders')
                <div x-data="{ open: {{ request()->is('admin/orders*') || request()->is('admin/appointments*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white {{ request()->is('admin/orders*') || request()->is('admin/appointments*') ? 'text-primary dark:text-white font-black' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="font-bold">{{ __('Orders Management') }}</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="space-y-1 {{ app()->getLocale() == 'ar' ? 'pr-12' : 'pl-12' }}">
                        <a href="{{ route('admin.orders.index') }}" class="block py-2 text-sm {{ (request()->routeIs('admin.orders.index') && !request()->has('tab')) || request()->get('tab') == 'new' ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('New Orders') }}</a>
                        <a href="{{ route('admin.appointments.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.appointments.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Appointments') }}</a>
                        <a href="{{ route('admin.orders.premium') }}" class="block py-2 text-sm {{ request()->routeIs('admin.orders.premium') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Orders') }}</a>
                    </div>
                </div>
                @endcan

                <!-- Contract Management -->
                @can('view orders')
                <a href="{{ route('admin.contracts.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.contracts.*') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-bold">{{ __('Contracts Management') }}</span>
                </a>
                @endcan

                <!-- Service Management -->
                @can('view services')
                <a href="{{ route('admin.services.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.services.*') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span class="font-bold">{{ __('Services Management') }}</span>
                </a>
                @endcan

                <!-- Inventory Management -->
                @can('view contents')
                <a href="{{ route('admin.inventory.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.inventory.*') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span class="font-bold">{{ __('Inventory Management') }}</span>
                </a>
                @endcan
                
                <!-- City Management -->
                @can('view cities and districts')
                <a href="{{ route('admin.cities.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.cities.*') || request()->routeIs('admin.districts.*') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="font-bold">{{ __('Cities Management') }}</span>
                </a>
                @endcan

                <!-- Content Management -->
                @can('view contents')
                <a href="{{ route('admin.contents.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.contents.*') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="font-bold">{{ __('Content Management') }}</span>
                </a>
                @endcan



                @can('view financial reports')
                <div x-data="{ open: {{ request()->is('admin/payments*') || request()->is('admin/invoices*') || request()->is('admin/financial-settlements*') || request()->is('admin/platform-profits*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white {{ request()->is('admin/payments*') || request()->is('admin/invoices*') || request()->is('admin/financial-settlements*') || request()->is('admin/platform-profits*') ? 'text-primary dark:text-white font-black' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-bold">{{ __('Financial Management') }}</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="space-y-1 {{ app()->getLocale() == 'ar' ? 'pr-12' : 'pl-12' }}">
                        <a href="{{ route('admin.payments.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.payments.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Payments') }}</a>
                        <a href="{{ route('admin.invoices.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.invoices.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Invoices') }}</a>
                        <a href="{{ route('admin.financial-settlements.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.financial-settlements.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Settlements') }}</a>
                        <a href="{{ route('admin.refunds.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.refunds.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Returns') }}</a>
                    </div>
                </div>
                @endcan

                <!-- Ratings -->
                @can('view services')
                <a href="{{ route('admin.reviews.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.reviews.*') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    <span class="font-bold">{{ __('Ratings') }}</span>
                </a>
                @endcan

                <!-- Reports -->
                @can('view financial reports')
                <a href="{{ route('admin.reports.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.reports.*') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-bold">{{ __('Reports') }}</span>
                </a>
                @endcan

                <!-- Notification Management -->
                @can('view notifications')
                <a href="{{ route('admin.broadcast-notifications.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.broadcast-notifications.*') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black' : 'hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="font-bold">{{ __('Notification Management') }}</span>
                </a>
                @endcan

                @can('view settings')
                <div x-data="{ open: {{ request()->is('admin/settings*') || request()->routeIs('admin.profile.show') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white {{ request()->is('admin/settings*') || request()->routeIs('admin.profile.show') ? 'text-primary dark:text-white font-black' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="font-bold">{{ __('Settings') }}</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="space-y-1 {{ app()->getLocale() == 'ar' ? 'pr-12' : 'pl-12' }}">
                        <a href="{{ route('admin.profile.show') }}" class="block py-2 text-sm {{ request()->routeIs('admin.profile.show') ? 'bg-primary/5 dark:bg-white/10 text-primary dark:text-white font-black px-4 rounded-lg' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Personal Account') }}</a>
                        <a href="{{ route('admin.settings.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.settings.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Platform Settings') }}</a>
                        <a href="{{ route('admin.platform-profits.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.platform-profits.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Platform Profits') }}</a>
                        <a href="{{ route('admin.terms.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.terms.*') || request()->routeIs('admin.privacy-policies.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Terms and Policies') }}</a>
                        <a href="{{ route('admin.social-links.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.social-links.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Social Links') }}</a>
                    </div>
                </div>
                @endcan

                @can('view inquiry and support')
                <div x-data="{ open: {{ request()->is('admin/faqs*') || request()->is('admin/inquiries*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white {{ request()->is('admin/faqs*') || request()->is('admin/inquiries*') ? 'text-primary dark:text-white font-black' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="font-bold">{{ __('Support & Help') }}</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-collapse class="space-y-1 {{ app()->getLocale() == 'ar' ? 'pr-12' : 'pl-12' }}">
                        <a href="{{ route('admin.faqs.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.faqs.*') ? 'text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('FAQs Management') }}</a>
                        <a href="{{ route('admin.complaints.index') }}" class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.complaints.*') ? 'bg-primary/10 dark:bg-white/10 text-primary dark:text-white font-black' : 'text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-white' }}">{{ __('Customer Service and Complaints') }}</a>
                    </div>
                </div>
                @endcan
            </nav>

            <!-- Sidebar Footer: Logout -->
            <div class="p-6 border-t border-slate-100 dark:border-white/5 mt-auto">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all group">
                        <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="font-black">{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-hidden bg-slate-50 dark:bg-[#0F0F1E]">
            
            <!-- Premium Header -->
            <header class="h-20 bg-white dark:bg-[#1A1A31]/50 backdrop-blur-3xl border-b border-slate-100 dark:border-white/5 flex items-center justify-between px-4 md:px-8 z-20">
                <div class="flex items-center gap-4 md:gap-6">
                    <!-- Mobile Sidebar Toggle -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-white hover:text-primary dark:hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>

                    <div class="hidden md:flex flex-col text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <h1 class="text-lg md:text-xl font-black text-slate-800 dark:text-white leading-tight truncate max-w-[150px] md:max-w-none">{{ __('Welcome') }}, {{ Auth::user()->name }}</h1>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 md:gap-4">
                    <!-- Notifications -->
                    <div x-data="{ 
                        dropdownOpen: false, 
                        count: {{ $adminUnreadCount ?? 0 }},
                        async markRead(id, event) {
                            event.stopPropagation();
                            try {
                                const response = await fetch(`{{ route('admin.notifications.markRead', ':id') }}`.replace(':id', id), {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json'
                                    }
                                });
                                if (response.ok) {
                                    this.count = Math.max(0, this.count - 1);
                                    // Change style instead of removing
                                    const item = event.target.closest('.notification-item');
                                    item.classList.add('opacity-50', 'bg-slate-100', 'dark:bg-white/10');
                                    item.classList.remove('font-bold'); // Assuming unread has font-bold, though current CSS doesn't show it explicitly
                                    
                                    // Hide the checkmark button to prevent re-clicks
                                    event.target.closest('button').style.display = 'none';
                                }
                            } catch (e) { console.error(e); }
                        },
                        async markAllRead() {
                            try {
                                const response = await fetch(`{{ route('admin.notifications.markAllRead') }}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json'
                                    }
                                });
                                if (response.ok) {
                                    this.count = 0;
                                    this.dropdownOpen = false;
                                    window.location.reload();
                                }
                            } catch (e) { console.error(e); }
                        }
                    }" @click.away="dropdownOpen = false" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 dark:text-slate-200 hover:text-primary dark:hover:text-white transition-all relative group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span x-show="count > 0" x-text="count" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white dark:ring-[#1A1A31]"></span>
                        </button>

                        <!-- Notification Popup -->
                        <div x-show="dropdownOpen" x-cloak 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 translate-y-2" 
                             x-transition:enter-end="opacity-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 translate-y-2" 
                             class="fixed inset-x-4 top-10 md:absolute md:top-full md:mt-3 md:inset-x-auto w-auto md:w-[28rem] bg-white dark:bg-[#1A1A31] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden z-[60] py-2"
                             style="{{ app()->getLocale() == 'ar' ? 'left: 10px !important; right: auto !important;' : 'right: 10px !important; left: auto !important;' }}">
                            <div class="px-5 py-3 border-b border-slate-50 dark:border-white/5 flex items-center justify-between">
                                <h3 class="font-bold text-sm text-slate-800 dark:text-white">{{ __('Notifications') }}</h3>
                                <div class="flex items-center gap-3">
                                    <button x-show="count > 0" @click="markAllRead()" class="text-[10px] font-bold text-primary hover:text-primary dark:hover:text-white-dark transition-colors">{{ __('Mark all as read') }}</button>
                                    <button @click="dropdownOpen = false" class="text-slate-400 hover:text-slate-600 dark:text-white dark:hover:text-white transition-colors md:hidden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="max-h-[70vh] md:max-h-96 overflow-y-auto custom-scrollbar">
                                @forelse(($adminNotifications ?? [])->take(5) as $notification)
                                @php
                                    $locale = app()->getLocale();
                                    $notifTitle = $locale === 'ar' ? ($notification->title_ar ?? $notification->title_en ?? __('Notification')) : ($notification->title_en ?? $notification->title_ar ?? __('Notification'));
                                    $notifBody  = $locale === 'ar' ? ($notification->body_ar ?? $notification->body_en ?? '') : ($notification->body_en ?? $notification->body_ar ?? '');
                                @endphp
                                <div x-data="{ expanded: false }" 
                                     @click="expanded = !expanded"
                                     class="notification-item px-5 py-4 border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-all relative cursor-pointer {{ $notification->is_read ? 'opacity-60' : '' }}">
                                    <!-- Unread dot -->
                                    @if(!$notification->is_read)
                                    <span class="absolute top-4 {{ app()->getLocale() == 'ar' ? 'left-5' : 'right-5' }} w-2 h-2 rounded-full bg-primary"></span>
                                    @endif
                                    <div class="flex gap-3 {{ app()->getLocale() == 'ar' ? 'pl-4' : 'pr-4' }}">
                                        <div class="w-10 h-10 rounded-full bg-[#1A1A31] dark:bg-white flex items-center justify-center text-white dark:text-slate-900 font-black flex-shrink-0 text-sm">
                                            {{ mb_strtoupper(mb_substr($notifTitle, 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="text-xs font-bold text-slate-800 dark:text-white leading-snug">{{ $notifTitle }}</p>
                                                <svg class="w-3 h-3 text-slate-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                            
                                            <div class="text-[11px] text-slate-500 dark:text-slate-300 leading-relaxed break-words">
                                                <p :class="expanded ? '' : 'line-clamp-2'">{{ $notifBody }}</p>
                                                
                                                <div x-show="expanded" x-collapse @click.stop>
                                                    <div class="pt-3 space-y-3">
                                                        <!-- Detailed Fields from data -->
                                                        @if(isset($notification->data['customer_name']))
                                                            <div class="flex items-center gap-2 text-[10px] bg-slate-50 dark:bg-white/5 p-2 rounded-lg">
                                                                <span class="text-slate-400 font-bold uppercase tracking-wider">{{ __('Customer') }}:</span> 
                                                                <span class="text-slate-700 dark:text-white font-black">{{ $notification->data['customer_name'] }}</span>
                                                            </div>
                                                        @endif

                                                        <!-- Actions for Technician Requests -->
                                                        @if(($notification->data['type'] ?? $notification->type) == 'technician_request' && isset($notification->data['request_id']))
                                                            @php
                                                                $techRequest = \App\Models\TechnicianRequest::find($notification->data['request_id']);
                                                            @endphp
                                                            @if($techRequest && $techRequest->status === 'pending')
                                                            <div class="flex items-center gap-2">
                                                                <form action="{{ route('admin.technician-requests.accept', $notification->data['request_id']) }}" method="POST" class="flex-1">
                                                                    @csrf
                                                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                                                    <input type="hidden" name="password" value="password123">
                                                                    <button type="submit" class="w-full py-2 bg-primary text-white rounded-xl text-[10px] font-black flex items-center justify-center gap-1 hover:brightness-110 transition-all shadow-lg shadow-primary/20">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                        {{ __('Accept') }}
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('admin.technician-requests.refuse', $notification->data['request_id']) }}" method="POST" class="flex-1">
                                                                    @csrf
                                                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                                                    <input type="hidden" name="rejection_reason" value="Rejected via notification">
                                                                    <button type="submit" class="w-full py-2 bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 hover:bg-slate-200 dark:hover:bg-white/20 transition-all">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                        {{ __('Refuse') }}
                                                                    </button>
                                                                </form>
                                                            </div>
                                                            @endif
                                                        @endif

                                                        <!-- Actions for New Orders -->
                                                        @if(($notification->data['type'] ?? $notification->type) == 'new_order' && isset($notification->data['order_id']))
                                                            @php
                                                                $order = \App\Models\Order::find($notification->data['order_id']);
                                                            @endphp
                                                            @if($order && $order->status === 'new')
                                                            <div class="flex items-center gap-2">
                                                                <a href="{{ route('admin.orders.show', $order->id) }}?notification_id={{ $notification->id }}" class="flex-1 py-1.5 bg-primary text-white rounded-xl text-[10px] font-black flex items-center justify-center gap-1 hover:brightness-110 transition-all shadow-lg shadow-primary/20">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                    {{ __('Details') }}
                                                                </a>
                                                                <form action="{{ route('admin.orders.refuse', $order->id) }}" method="POST" class="flex-1">
                                                                    @csrf
                                                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                                                    <input type="hidden" name="rejection_reason" value="Rejected via notification">
                                                                    <button type="submit" class="w-full py-1.5 bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white rounded-xl text-[10px] font-bold flex items-center justify-center gap-1 hover:bg-slate-200 dark:hover:bg-white/20 transition-all">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                        {{ __('Refuse') }}
                                                                    </button>
                                                                </form>
                                                            </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Type badge + time -->
                                            <div class="flex items-center gap-2 flex-wrap mt-2">
                                                @if($notification->type)
                                                <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300 uppercase tracking-wide">{{ __($notification->type) }}</span>
                                                @endif
                                                <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        <!-- ... menu -->
                                        <div x-data="{ openMenu: false }" class="absolute top-3 {{ app()->getLocale() == 'ar' ? 'left-3' : 'right-3' }}" @click.stop>
                                            <button @click="openMenu = !openMenu" class="text-slate-300 hover:text-slate-600 dark:text-white dark:hover:text-white transition-colors p-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                                            </button>
                                            <div x-show="openMenu" @click.away="openMenu = false" class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-1 w-40 bg-white dark:bg-[#1A1A31] rounded-xl shadow-xl border border-slate-100 dark:border-white/10 z-30 overflow-hidden" style="display:none;">
                                                @if(!$notification->is_read)
                                                <button @click="markRead('{{ $notification->id }}', $event)" class="w-full text-start px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-colors flex items-center gap-2">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    {{ __('Mark as read') }}
                                                </button>
                                                @endif
                                                <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full text-start px-4 py-2.5 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-white/5 transition-colors flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="p-10 text-center text-slate-400 dark:text-slate-500 flex flex-col items-center gap-3">
                                    <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    <p class="text-xs font-bold">{{ __('No new notifications') }}</p>
                                </div>
                                @endforelse
                                
                                @if(($adminUnreadCount ?? 0) > 5)
                                <div class="px-5 py-3 border-t border-slate-50 dark:border-white/5 text-center">
                                    <a href="{{ route('admin.notifications.index') }}" class="text-xs font-black text-primary hover:underline">{{ __('Show More') }} ({{ $adminUnreadCount - 5 }}+)</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-primary dark:hover:text-white transition-all">
                        <svg id="sunIcon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.727 12.727L12 12l8.485 8.485z"></path></svg>
                        <svg id="moonIcon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
                    </button>

                    <!-- Language -->
                    <div x-data="{ langOpen: false }" @click.away="langOpen = false" class="relative">
                        <button @click="langOpen = !langOpen" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/5 text-slate-600 dark:text-white/70 text-xs font-bold hover:text-primary dark:hover:text-white transition-all">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe" viewBox="0 0 16 16">
  <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7.03 7.03 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5zm6.853 3.472A7 7 0 0 0 13.745 12H11.91a9.3 9.3 0 0 1-.64 1.539 7 7 0 0 1-.597.933M8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z"/>
</svg>                            <span class="hidden sm:inline">{{ app()->getLocale() == 'ar' ? 'العربية' : 'English' }}</span>
                        </button>
                        <div x-show="langOpen" x-cloak class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-[#1A1A31] rounded-xl shadow-2xl border border-slate-100 dark:border-white/10 z-50 overflow-hidden">
                            <a href="{{ route('admin.switch-language', 'ar') }}" class="block px-4 py-3 text-xs font-bold text-slate-700 dark:text-white hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white">العربية</a>
                            <a href="{{ route('admin.switch-language', 'en') }}" class="block px-4 py-3 text-xs font-bold text-slate-700 dark:text-white hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white">English</a>
                        </div>
                    </div>

                    <!-- User Circle Dropdown -->
                    <div x-data="{ open: false, logoutModal: false }" class="relative">
                        <button @click="open = !open; if(open) logoutModal = false" class="flex items-center gap-3 p-1 rounded-xl bg-slate-50 dark:bg-white/5 hover:bg-slate-100 transition-all border border-transparent hover:border-slate-200">
                            <div class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center font-black">
                                {{ mb_substr(Auth::user()->name ?? 'A', 0, 1) }}
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <!-- Profile Dropdown -->
                        <div x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-56 bg-white dark:bg-[#1A1A31] rounded-2xl shadow-2xl border border-slate-100 dark:border-white/5 py-2 z-50">
                            <a href="{{ route('admin.profile.show') }}" class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 dark:hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ __('Account Settings') }}
                            </a>
                            <div class="h-px bg-slate-100 dark:bg-white/5 my-1"></div>
                            <button @click="logoutModal = true; open = false" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-black text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                {{ __('Logout') }}
                            </button>
                        </div>

                        <!-- Logout Modal -->
                        <div x-show="logoutModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                            <div x-show="logoutModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="logoutModal = false"></div>
                            <div x-show="logoutModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-[#1A1A31] p-8 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/5 w-full max-w-md text-center">
                                <div class="w-20 h-20 bg-red-50 dark:bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-red-500">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </div>
                                <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">{{ __('Confirm Logout') }}</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mb-8">{{ __('Are you sure you want to log out of your account?') }}</p>
                                <div class="flex gap-4">
                                    <button @click="logoutModal = false" class="flex-1 py-4 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 rounded-2xl font-bold hover:bg-slate-200 transition-all">{{ __('Cancel') }}</button>
                                    <form action="{{ route('admin.logout') }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full py-4 bg-red-500 text-white rounded-2xl font-black shadow-lg shadow-red-500/20 hover:bg-red-600 transition-all">{{ __('Logout') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Scroll Area -->
            <div class="flex-1 overflow-y-auto p-6 md:p-8 custom-scrollbar">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const platformSetting = '{{ \App\Models\Setting::getByKey("system_mode", "light") }}';
        const forcedTheme = '{{ session("force_theme") }}';
        
        const updateTheme = () => {
            let theme = localStorage.getItem('theme');
            const isSystemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // 1. If the admin just saved settings (forced update)
            if (forcedTheme) {
                theme = (forcedTheme === 'auto') ? (isSystemDark ? 'dark' : 'light') : forcedTheme;
                localStorage.setItem('theme', theme);
                if (forcedTheme === 'auto') localStorage.removeItem('user_manual_toggle'); // Reset manual override if auto is chosen
            }
            
            // 2. If platform is NOT auto, enforce it as global default unless user manually toggled
            if (platformSetting !== 'auto' && !localStorage.getItem('user_manual_toggle')) {
                theme = platformSetting;
                localStorage.setItem('theme', theme);
            }
            
            // 3. If platform IS auto and no manual toggle, follow system
            if (platformSetting === 'auto' && !localStorage.getItem('user_manual_toggle')) {
                theme = isSystemDark ? 'dark' : 'light';
                localStorage.setItem('theme', theme);
            }

            // 4. Final fallback
            if (!theme) {
                theme = isSystemDark ? 'dark' : 'light';
            }
            
            document.documentElement.classList.toggle('dark', theme === 'dark');
        };

        themeToggle.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            const newTheme = isDark ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            localStorage.setItem('user_manual_toggle', 'true');
        });

        updateTheme();
        
        // Listen for system theme changes if in auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (platformSetting === 'auto' && !localStorage.getItem('user_manual_toggle')) {
                const newTheme = e.matches ? 'dark' : 'light';
                document.documentElement.classList.toggle('dark', e.matches);
                localStorage.setItem('theme', newTheme);
            }
        });
    </script>
    <!-- Global Alerts -->
    <div class="fixed bottom-4 right-4 left-4 md:left-auto md:bottom-10 md:right-10 z-[2000] flex flex-col items-end gap-3 pointer-events-none">
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8"
             class="pointer-events-auto bg-emerald-500 text-white p-5 rounded-2xl shadow-2xl flex items-start gap-4 border border-emerald-400/20 max-w-full md:max-w-md overflow-hidden">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="flex-1 min-w-0 py-2">
                <p class="font-black text-sm tracking-wide break-words leading-relaxed">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="shrink-0 opacity-50 hover:opacity-100 transition-opacity p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8"
             class="pointer-events-auto bg-rose-500 text-white p-5 rounded-2xl shadow-2xl flex items-start gap-4 border border-rose-400/20 max-w-full md:max-w-md overflow-hidden text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="flex-1 min-w-0 py-2">
                <p class="font-black text-sm tracking-wide break-words leading-relaxed">{{ session('error') }}</p>
            </div>
            <button @click="show = false" class="shrink-0 opacity-50 hover:opacity-100 transition-opacity p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        @endif
    </div>

    @yield('scripts')
</body>
</html>
