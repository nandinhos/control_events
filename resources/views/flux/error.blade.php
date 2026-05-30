<!-- Stub minimal para flux::error -->
@props([
    'name' => null,
    'bag' => 'default',
])
@php
    $errorName = $name ?: ($attributes->whereStartsWith('wire:model')->first());
@endphp
@if($errorName && $errors->has($errorName))
    <p {{ $attributes->class(['mt-1 text-sm text-red-600 dark:text-red-400']) }} data-flux-error role="alert">
        {{ $errors->first($errorName) }}
    </p>
@endif
