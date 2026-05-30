<!-- Stub minimal para flux::input.group.input -->
@props([
    'type' => 'text',
])
<input
    type="{{ $type }}"
    {{ $attributes->class([
        'block w-full min-w-0 rounded-lg border-zinc-300 shadow-sm transition-colors',
        'dark:border-zinc-700 dark:bg-zinc-900 dark:text-white',
        'focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
    ]) }}
    data-flux-control
/>
