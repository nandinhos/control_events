<!-- Stub minimal para flux::input -->
@props([
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
])
<input
    type="{{ $type }}"
    value="{{ $value }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->class([
        'block w-full rounded-lg border-zinc-300 shadow-sm transition-colors',
        'dark:border-zinc-700 dark:bg-zinc-900 dark:text-white',
        'focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
    ]) }}
    data-flux-control
/>
