<!-- Stub minimal para flux::label -->
@props([
    'badge' => null,
    'aside' => null,
    'trailing' => null,
    'srOnly' => null,
])
<label {{ $attributes->class(['inline-flex items-center text-sm font-medium', $srOnly ? 'sr-only' : 'text-zinc-800 dark:text-white']) }} data-flux-label>
    {{ $slot }}
    @if($badge)
        <span class="ms-1.5 text-xs bg-zinc-800/5 px-1.5 py-1 rounded dark:bg-white/10">{{ $badge }}</span>
    @endif
    @if($aside)
        <span class="ms-1.5 text-xs bg-zinc-800/5 px-1.5 py-1 rounded dark:bg-white/10">{{ $aside }}</span>
    @endif
    @if($trailing)
        <div class="ml-auto" data-flux-label-trailing>{{ $trailing }}</div>
    @endif
</label>
