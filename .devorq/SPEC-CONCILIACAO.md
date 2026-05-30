# SPEC: Conciliacao N-para-1 — Stories REC-003 a REC-006

**Data:** 2026-05-30  
**Projeto:** control_events  
**Stories:** REC-003, REC-004, REC-005, REC-006  
**Stack:** Laravel 13 + Livewire v4 + Flux Pro v2.14

---

## Contexto

O componente `Conciliacao/Index.php` já possui:
- Propriedades: `selectedLancamentos`, `transacaoParaNpara1`, `editavel`, etc.
- Métodos: `toggleLancamento()`, `conciliarNpara1()`, `enableEdit()`, `saveInlineEdit()`

**PROBLEMA:** A view `resources/views/livewire/conciliacao/index.blade.php` NÃO possui a UI do N-para-1.

---

## REC-003: N-para-1 Matching UI

### Descrição
UI para selecionar múltiplos títulos que somam ao valor de 1 transação bancária.

### Requisitos
1. **Painel lateral N-para-1** aparece quando usuário clica em transação bancária
2. **Lista de lançamentos não conciliados** com checkbox multi-select
3. **Soma em tempo real** dos selecionados vs valor da transação
4. **Select All** para selecionar todos visíveis

### UI Components

```
┌─────────────────────────────────────────────────────────┐
│  [X] Transação Bancária Selecionada                     │
│      Valor: R$ 1.000,00                                 │
├─────────────────────────────────────────────────────────┤
│  ☐ Lançamentos Não Conciliados                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │ [ ] Evento X | R$ 300,00 | 15/06/2026            │ │
│  │ [✓] Evento Y | R$ 400,00 | 20/06/2026            │ │
│  │ [✓] Evento Z | R$ 600,00 | 25/06/2026             │ │
│  └───────────────────────────────────────────────────┘ │
│                                                         │
│  Soma: R$ 1.000,00 ✓                                   │
│ Diferença: R$ 0,00                                     │
│                                                         │
│  [Selecionar Todos] [Limpar Seleção]                    │
│                                                         │
│  [Conciliar N-para-1] (habilitado se diferença = 0)   │
└─────────────────────────────────────────────────────────┘
```

### Acceptance Criteria
- [ ] Painel N-para-1 abre ao selecionar transação
- [ ] Multi-select funciona com checkbox
- [ ] Soma atualiza em tempo real
- [ ] Select All seleciona todos visíveis
- [ ] Diferença é calculada corretamente

---

## REC-004: Quick Value Adjustment

### Descrição
Campo editável inline para ajustar valor da provisão antes de conciliar.

### Requisitos
1. **Ícone de edição** (lápis) aparece ao passar mouse no valor do lançamento
2. **Campo editável inline** substitui o valor quando clicado
3. **Registro em AuditLog** (model já existe: `ConciliacaoBancariaLink` tem histórico)

### UI Components

```
┌─────────────────────────────────────────────────────┐
│ Evento Tal...                          [R$ 1.000,00]│
│                                              ✏️     │
└─────────────────────────────────────────────────────┘
         ↓ CLICOU NO LÁPIS
┌─────────────────────────────────────────────────────┐
│ Evento Tal...                          [________] 💾 │
│                                         [Cancelar]  │
└─────────────────────────────────────────────────────┘
```

### Acceptance Criteria
- [ ] Ícone de edição aparece no hover
- [ ] Campo editável substitui valor
- [ ] Salvar persiste o novo valor
- [ ] Cancelar restaura valor original
- [ ] Log de auditoria registrado

---

## REC-005: Reconciliation Confirmation

### Descrição
Botão confirmar que só habilita se soma dos títulos = valor do extrato.

### Requisitos
1. **Botão "Conciliar N-para-1"** desabilitado se diferença > R$ 0,01
2. **Feedback visual** quando soma não bate (mostrar diferença em vermelho)
3. **Validação server-side** no método `conciliarNpara1()`

### Acceptance Criteria
- [ ] Botão desabilitado se diferença > 0.01
- [ ] Diferença em vermelho se não bate
- [ ] Diferença em verde se bate
- [ ] Validação server-side não permite conciliacao se soma divergir

---

## REC-006: Add New Lancamento Button

### Descrição
Botão para adicionar novo lançamento vinculado ao contrato.

### Requisitos
1. **Botão "Novo Lançamento"** no painel N-para-1
2. **Modal de criação rápida** com campos mínimos
3. **Vínculo automático** com a transação bancária selecionada

### UI Components

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  [+ Novo Lançamento]                                    │
│                                                         │
└─────────────────────────────────────────────────────────┘
         ↓ CLICOU
┌─────────────────────────────────────────────────────────┐
│  Novo Lançamento                                        │
│                                                         │
│  Tipo:  [Contas a Receber ▼]                           │
│                                                         │
│  Evento: [________________________________]             │
│  Valor:  [________________________________]             │
│  Vencim: [________________________________]             │
│                                                         │
│  [Cancelar]                           [Criar e Vincular]│
└─────────────────────────────────────────────────────────┘
```

### Acceptance Criteria
- [ ] Modal abre ao clicar no botão
- [ ] Formulário cria lançamento
- [ ] Lançamento é vinculado automaticamente
- [ ] Modal fecha após criar

---

## Arquivos a Modificar

| Arquivo | Mudanças |
|---------|----------|
| `resources/views/livewire/conciliacao/index.blade.php` | Adicionar UI N-para-1, Quick Edit, Modal |
| `app/Livewire/Conciliacao/Index.php` | Correções e novos métodos |
| `app/Services/ConciliacaoService.php` | Método para criar e linkar |

---

## Dependências

- REC-003 depende de REC-002 (AutoMatch - já implementado)
- REC-004 depende de AuditLog (parcialmente implementado via Log)
- REC-005 depende de REC-003 (UI)
- REC-006 depende de REC-003 (UI)

---

## Testes

```bash
# Testar N-para-1
php artisan test --filter=ConciliacaoNpara1Test

# Testar Quick Adjustment
php artisan test --filter=QuickAdjustmentTest
```
