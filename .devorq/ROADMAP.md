# Roadmap de Implementação — control_events MVP

**Versão:** 1.0  
**Data:** 2026-05-30  
**Total de Stories:** 26  
**Duração Estimada:** 12 semanas (3 meses)

---

## Visão Geral

```
Semanas  1---2---3---4---5---6---7---8---9---10--11--12
         |--------Phase 1--------|--------Phase 2--------|
         |  Foundation & RBAC  |  Core Finance        |
         |                     |  Provisioning       |
         |--------Phase 3-------------------|-Phase 4-|
         |  Reconciliation Engine           |Intl & Hub|
```

---

## Phase 1: Foundation & RBAC
**Período:** Semanas 1-3  
**Prioridade:** CRÍTICA  
**Dependências:** Nenhuma (primeira fase)

### Executores
- **Track A:** architect → design state machine
- **Track B:** backend + security → RBAC middleware, gates, policies
- **Track C:** backend → Spatie state machine, guards

### Stories

| ID | Story | Criteria | Dependencies |
|----|-------|----------|--------------|
| RBAC-001 | Middleware RBAC 5 roles | Helper role(), gates definidos | - |
| RBAC-002 | Gate/Policy Layer | 3 Policies implementadas | RBAC-001 |
| RBAC-003 | Route Protection | Middleware VerifyRole | RBAC-001 |
| RBAC-004 | Admin-only Filter | Global scope, segregação dados | RBAC-002 |
| SM-001 | Design State Machine | Diagrama documentado | - |
| SM-002 | Spatie Implementation | State machine configurada | SM-001 |
| SM-003 | Transition Guards | Guards funcionais | SM-002, PROV-001 |

### Critérios de Passagem
- [ ] PHPStan level 5: 0 errors
- [ ] Tests: 100% passing
- [ ] Playwright: 10/10 routes

---

## Phase 2: Core Finance & Provisioning
**Período:** Semanas 4-6  
**Prioridade:** P0  
**Dependências:** Phase 1 concluída

### Executores
- **Track A:** backend → ProvisionamentoValidationService
- **Track B:** frontend → Splits UI

### Stories

| ID | Story | Criteria | Dependencies |
|----|-------|----------|--------------|
| PROV-001 | Provisionamento Validation | Soma booking = valor_bruto | SM-002 |
| PROV-002 | Alerta Incompleto | Banner visual | PROV-001 |
| PROV-003 | Splits UI | Panorama/Coral/Artista | PROV-001 |
| PROV-004 | Split Validation | Soma splits = parcela | PROV-003 |
| PAY-001 | Block Pagar | Bloqueio funciona | SM-003 |

### Critérios de Passagem
- [ ] Provisionamento validado
- [ ] Splits 70%/30% funcionais
- [ ] Pagar bloqueado para contratos não "Em Execução"

---

## Phase 3: Reconciliation Engine
**Período:** Semanas 7-9  
**Prioridade:** P1  
**Dependências:** Phase 1 + Phase 2 (parcial)

### Executores
- **Track A:** backend → Fuzzy search, auto-match
- **Track B:** frontend → N-para-1 UI, quick adjustment

### Stories

| ID | Story | Criteria | Dependencies |
|----|-------|----------|--------------|
| REC-001 | Fuzzy Search | <500ms response | - |
| REC-002 | Auto-match Algorithm | Top 5 matches rankeados | REC-001 |
| REC-003 | N-para-1 UI | Multi-select + soma real-time | REC-002 |
| REC-004 | Quick Adjustment | Edição inline + auditoria | PAY-002 |
| REC-005 | Reconciliation Confirm | Botão só habilita se soma batê | REC-003 |
| REC-006 | Add Lancamento Button | Modal + linked | REC-003 |

### Critérios de Passagem
- [ ] Auto-match >= 80% accuracy
- [ ] N-para-1 funcionando
- [ ] Quick adjustment registrado

---

## Phase 4: International & Hub Artista
**Período:** Semanas 10-12  
**Prioridade:** P1  
**Dependências:** Phase 1 (RBAC)

