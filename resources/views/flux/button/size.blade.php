<!-- Stub minimal para flux::button.size -->
@props([
    'size' => 'base',
])
<span {{ $attributes->class([
    'inline-flex items-center justify-center rounded-lg font-medium transition-colors',
    match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'lg' => 'px-6 py-3 text-base',
        'xs' => 'px-2 py-1 text-xs',
        default => 'px-4 py-2 text-sm',
    }
]) }}>
    {{ $slot }}
</span>
