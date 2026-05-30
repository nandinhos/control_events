<!-- Stub minimal para flux::button -->
@props([
    'type' => 'button',
    'variant' => 'outline',
    'size' => 'base',
])
<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-lg font-medium transition-colors',
        'focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
        'disabled:opacity-50',
        match($variant) {
            'outline' => 'border border-zinc-300 bg-white px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
            'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2',
            'ghost' => 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 px-4 py-2',
            default => 'border border-zinc-300 bg-white px-4 py-2 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
        }
    ]) }}
>
    {{ $slot }}
</button>
