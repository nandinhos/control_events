# Lesson 003 — Flux Pro v2.14 NAO tem componentes field/label/input

## Contexto
- **Projeto:** control_events
- **Data:** 2025-05-30
- **Stack:** Flux Pro v2.14 + Laravel Blade

## Problema
Flux Pro v2.14 (versão instalada no projeto) NAO possui os seguintes componentes:
- `flux:field.label`
- `flux:label`
- `flux:description`
- `flux:error`
- `flux:errors`
- `flux:button`
- `flux:input`

Estes existem apenas em versões MAIORES do Flux Pro. Usá-los causa erro de Blade: `Unable to locate a view`.

## Solução
Criar **stubs Blade** em `resources/views/flux/` para cada componente ausente. Cada stub不过是 um `<div>` wrapper com classes CSS apropriadas ou um componente Blade simples que encapsula a lógica.

Exemplo — `resources/views/flux/input.blade.php`:
```blade
@props(['type' => 'text', 'name', 'value' => null])
<input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" {{ $attributes->class('w-full ...') }}>
```

**Regra:** Ao migrar ou atualizar um projeto Laravel + Flux, verificar a versão exata do pacote e comparar com a documentação da versão específica — nunca assumir que componentes Blade existem sem verificar.

## Tags
#flux-pro #laravel #blade #stubs #compatibility
