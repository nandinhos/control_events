<!-- Stub minimal para flux::button.icon -->
@props([
    'icon' => null,
    'variant' => 'outline',
    'size' => 'base',
])
<button
    type="button"
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-lg font-medium transition-colors',
        match($variant) {
            'outline' => 'border border-zinc-300 bg-white hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:hover:bg-zinc-700',
            'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700',
            'ghost' => 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800',
            default => 'border border-zinc-300 bg-white hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:hover:bg-zinc-700',
        },
        match($size) {
            'sm' => 'p-1.5',
            'lg' => 'p-2.5',
            'xs' => 'p-1',
            default => 'p-2',
        }
    ]) }}
>
    @if($icon)
        {{ $icon }}
    @else
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
    @endif
</button>
