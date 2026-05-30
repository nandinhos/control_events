<!-- Stub minimal para flux::field.description -->
@props([
    'trailing' => null,
])
<p {{ $attributes->class(['mt-1 text-sm text-zinc-600 dark:text-zinc-400']) }} data-flux-description>
    {{ $slot }}
    @if($trailing)
        <span class="ml-auto">{{ $trailing }}</span>
    @endif
</p>
