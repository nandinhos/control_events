<!-- Stub minimal para flux::errors -->
@props([
    'name' => null,
    'bag' => 'default',
])
@if($errors && $errors->has($name ?: $attributes->whereStartsWith('wire:model')->first()))
    <p {{ $attributes->class(['mt-1 text-sm text-red-600 dark:text-red-400']) }} data-flux-error>
        {{ $errors->first($name ?: $attributes->whereStartsWith('wire:model')->first()) }}
    </p>
@endif