### Executores
- **Track A:** database + backend → multicurrency, cambio
- **Track B:** frontend → International UI, Hub dashboard

### Stories

| ID | Story | Criteria | Dependencies |
|----|-------|----------|--------------|
| INT-001 | Multicurrency Fields | BRL/USD/EUR/GBP columns | - |
| INT-002 | Cambio Service | Conversão funcional | INT-001 |
| INT-003 | Aguardando Cambio | Status aplicado | INT-002 |
| INT-004 | Admin Exchange Rate | Campo só para admin | INT-003, RBAC |
| INT-005 | Movimentacao Interna | Tracking sem extrato | INT-001 |
| INT-006 | International UI | Tabs Import/Export | INT-001, INT-005 |
| HUB-001 | Hub Artista Dashboard | KPIs + filtros | RBAC |
| HUB-002 | Artist Reconciliation | View filtrada | HUB-001, REC-005 |

### Critérios de Passagem
- [ ] Multimoeda funcionando
- [ ] Hub Artista completo
- [ ] Internacional module usável

---

## Dependências entre Stories

```
RBAC-001 ──┬── RBAC-002 ─── RBAC-004 ──── HUB-001 ──── HUB-002
           │                │
           └── RBAC-003 ────┘
                          │
SM-001 ── SM-002 ── SM-003 ──┬── PROV-001 ──┬── PROV-002
                              │             │
                              └── PROV-003 ──┴── PROV-004
                                            │
                                            └── PAY-001
                                                      
REC-001 ── REC-002 ── REC-003 ──┬── REC-005 ──── HUB-002
                                 │
                                 └── REC-004
                                
INT-001 ── INT-002 ── INT-003 ──┬── INT-004
                                 │
                                 └── INT-005 ── INT-006
```

---

## Priorização de Features

### MUST HAVE (P0) — Semanas 1-6
1. RBAC completo (CRÍTICO)
2. State Machine contratos
3. Provisionamento validation
4. Splits de destino

### SHOULD HAVE (P1) — Semanas 7-12
5. Reconciliation completo
6. Multimoeda
7. Hub Artista

### NICE TO HAVE (P2) — backlog
8. AES-256 encryption (já existe em partes)
9. Histórico de aging auditável

---

## Self-Evolution Integration

Após cada phase, capturar lições:

```bash
./self-evolution/lesson-extractor.sh "Phase 1 RBAC implementation" "Middleware + Policies + Gates"
./self-evolution/lesson-extractor.sh "Phase 1 State Machine" "Contract lifecycle Spatie"
./self-evolution/lesson-extractor.sh "Phase 2 Provisioning" "Booking validation + splits"
./self-evolution/lesson-extractor.sh "Phase 3 Reconciliation" "Fuzzy search + auto-match"
./self-evolution/lesson-extractor.sh "Phase 4 International" "Multicurrency + Hub"
```

---

## Testes de Integração

Após cada phase:

```bash
# Phase 1
php artisan test --filter=RbacTest
php artisan test --filter=StateMachineTest

# Phase 2  
php artisan test --filter=ProvisionamentoTest
php artisan test --filter=SplitTest

# Phase 3
php artisan test --filter=ReconciliationTest
php artisan test --filter=AutoMatchTest

# Phase 4
php artisan test --filter=InternationalTest
php artisan test --filter=HubArtistaTest
```

---

## Gates de Qualidade

Para cada phase, MUST PASS antes de prosseguir:

| Gate | Phase 1 | Phase 2 | Phase 3 | Phase 4 |
|------|---------|---------|---------|---------|
| PHPStan | 0 errors | 0 errors | 0 errors | 0 errors |
| Tests | 100% | 100% | 100% | 100% |
| Playwright | 10/10 | 10/10 | 10/10 | 10/10 |
| Code Review | Approved | Approved | Approved | Approved |

---

## Versionamento

| Versão | Data | Changes |
|--------|------|---------|
| 1.0 | 2026-05-30 | Initial roadmap |
