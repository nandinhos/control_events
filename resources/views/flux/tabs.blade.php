<!-- Stub minimal para flux::tabs -->
@props([
    'selected' => null,
])
<div {{ $attributes->class(['block']) }} data-flux-tabs>
    {{ $slot }}
</div>
