<!-- Stub minimal para flux::field.label -->
@props([
    'for' => null,
])
<label for="{{ $for }}" {{ $attributes->class(['text-sm font-medium text-zinc-800 dark:text-white']) }}>
    {{ $slot }}
</label>
