@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md', // sm, md, lg, xl
])

@php
$sizeClasses = [
    'sm' => 'max-w-md',
    'md' => 'max-w-2xl',
    'lg' => 'max-w-4xl',
    'xl' => 'max-w-6xl',
];
@endphp

<div 
    x-data="{ open: false }"
    x-show="open"
    x-on:open-modal-{{ $id }}.window="open = true"
    x-on:close-modal-{{ $id }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full {{ $sizeClasses[$size] ?? $sizeClasses['md'] }} bg-white dark:bg-[#1A1A31] rounded-[2rem] shadow-2xl border border-slate-100 dark:border-white/5"
        >
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-white/5">
                <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $title }}</h3>
                <button 
                    @click="open = false"
                    class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 transition-all"
                >
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                {{ $slot }}
            </div>

            <!-- Footer (if provided) -->
            @isset($footer)
            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-100 dark:border-white/5">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </div>
</div>
