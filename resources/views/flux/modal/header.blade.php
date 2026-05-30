@blaze(fold: true)

@props([
    'closeable' => true,
])

<div class="flex items-center justify-between mb-4">
    <div class="flex-1">
        {{ $slot }}
    </div>
    @if($closeable)
        <flux:modal.close>
            <flux:button variant="ghost" icon="x-mark" size="sm" aria-label="{{ __('Close') }}" class="text-zinc-400! hover:text-zinc-800! dark:text-zinc-500! dark:hover:text-white!" />
        </flux:modal.close>
    @endif
</div>