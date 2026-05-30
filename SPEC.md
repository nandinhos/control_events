# SPEC.md — control_events MVP Implementation

**Data:** 2026-05-30
**Status:** IN PROGRESS — Phase 1 (Foundation & RBAC)
**Stack:** Laravel 13 + Livewire v4 + Flux Pro v2.14 + Sail + PostgreSQL 17
**Port:** localhost:50138

---

## Documentação de Referência

| Documento | Local | Descrição |
|-----------|-------|-----------|
| PRD Original | `docs/prd_event_control.md` | Requisitos completos do sistema |
| Regras de Negócio | `docs/regras_negocio_event_control.md` | RNs detalhadas |
| Roadmap | `.devorq/ROADMAP.md` | Roadmap de implementação |
| PRD v2 (Stories) | `.devorq/prd-v2.json` | 26 stories organizadas por phase |

---

## Contexto Atual

### Problema Original (Já Corrigido)
Dashboard quebrava com `$ano = 0` quando renderizado via `app()` no Blade.

### Correções Aplicadas
1. `$ano` com fallback `?: date('Y')` em todos os métodos
2. `strftime()` → `DateTime::format()`
3. `status` → `status_booking` na query
4. Tabela `extrato_bancario_transacaos` → `extrato_bancario_transacoes`
5. Nomenclatura `Entidade/` → `Entidades/`, `Contrato/` → `Contratos/`
6. Rota `entidades.index` e `contratos.index` corrigidas
7. Loop infinito HubArtista resolvido
8. `ConfirmDelete` removido (não existia)

---

## GATE Status

| Gate | Status | Descrição |
|------|---------|-----------|
| GATE-1 | ✅ | SPEC.md criado |
| GATE-2 | ✅ | PHPStan 0 errors, Tests passing |
| GATE-3 | ✅ | prd.json e context.json preenchidos |
| GATE-4 | ✅ | 3 lessons capturadas |
| GATE-5 | ✅ | devorq compact OK |
| GATE-6 | ✅ | Context7 checked |
| GATE-7 | ✅ | Playwright E2E 10/10 |

---

## Roadmap de Implementação

### Phase 1: Foundation & RBAC (Semanas 1-3) — **IN PROGRESS**
### Phase 2: Core Finance & Provisioning (Semanas 4-6)
### Phase 3: Reconciliation Engine (Semanas 7-9)
### Phase 4: International & Hub Artista (Semanas 10-12)

**Detalhes completos:** [`.devorq/ROADMAP.md`](.devorq/ROADMAP.md)

---

## Stories (26 Total)

### Phase 1 - Foundation & RBAC

| ID | Story | Status | Dependencies |
|----|-------|--------|--------------|
| RBAC-001 | Middleware RBAC 5 roles | ⏳ Pending | - |
| RBAC-002 | Gate/Policy Layer | ⏳ Pending | RBAC-001 |
| RBAC-003 | Route Protection | ⏳ Pending | RBAC-001 |
| RBAC-004 | Admin-only Filter | ⏳ Pending | RBAC-002 |
| SM-001 | Design State Machine | ⏳ Pending | - |
| SM-002 | Spatie Implementation | ⏳ Pending | SM-001 |
| SM-003 | Transition Guards | ⏳ Pending | SM-002 |

### Phase 2 - Core Finance

| ID | Story | Status | Dependencies |
|----|-------|--------|--------------|
| PROV-001 | Provisionamento Validation | ⏳ Pending | SM-002 |
| PROV-002 | Alerta Incompleto | ⏳ Pending | PROV-001 |
| PROV-003 | Splits UI | ⏳ Pending | PROV-001 |
| PROV-004 | Split Validation | ⏳ Pending | PROV-003 |
| PAY-001 | Block Pagar | ⏳ Pending | SM-003 |

### Phase 3 - Reconciliation

| ID | Story | Status | Dependencies |
|----|-------|--------|--------------|
| REC-001 | Fuzzy Search | ⏳ Pending | - |
| REC-002 | Auto-match Algorithm | ⏳ Pending | REC-001 |
| REC-003 | N-para-1 UI | ⏳ Pending | REC-002 |
| REC-004 | Quick Adjustment | ⏳ Pending | PAY-002 |
| REC-005 | Reconciliation Confirm | ⏳ Pending | REC-003 |
| REC-006 | Add Lancamento | ⏳ Pending | REC-003 |

### Phase 4 - International & Hub

| ID | Story | Status | Dependencies |
|----|-------|--------|--------------|
| INT-001 | Multicurrency Fields | ⏳ Pending | - |
| INT-002 | Cambio Service | ⏳ Pending | INT-001 |
| INT-003 | Aguardando Cambio | ⏳ Pending | INT-002 |
| INT-004 | Admin Exchange Rate | ⏳ Pending | INT-003, RBAC |
| INT-005 | Movimentacao Interna | ⏳ Pending | INT-001 |
| INT-006 | International UI | ⏳ Pending | INT-001, INT-005 |
| HUB-001 | Hub Artista Dashboard | ⏳ Pending | RBAC |
| HUB-002 | Artist Reconciliation | ⏳ Pending | HUB-001, REC-005 |

---

## Lições Aprendidas

### Lesson 001: Dashboard Component via `app()`
- **Problema:** `$ano` inicializado com `public int $ano = 0` — `mount()` não executa quando criado via `app()`
- **Solução:** TODAS variáveis devem usar fallback local `?: valor` dentro de cada método

### Lesson 002: Nomes de Colunas Devem Combinar com Migration
- **Problema:** Query usava `status` mas coluna real é `status_booking`
- **Solução:** Consultar migration antes de escrever queries

### Lesson 003: Flux Pro v2.14 Stubs
- **Problema:** Componentes Flux não existiam no projeto
- **Solução:** Copiar stubs do vendor ou recriar manualmente

---

## Out of Scope (MVP)
- Integração Open Finance / APIs bancárias
- Emissão automatizada de Notas Fiscais
- Assinatura eletrônica integrada (DocuSign/Clicksign)
- IA para conciliação 100% autônoma

---

## Comandos Úteis

```bash
# Playwright E2E
node playwright-e2e.cjs

# PHPStan
docker compose exec -T laravel.test vendor/bin/phpstan analyse

# Tests
docker compose exec -T laravel.test php artisan test

# Routes
docker compose exec -T laravel.test php artisan route:list
```
