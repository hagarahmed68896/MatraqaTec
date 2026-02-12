@props(['name', 'title' => 'Confirm Action', 'message' => 'Are you sure you want to perform this action?', 'type' => 'danger'])

<div x-data="{ 
        show: false, 
        name: '{{ $name }}', 
        title: '{{ $title }}', 
        message: '{{ $message }}',
        actionUrl: '',
        type: '{{ $type }}'
    }"
     x-show="show"
     x-on:open-modal.window="if ($event.detail === name || $event.detail.name === name) { 
        show = true; 
        if ($event.detail.url) actionUrl = $event.detail.url;
        if ($event.detail.title) title = $event.detail.title;
        if ($event.detail.message) message = $event.detail.message;
        if ($event.detail.type) type = $event.detail.type;
     }"
     x-on:close-modal.window="show = false"
     x-on:keydown.escape.window="show = false"
     class="fixed inset-0 z-[100] overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
         @click="show = false"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-[#1A1A31] px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6 border border-slate-100 dark:border-white/5">
            
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10 transition-colors"
                     :class="type === 'danger' ? 'bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-500' : 'bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-500'">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <template x-if="type === 'danger'">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </template>
                        <template x-if="type !== 'danger'">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </template>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                    <h3 class="text-base font-bold leading-6 text-slate-900 dark:text-white" x-text="title"></h3>
                    <div class="mt-2">
                        <p class="text-sm text-slate-500 dark:text-slate-400" x-text="message"></p>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                 <form :action="actionUrl" method="POST">
                    @csrf
                    <button type="submit" 
                            class="inline-flex w-full justify-center rounded-xl px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors"
                            :class="type === 'danger' ? 'bg-red-600 hover:bg-red-500' : 'bg-[#1A1A31] hover:bg-black'">
                        {{ __('Confirm') }}
                    </button>
                </form>
                <button type="button" @click="show = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white dark:bg-white/5 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-white/10 hover:bg-slate-50 dark:hover:bg-white/10 sm:mt-0 sm:w-auto transition-colors">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
