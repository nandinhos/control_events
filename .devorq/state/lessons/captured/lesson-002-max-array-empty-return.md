# Lesson 002 — max(array, int) PHP 8.x retorna array em vez de int

## Contexto
- **Projeto:** control_events
- **Data:** 2025-05-30
- **Stack:** PHP 8.x

## Problema
Em PHP 8.x, `max()` chamada com array vazio retorna o array em si, NAO `0` ou `false`:

```php
$values = [];
$max = max($values, 0); // retorna [] (array vazio), NÃO 0
```

Isso causa falha silenciosa em cálculos de paginação e métricas.

## Solução
Sempre validar antes:

```php
$maxVal = max(array_column($records, 'amount'), 0);
$maxVal = $maxVal > 0 ? $maxVal : 1; // garante int > 0
```

**Regra:** Nunca confiar que `max()` retorna int quando uma das entradas pode ser array vazio. Usar guard clause.

## Tags
#php #php8 #bug #pagination #calculations
