<!-- Stub minimal para flux::field -->
@props([
    'variant' => 'block',
])
<div {{ $attributes->class(['block']) }} data-flux-field>
    {{ $slot }}
</div>
