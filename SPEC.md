# SPEC.md — control_events Systematic Debug

**Data:** 2026-05-30
**Status:** IN PROGRESS
**Stack:** Laravel 13 + Livewire v4 + Flux Pro v2.14 + Sail + PostgreSQL 17
**Port:** localhost:50138

---

## 1. Problema

Dashboard quebra com `$ano = 0` em contextos onde `mount()` não executa corretamente (renderização via `app()` no Blade). Erros SQL:
- `0000-05-01` (date overflow PostgreSQL) — `$ano` defaultava para 0
- `column "status" does not exist` — query usava coluna `status` mas tabela tem `status_booking`

---

## 2. Correções Ja Aplicadas

1. `Dashboard/Index.php` — `$ano` local com fallback `?: (int) date('Y')` em todos os metodos
2. `Dashboard/Index.php` — `strftime()` → `DateTime::format('M y')` (DB-agnostic)
3. `Dashboard/Index.php` — `status` → `status_booking` na query de eventosMes
4. `dashboard.blade.php` — `$evento['status']` → `$evento['status_booking']`

---

## 3. Success Criteria

### GATE-1: Spec Exists
- [x] SPEC.md criado com problemas e correções documentados

### GATE-2: Tests Pass
- [ ] PHPStan level 0 em app/Models, app/Services, app/Livewire (0 errors)
- [ ] `php artisan test` executa sem failures de lógica nossa
- [ ] `php artisan route:list` sem erros

### GATE-3: Context Documented
- [x] context.json preenchido com intent, stack, success_criteria

### GATE-4: Lessons Reviewed
- [ ] Verificar lessons para problemas similares ja conhecidos

### GATE-5: Handoff Ready
- [ ] devorq compact gera JSON válido

### GATE-6: Context7 Checked
- [ ] Consultar docs Laravel para padrões corretos

### GATE-7: Systematic Debug
- [ ] Playwright E2E — todas as rotas navegáveis sem 500
- [ ] Dashboard sem erros no load
- [ ] Todas as páginas autenticadas retornam HTTP 200

---

## 4. Verificação Visual (Playwright)

### Rotas a Testar
| Rota | Esperado | Método |
|------|----------|--------|
| `/` | 200 | Landing page |
| `/dashboard` | 200 | Dashboard KPIs |
| `/entidades` | 200 | CRUD Entidades |
| `/contratos` | 200 | CRUD Contratos |
| `/receber` | 200 | Contas a Receber |
| `/pagar` | 200 | Contas a Pagar |
| `/conciliacao` | 200 | Conciliacao Bancaria |
| `/hub-artista` | 200 | Hub Artista |
| `/internacional` | 200 | Placeholder |

### Fluxo
1. Login com usuário existente
2. Navegar para cada rota autenticada
3. Verificar HTTP 200 + sem erros 500 no page
4. Se erro → systematic-debugging → GATE-7 → corrigir → re-testar

---

## 5. Lições Aprendidas

### Lesson 1: Dashboard Component Renderizado via `app()` no Blade
- **Problema:** `$ano` inicializado com `public int $ano = 0` — quando o componente é criado via `app(\App\Livewire\Dashboard\Index::class)`, `mount()` não é chamado no contexto de render Blade
- **Solução:** TODAS as variáveis devem usar fallback local dentro de cada método `#[Computed]`
- **Stack:** Laravel + Livewire + Blade `app()` helper

### Lesson 2: Nomes de Colunas Devem Combinar com Migration
- **Problema:** Query usava `status` mas a coluna real é `status_booking`
- **Solução:** Sempre consultar a migration antes de escrever queries
- **Stack:** PostgreSQL + Laravel Eloquent

---

## 6. Out of Scope
- Tests funcionais de CRUD (não temos dados seed)
- Code review de estilo (Laravel Pint resolve)
- Internacionalização
- Webhooks / notificações