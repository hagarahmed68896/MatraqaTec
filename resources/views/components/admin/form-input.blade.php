@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'help' => '',
])

<div class="space-y-2">
    @if($label)
    <label for="{{ $name }}" class="block text-xs font-black text-slate-600 dark:text-white/70 uppercase tracking-wider">
        {{ $label }}
        @if($required)
        <span class="text-red-500">*</span>
        @endif
    </label>
    @endif

    <input 
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all']) }}
    >

    @error($name)
    <p class="text-xs text-red-500 font-bold">{{ $message }}</p>
    @enderror

    @if($help)
    <p class="text-xs text-slate-500 dark:text-white/50">{{ $help }}</p>
    @endif
</div>
