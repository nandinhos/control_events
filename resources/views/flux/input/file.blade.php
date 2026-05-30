<!-- Stub minimal para flux::input.file -->
@props([
    'name' => null,
])
<input
    type="file"
    name="{{ $name }}"
    {{ $attributes->class([
        'block w-full rounded-lg border-zinc-300 shadow-sm transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold',
        'dark:border-zinc-700 dark:bg-zinc-900 dark:text-white file:dark:bg-zinc-800',
        'focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
    ]) }}
    data-flux-control
/>
