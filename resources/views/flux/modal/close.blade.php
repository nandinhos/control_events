<!-- Stub minimal para flux::modal.close -->
@props([
    'icon' => null,
])
<button
    type="button"
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-lg p-1 text-zinc-400 transition-colors',
        'hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300'
    ]) }}
    aria-label="Close modal"
>
    @if($icon)
        {{ $icon }}
    @else
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    @endif
</button>
