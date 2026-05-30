<!-- Stub minimal para flux::tab -->
@props([
    'selected' => null,
    'inline' => null,
])
<button
    type="button"
    {{ $attributes->class([
        'inline-flex items-center px-4 py-2 text-sm font-medium transition-colors',
        'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white',
        $selected ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : ''
    ]) }}
    data-flux-tab
>
    {{ $slot }}
</button>
